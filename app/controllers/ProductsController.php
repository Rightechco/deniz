<?php
// app/controllers/ProductsController.php

class ProductsController extends Controller {
    private $productModel;
    private $categoryModel;
    private $attributeModel; // برای خواندن ویژگی‌ها و تنوع‌ها

    public function __construct() {
        $this->productModel = $this->model('Product');
        $this->categoryModel = $this->model('Category');
        $this->attributeModel = $this->model('ProductAttribute'); // مدل ویژگی‌ها و تنوع‌ها
        if (session_status() == PHP_SESSION_NONE) { 
            session_start();
        }
    }

    public function index() {
        // ... (متد index بدون تغییر)
        $products = $this->productModel->getAllProducts();
        $categories = $this->categoryModel->getAllCategories();
        $data = [
            'pageTitle' => 'لیست تمام محصولات',
            'products' => $products,
            'categories' => $categories,
            'current_category_id' => null, 
            'current_category_name' => 'همه محصولات'
        ];
        $this->view('products/index', $data);
    }

    public function show($id = null) {
        if (is_null($id) || !is_numeric($id)) {
            $errorController = new ErrorController(); 
            $errorController->notFound("شناسه محصول نامعتبر است.");
            return;
        }
        $product = $this->productModel->getProductById((int)$id);

        if ($product) {
            $data = [
                'pageTitle' => htmlspecialchars($product['name']),
                'product' => $product,
                'product_configurable_attributes' => [], // برای ویژگی‌های قابل انتخاب
                'product_variations_json' => '[]' // برای جاوااسکریپت، لیست تنوع‌ها به صورت JSON
            ];

            if ($product['product_type'] == 'variable') {
                // دریافت ویژگی‌های قابل تنظیم این محصول که مقادیر دارند
                $configurableAttrs = $this->attributeModel->getConfigurableAttributeDetailsForProduct($product['id']);
                $data['product_configurable_attributes'] = $configurableAttrs;

                // دریافت تمام تنوع‌های این محصول برای استفاده در جاوااسکریپت
                $variations = $this->attributeModel->getVariationsForProduct($product['id']);
                $data['product_variations_json'] = json_encode($variations); // تبدیل به JSON برای جاوااسکریپت
            }

            $this->view('products/show', $data);
        } else {
            $errorController = new ErrorController();
            $errorController->notFound("محصولی با این شناسه یافت نشد.");
        }
    }

    public function category($category_id = null) {
        // ... (متد category بدون تغییر)
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
            'products' => $products,
            'categories' => $categories,
            'current_category_id' => $category_id_int, 
            'current_category_name' => htmlspecialchars($current_category['name'])
        ];
        $this->view('products/index', $data);
    }
}
?>
