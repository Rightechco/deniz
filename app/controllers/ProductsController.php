<?php
// app/controllers/ProductsController.php

class ProductsController extends Controller {
    private $productModel;
    private $categoryModel;
    private $attributeModel; 

    public function __construct() {
        $this->productModel = $this->model('Product');
        $this->categoryModel = $this->model('Category');
        $this->attributeModel = $this->model('ProductAttribute'); 
        if (session_status() == PHP_SESSION_NONE) { 
            session_start();
        }
    }

    public function index($category_slug = null) {
        // ... (کد قبلی این متد از سند products_controller_gallery_display_v3)
        $products = [];
        $current_category_id = null;
        $current_category_name = 'همه محصولات';
        $pageTitle = 'فروشگاه محصولات';

        if ($category_slug) {
            $category = method_exists($this->categoryModel, 'getCategoryBySlug') ? $this->categoryModel->getCategoryBySlug($category_slug) : null;
            if ($category) {
                $current_category_id = $category['id'];
                $current_category_name = $category['name'];
                $products = $this->productModel->getProductsByCategoryId($current_category_id);
                $pageTitle = 'محصولات دسته: ' . htmlspecialchars($current_category_name);
            } else {
                flash('error_message', 'دسته‌بندی مورد نظر یافت نشد.', 'alert alert-warning');
                $products = $this->productModel->getAllProducts();
            }
        } else {
            $products = $this->productModel->getAllProducts();
        }
        
        $categories = $this->categoryModel->getAllCategories();
        $data = [
            'pageTitle' => $pageTitle,
            'products' => $products ?: [],
            'categories' => $categories ?: [],
            'current_category_id' => $current_category_id, 
            'current_category_name' => htmlspecialchars($current_category_name)
        ];
        $this->view('products/index', $data);
    }

    public function show($id = null) {
        if (is_null($id) || !is_numeric($id)) {
            $errorController = new ErrorController(); 
            $errorController->notFound("شناسه محصول نامعتبر است.");
            return;
        }
        $product_id = (int)$id;
        // getProductById from ProductModel should already join with users table for vendor info
        $product = $this->productModel->getProductById($product_id);

        if ($product) {
            $gallery_images = method_exists($this->productModel, 'getGalleryImages') ? $this->productModel->getGalleryImages($product_id) : [];

            $data = [
                'pageTitle' => htmlspecialchars($product['name']),
                'product' => $product, // This should contain vendor_full_name or vendor_username if joined correctly
                'gallery_images' => $gallery_images ?: [], 
                'product_configurable_attributes' => [], 
                'product_variations_json' => '[]' 
            ];

            if ($product['product_type'] == 'variable') {
                $configurableAttrs = $this->attributeModel->getConfigurableAttributeDetailsForProduct($product['id']);
                $data['product_configurable_attributes'] = $configurableAttrs ?: [];

                $variations = $this->attributeModel->getVariationsForProduct($product['id']);
                // Using JSON flags for better encoding
                $json_variations = json_encode($variations ?: [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log("ProductsController::show - JSON encode error for variations (Product ID: {$product_id}): " . json_last_error_msg());
                    error_log("ProductsController::show - Variations data that caused error: " . print_r($variations, true));
                    $data['product_variations_json'] = '[]'; // Fallback to empty array on error
                    flash('product_error', 'خطا در بارگذاری اطلاعات تنوع محصول.', 'alert alert-danger');
                } else {
                    $data['product_variations_json'] = $json_variations;
                }
            }
            
            $this->view('products/show', $data);
        } else {
            $errorController = new ErrorController();
            $errorController->notFound("محصولی با این شناسه یافت نشد.");
        }
    }

    public function category($category_id = null) { // Kept for compatibility
        // ... (کد قبلی این متد از سند products_controller_gallery_display_v3) ...
        if (is_null($category_id) || !is_numeric($category_id)) {
            flash('error_message', 'شناسه دسته‌بندی نامعتبر است.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'products');
            exit();
        }
        $category_id_int = (int)$category_id;
        $products = $this->productModel->getProductsByCategoryId($category_id_int); 
        $current_category = $this->categoryModel->getCategoryById($category_id_int);
        $categories = $this->categoryModel->getAllCategories();

        if (!$current_category) {
            flash('error_message', 'دسته‌بندی مورد نظر یافت نشد.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'products');
            exit();
        }
        $data = [
            'pageTitle' => 'محصولات دسته‌بندی: ' . htmlspecialchars($current_category['name']),
            'products' => $products ?: [],
            'categories' => $categories ?: [],
            'current_category_id' => $category_id_int, 
            'current_category_name' => htmlspecialchars($current_category['name'])
        ];
        $this->view('products/index', $data);
    }
    
    // Example search method (basic)
    public function search() {
        $query = isset($_GET['query']) ? trim(filter_input(INPUT_GET, 'query', FILTER_SANITIZE_FULL_SPECIAL_CHARS)) : '';
        $products = [];
        if (!empty($query)) {
            // You need to implement searchProducts in your ProductModel
            $products = method_exists($this->productModel, 'searchProducts') ? $this->productModel->searchProducts($query) : [];
            if(empty($products)){
                 flash('info_message', 'محصولی با عبارت "'.htmlspecialchars($query).'" یافت نشد.', 'alert alert-info');
            }
        } else {
            flash('info_message', 'لطفاً عبارتی برای جستجو وارد کنید.', 'alert alert-info');
            // Optionally redirect to products page or show all products
            // header('Location: ' . BASE_URL . 'products');
            // exit;
        }
        $categories = $this->categoryModel->getAllCategories();
        $data = [
            'pageTitle' => 'نتایج جستجو برای: ' . htmlspecialchars($query),
            'products' => $products,
            'categories' => $categories,
            'search_query' => $query,
            'current_category_id' => null,
            'current_category_name' => 'نتایج جستجو'
        ];
        $this->view('products/index', $data); // Re-use the product listing view
    }
     public function categories() {
        $categories = $this->categoryModel->getAllCategoriesWithProductCount(); // Needs implementation in CategoryModel
        $data = [
            'pageTitle' => 'همه دسته‌بندی‌ها',
            'categories' => $categories ?: []
        ];
        $this->view('products/categories_list', $data); // You'll need to create this view
    }
}
?>
