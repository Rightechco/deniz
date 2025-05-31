<?php
// app/controllers/VendorController.php

class VendorController extends Controller {
    private $userModel;
    private $productModel;
    private $categoryModel;
    private $attributeModel; 
    private $orderModel;     
    // private $uploadDir = 'uploads/products/'; // Not directly used in payout logic shown
    // private $variationUploadDir = 'uploads/variations/'; // Not directly used in payout logic shown

    public function __construct() {
        $this->userModel = $this->model('User');
        $this->productModel = $this->model('Product');
        $this->categoryModel = $this->model('Category');
        $this->attributeModel = $this->model('ProductAttribute');
        $this->orderModel = $this->model('Order'); 

        if (!$this->userModel || !$this->productModel || !$this->categoryModel || !$this->orderModel || !$this->attributeModel) {
            error_log("VendorController FATAL: One or more models failed to load in constructor.");
            die("خطای سیستمی: بارگذاری مدل‌ها با مشکل مواجه شد.");
        }

        if (session_status() == PHP_SESSION_NONE) { 
            session_start(); 
        }

        if (!isset($_SESSION['user_id'])) {
            flash('auth_required', 'برای دسترسی به پنل فروشندگی، لطفاً ابتدا وارد شوید.', 'alert alert-danger');
            $_SESSION['redirect_after_login'] = BASE_URL . 'vendor/dashboard';
            header('Location: ' . BASE_URL . 'auth/login');
            exit();
        }
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'vendor') {
            flash('access_denied', 'شما اجازه دسترسی به پنل فروشندگی را ندارید.', 'alert alert-danger');
            // Redirect based on actual role if needed, or to a generic page
            header('Location: ' . BASE_URL); 
            exit();
        }
    }

    public function dashboard() {
        $vendor_id = $_SESSION['user_id'];
        $vendor = $this->userModel->findUserById($vendor_id);
        
        if (!$vendor) {
            flash('error_message', 'اطلاعات کاربری شما (فروشنده) یافت نشد.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'auth/logout'); // Log out if vendor data is inconsistent
            exit();
        }

        $withdrawable_balance = $this->orderModel->getVendorWithdrawableBalance($vendor_id);
        $unpaid_items = $this->orderModel->getUnpaidOrderItemsForVendor($vendor_id);

        // Enhanced Logging
        error_log("Vendor Dashboard - Vendor ID: {$vendor_id}");
        error_log("Vendor Dashboard - Withdrawable Balance from Model: " . print_r($withdrawable_balance, true));
        error_log("Vendor Dashboard - Unpaid Items from Model (Count): " . count($unpaid_items ?: []));
        error_log("Vendor Dashboard - Unpaid Items Data from Model: " . print_r($unpaid_items, true));

        $data = [
            'pageTitle' => 'داشبورد فروشنده',
            'vendor_name' => $vendor['first_name'] ? ($vendor['first_name'] . ' ' . $vendor['last_name']) : ($vendor['username'] ?? 'فروشنده گرامی'),
            'withdrawable_balance' => $withdrawable_balance ?? 0.00,
            'unpaid_items' => $unpaid_items ?: []
            // Add other necessary data for dashboard like product count, sales amount etc.
            // 'total_products' => $this->productModel->getProductCountByVendor($vendor_id) ?? 0,
            // 'total_sales_amount' => $this->orderModel->getTotalSalesAmountByVendor($vendor_id) ?? 0, 
        ];
        $this->view('vendor/dashboard', $data);
    }

    public function myProducts() {
        $vendor_id = $_SESSION['user_id'];
        $productsFromDb = $this->productModel->getProductsByVendorId($vendor_id);
        $products_to_view = [];

        if ($productsFromDb) {
            foreach ($productsFromDb as $product_data_item) {
                $product_data_item['variations_details'] = [];
                $product_data_item['initial_total_stock'] = isset($product_data_item['initial_stock_quantity']) ? (int)$product_data_item['initial_stock_quantity'] : 0;
                $product_data_item['current_total_stock'] = isset($product_data_item['stock_quantity']) ? (int)$product_data_item['stock_quantity'] : 0;
                $product_data_item['sales_count_total_product'] = 0; // Initialize
                $product_data_item['remaining_total_stock'] = 0; // Initialize

                if ($product_data_item['product_type'] === 'variable') {
                    $variations = $this->attributeModel->getVariationsForProduct($product_data_item['id']);
                    $product_data_item['variations_details'] = $variations;
                    
                    $totalInitialVariableStock = 0;
                    $totalCurrentVariableStock = 0;
                    $totalVariableSales = 0;
                    if ($variations) {
                        foreach ($variations as $key => $variation_item) {
                             if (isset($variation_item['is_active']) && $variation_item['is_active']) {
                                $sales_data = method_exists($this->productModel, 'getVariationSalesAndStock') ? $this->productModel->getVariationSalesAndStock($variation_item['id']) : ['total_sold' => 0, 'current_stock' => $variation_item['stock_quantity']];
                                
                                $initial_stock = isset($variation_item['initial_stock_quantity']) ? (int)$variation_item['initial_stock_quantity'] : 0;
                                $current_stock = $sales_data['current_stock'] ?? (int)$variation_item['stock_quantity'];
                                $sales = $sales_data['total_sold'] ?? 0;

                                $totalInitialVariableStock += $initial_stock;
                                $totalCurrentVariableStock += $current_stock;
                                $totalVariableSales += $sales;
                                
                                $product_data_item['variations_details'][$key]['initial_stock_quantity'] = $initial_stock;
                                $product_data_item['variations_details'][$key]['current_stock_quantity'] = $current_stock;
                                $product_data_item['variations_details'][$key]['sales_count'] = $sales;
                                $remaining_var_stock = $initial_stock - $sales;
                                $product_data_item['variations_details'][$key]['remaining_stock_from_initial'] = ($remaining_var_stock < 0) ? 0 : $remaining_var_stock;
                            }
                        }
                    }
                    $product_data_item['initial_total_stock'] = $totalInitialVariableStock;
                    $product_data_item['current_total_stock'] = $totalCurrentVariableStock;
                    $product_data_item['sales_count_total_product'] = $totalVariableSales;
                } else { // Simple product
                    $sales_data = method_exists($this->productModel, 'getSalesCount') ? $this->productModel->getSalesCount($product_data_item['id']) : 0;
                    $product_data_item['sales_count_total_product'] = $sales_data;
                }
                
                $product_data_item['remaining_total_stock'] = $product_data_item['initial_total_stock'] - $product_data_item['sales_count_total_product'];
                if ($product_data_item['remaining_total_stock'] < 0) {
                    $product_data_item['remaining_total_stock'] = 0;
                }
                
                $products_to_view[] = $product_data_item;
            }
        }
        $data = [
            'pageTitle' => 'محصولات من',
            'products' => $products_to_view
        ];
        $this->view('vendor/products/index', $data);
    }
    public function addProduct() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize POST data
            $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $price_input = trim(filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
            $stock_quantity_input = trim(filter_input(INPUT_POST, 'stock_quantity', FILTER_SANITIZE_NUMBER_INT));
            $category_id_input = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
            $product_type_input = filter_input(INPUT_POST, 'product_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'name' => $name,
                'description' => $description,
                'price' => $price_input,
                'stock_quantity' => $stock_quantity_input,
                'initial_stock_quantity' => $stock_quantity_input,
                'category_id' => !empty($category_id_input) ? (int)$category_id_input : null,
                'product_type' => $product_type_input ?: 'simple',
                'vendor_id' => $_SESSION['user_id'],
                'image_file' => $_FILES['product_image'] ?? null, // Pass the file array
                // Gallery images will be handled after product creation
                'name_err' => '', 'price_err' => '', 'stock_quantity_err' => '', 'image_err' => '', 'product_type_err' => ''
            ];

            // --- Validation ---
            if (empty($data['name'])) { $data['name_err'] = 'لطفاً نام محصول را وارد کنید.'; }
            // ... (سایر اعتبارسنجی‌ها مشابه AdminController) ...
            if ($data['product_type'] == 'simple') {
                if ($data['price'] === '' || $data['price'] === null) { $data['price_err'] = 'لطفاً قیمت محصول را وارد کنید.'; }
                elseif (!is_numeric($data['price']) || (float)$data['price'] < 0) { $data['price_err'] = 'قیمت معتبر نیست.'; }
                if ($data['stock_quantity'] === '' || !is_numeric($data['stock_quantity']) || (int)$data['stock_quantity'] < 0) {
                    $data['stock_quantity_err'] = 'موجودی معتبر نیست.';
                } else { $data['initial_stock_quantity'] = (int)$data['stock_quantity'];}
            } else { // Variable
                if (!empty($data['price']) && (!is_numeric($data['price']) || (float)$data['price'] < 0)) { $data['price_err'] = 'قیمت والد (اختیاری) نامعتبر.';}
                else if (empty($data['price'])) { $data['price'] = null; }
                if (!empty($data['stock_quantity']) && (!is_numeric($data['stock_quantity']) || (int)$data['stock_quantity'] < 0)) { $data['stock_quantity_err'] = 'موجودی والد (اختیاری) نامعتبر.';}
                else if ($data['stock_quantity'] === '' || !is_numeric($data['stock_quantity'])) { $data['stock_quantity'] = 0; $data['initial_stock_quantity'] = 0;}
                else { $data['initial_stock_quantity'] = (int)$data['stock_quantity'];}
            }


            if (empty($data['name_err']) && empty($data['price_err']) && empty($data['stock_quantity_err']) && empty($data['image_err']) && empty($data['product_type_err'])) {
                
                $addProductResult = $this->productModel->addProduct($data, $_FILES['product_image'] ?? null, $_SESSION['user_id']);

                if (isset($addProductResult['success']) && $addProductResult['success'] && isset($addProductResult['product_id'])) {
                    $product_id = $addProductResult['product_id'];
                    // Handle configurable attributes
                    if ($data['product_type'] === 'variable' && isset($_POST['configurable_attributes']) && is_array($_POST['configurable_attributes'])) {
                        $this->attributeModel->setConfigurableAttributesForProduct($product_id, array_map('intval', $_POST['configurable_attributes']));
                    }
                    // Handle Gallery Images
                    if (isset($_FILES['gallery_images_new'])) { // Note: name in form should be gallery_images_new[]
                        $gallery_alt_texts_new = $_POST['gallery_alt_texts_new'] ?? [];
                        foreach ($_FILES['gallery_images_new']['name'] as $key => $name) {
                            if ($_FILES['gallery_images_new']['error'][$key] == UPLOAD_ERR_OK) {
                                $gallery_file_data_single = [
                                    'name' => $_FILES['gallery_images_new']['name'][$key],
                                    'type' => $_FILES['gallery_images_new']['type'][$key],
                                    'tmp_name' => $_FILES['gallery_images_new']['tmp_name'][$key],
                                    'error' => $_FILES['gallery_images_new']['error'][$key],
                                    'size' => $_FILES['gallery_images_new']['size'][$key]
                                ];
                                $alt_text = isset($gallery_alt_texts_new[$key]) ? trim($gallery_alt_texts_new[$key]) : null;
                                if(!$this->productModel->addGalleryImage($product_id, $gallery_file_data_single, $alt_text)){
                                     error_log("VendorController::addProduct - Failed to add gallery image for product ID {$product_id}, file: {$name}");
                                }
                            }
                        }
                    }
                    flash('product_action_success', 'محصول جدید با موفقیت اضافه شد.');
                    header('Location: ' . BASE_URL . ($data['product_type'] === 'variable' ? 'vendor/manageProductVariations/' . $product_id : 'vendor/myProducts'));
                    exit();
                } else {
                    $errorMsg = isset($addProductResult['message']) ? $addProductResult['message'] : 'خطا در افزودن محصول به پایگاه داده.';
                    flash('product_action_fail', $errorMsg, 'alert alert-danger');
                    // No need to delete main image here as addProduct in model should handle it on failure
                }
            } else {
                 flash('product_form_error', 'لطفاً تمام فیلدهای الزامی را به درستی پر کنید و خطاهای موجود را برطرف نمایید.', 'alert alert-danger');
                 // No need to delete main image here as it wasn't moved if validation failed before addProduct call
            }
        }
        // For GET request or if POST fails
        $data_for_view = $data ?? [];
        $data_for_view['pageTitle'] = 'افزودن محصول جدید';
        $data_for_view['categories'] = $this->categoryModel->getAllCategories();
        $data_for_view['all_attributes'] = $this->attributeModel->getAllAttributesWithValues();
        $data_for_view['configurable_attributes_for_product'] = []; // For new product
        $this->view('vendor/products/add', $data_for_view);
    }

    public function editProduct($id = null) {
        if (is_null($id) || !is_numeric($id)) { /* ... redirect ... */ }
        $id = (int)$id;
        $product = $this->productModel->getProductById($id);
        if (!$product || $product['vendor_id'] != $_SESSION['user_id']) { /* ... access denied ... */ }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize POST data
            $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            // ... (sanitize other fields similarly) ...
            $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $price_input = trim(filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
            $stock_quantity_input = trim(filter_input(INPUT_POST, 'stock_quantity', FILTER_SANITIZE_NUMBER_INT));
            $initial_stock_quantity_input = filter_input(INPUT_POST, 'initial_stock_quantity_edit', FILTER_SANITIZE_NUMBER_INT); // Admin might edit this, vendor usually not.
            $category_id_input = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
            $product_type_input = filter_input(INPUT_POST, 'product_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $delete_current_image_input = filter_input(INPUT_POST, 'delete_current_image', FILTER_VALIDATE_BOOLEAN);


            $current_image_url = isset($_POST['current_image_url']) ? trim($_POST['current_image_url']) : ($product['image_url'] ?? '');
            
            $data = [
                'id' => $id,
                'name' => $name,
                'description' => $description,
                'price' => $price_input,
                'stock_quantity' => $stock_quantity_input,
                'initial_stock_quantity' => $initial_stock_quantity_input !== null ? (int)$initial_stock_quantity_input : $product['initial_stock_quantity'],
                'category_id' => !empty($category_id_input) ? (int)$category_id_input : null,
                'product_type' => $product_type_input ?: 'simple',
                'vendor_id' => $_SESSION['user_id'], // Vendor cannot change this
                'image_file' => $_FILES['product_image'] ?? null, // Pass the file array
                'delete_current_image' => $delete_current_image_input,
                'image_url' => $current_image_url, // Start with current, update if new one is uploaded or old one deleted
                'name_err' => '', 'price_err' => '', 'stock_quantity_err' => '', 'image_err' => '', 'product_type_err' => ''
            ];
            // ... (Validation logic similar to addProduct, adapted for edit) ...
            if (empty($data['name'])) { $data['name_err'] = 'لطفاً نام محصول را وارد کنید.'; }
            // ... (other validations) ...

            if (empty($data['name_err']) && empty($data['price_err']) && empty($data['stock_quantity_err']) && empty($data['image_err']) && empty($data['product_type_err'])) {
                
                $updateResult = $this->productModel->updateProduct($id, $data, $_FILES['product_image'] ?? null, $delete_current_image_input);

                if (isset($updateResult['success']) && $updateResult['success']) {
                    $selected_configurable_attributes = isset($_POST['configurable_attributes']) && is_array($_POST['configurable_attributes']) ? array_map('intval', $_POST['configurable_attributes']) : [];
                    if ($data['product_type'] === 'variable') {
                        $this->attributeModel->setConfigurableAttributesForProduct($id, $selected_configurable_attributes);
                    } else { 
                        $this->attributeModel->setConfigurableAttributesForProduct($id, []); 
                        if (method_exists($this->attributeModel, 'deleteAllVariationsForProduct')) {
                            $this->attributeModel->deleteAllVariationsForProduct($id);
                        }
                    }

                    // Handle Gallery Images on Edit
                    // 1. Delete images marked for deletion
                    if (isset($_POST['delete_gallery_images']) && is_array($_POST['delete_gallery_images'])) {
                        foreach ($_POST['delete_gallery_images'] as $image_id_to_delete) {
                            $this->productModel->deleteGalleryImage((int)$image_id_to_delete);
                        }
                    }
                    // 2. Add new gallery images
                    if (isset($_FILES['gallery_images_new'])) {
                        $gallery_alt_texts_new = $_POST['gallery_alt_texts_new'] ?? [];
                        foreach ($_FILES['gallery_images_new']['name'] as $key => $name) {
                            if ($_FILES['gallery_images_new']['error'][$key] == UPLOAD_ERR_OK) {
                                $gallery_file_data_single = [
                                    'name' => $_FILES['gallery_images_new']['name'][$key],
                                    'type' => $_FILES['gallery_images_new']['type'][$key],
                                    'tmp_name' => $_FILES['gallery_images_new']['tmp_name'][$key],
                                    'error' => $_FILES['gallery_images_new']['error'][$key],
                                    'size' => $_FILES['gallery_images_new']['size'][$key]
                                ];
                                $alt_text = isset($gallery_alt_texts_new[$key]) ? trim($gallery_alt_texts_new[$key]) : null;
                                $this->productModel->addGalleryImage($id, $gallery_file_data_single, $alt_text);
                            }
                        }
                    }
                    // 3. Update existing gallery image alt texts
                    if (isset($_POST['existing_gallery_alt_texts']) && is_array($_POST['existing_gallery_alt_texts'])) {
                        foreach($_POST['existing_gallery_alt_texts'] as $img_id => $alt_text) {
                            $this->productModel->updateGalleryImageAltText((int)$img_id, trim($alt_text));
                        }
                    }

                    flash('product_action_success', 'محصول با موفقیت ویرایش شد.');
                    header('Location: ' . BASE_URL . 'vendor/myProducts'); 
                    exit();
                } else { 
                    $errorMsg = isset($updateResult['message']) ? $updateResult['message'] : 'خطا در ویرایش محصول در پایگاه داده.';
                    flash('product_action_fail', $errorMsg, 'alert alert-danger');
                }
            } else {
                 flash('product_form_error', 'لطفاً خطاهای فرم را برطرف نمایید.', 'alert alert-danger');
            }
        }
        // For GET request or if POST failed validation
        $data_for_view = $data ?? array_merge($product, ['id' => $id]); 
        $data_for_view['pageTitle'] = 'ویرایش محصول: ' . htmlspecialchars($product['name']);
        $data_for_view['categories'] = $this->categoryModel->getAllCategories();
        $data_for_view['all_attributes'] = $this->attributeModel->getAllAttributesWithValues(); 
        $data_for_view['configurable_attributes_for_product'] = $this->attributeModel->getConfigurableAttributesForProduct($id);
        $data_for_view['gallery_images'] = $this->productModel->getGalleryImages($id); // Fetch gallery images
        
        if((isset($data_for_view['image_err']) && !empty($data_for_view['image_err'])) || (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == UPLOAD_ERR_NO_FILE && empty($data_for_view['image_url'])) ){
            $data_for_view['image_url'] = $product['image_url']; 
        }
        $this->view('vendor/products/edit', $data_for_view);
    }
    
    public function deleteProduct($id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $id && is_numeric($id)) {
            $id = (int)$id;
            $product = $this->productModel->getProductById($id);
            if (!$product || $product['vendor_id'] != $_SESSION['user_id']) {
                flash('access_denied', 'شما اجازه حذف این محصول را ندارید.', 'alert alert-danger');
                header('Location: ' . BASE_URL . 'vendor/myProducts');
                exit();
            }
            
            $image_path_to_delete = (defined('FCPATH') && !empty($product['image_url'])) ? FCPATH . $product['image_url'] : null;
            if ($this->productModel->deleteProduct($id)) {
                if ($product['product_type'] === 'variable') {
                    $variations = $this->attributeModel->getVariationsForProduct($id); 
                    if($variations){
                        foreach($variations as $var_item){
                            if(!empty($var_item['image_url']) && defined('FCPATH') && file_exists(FCPATH . $var_item['image_url'])){
                                unlink(FCPATH . $var_item['image_url']);
                            }
                        }
                    }
                }
                if ($image_path_to_delete && file_exists($image_path_to_delete)) {
                    unlink($image_path_to_delete);
                }
                flash('product_deleted_success', 'محصول "' . htmlspecialchars($product['name']) . '" با موفقیت حذف شد.');
            } else { 
                flash('product_action_fail', 'خطا در حذف محصول.', 'alert alert-danger');
            }
            header('Location: ' . BASE_URL . 'vendor/myProducts');
            exit();
        } else { 
            flash('error_message', 'درخواست نامعتبر.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'vendor/myProducts');
            exit();
        }
    }
    
    // --- مدیریت تنوع‌ها برای محصولات فروشنده ---
    public function manageProductVariations($parent_product_id = null) {
        if (is_null($parent_product_id) || !is_numeric($parent_product_id)) {
            flash('error_message', 'شناسه محصول والد نامعتبر.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'vendor/myProducts'); exit();
        }
        $parent_product_id = (int)$parent_product_id;
        $parentProduct = $this->productModel->getProductById($parent_product_id);

        if (!$parentProduct || $parentProduct['vendor_id'] != $_SESSION['user_id'] || $parentProduct['product_type'] !== 'variable') {
            flash('access_denied', 'شما اجازه مدیریت تنوع برای این محصول را ندارید یا محصول متغیر نیست.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'vendor/myProducts');
            exit();
        }
        
        $configurableAttributes = $this->attributeModel->getConfigurableAttributeDetailsForProduct($parent_product_id);
        $existingVariations = $this->attributeModel->getVariationsForProduct($parent_product_id);
        $data = [
            'pageTitle' => 'مدیریت تنوع‌ها برای: ' . htmlspecialchars($parentProduct['name']),
            'parentProduct' => $parentProduct,
            'configurableAttributes' => $configurableAttributes,
            'existingVariations' => $existingVariations,
            'variation_sku' => '', 'variation_price' => '', 
            'variation_stock' => '0', 
            'variation_initial_stock' => '0', 
            'variation_image_url' => '', 'selected_attributes' => []
        ];
        $this->view('vendor/products/manage_variations', $data);
    }

    public function addVariation($parent_product_id = null) {
        if (is_null($parent_product_id) || !is_numeric($parent_product_id)) { 
             flash('error_message', 'شناسه محصول والد نامعتبر.', 'alert alert-danger');
             header('Location: ' . BASE_URL . 'vendor/myProducts'); exit();
        }
        $parent_product_id = (int)$parent_product_id;
        $parentProduct = $this->productModel->getProductById($parent_product_id);
        if (!$parentProduct || $parentProduct['vendor_id'] != $_SESSION['user_id'] || $parentProduct['product_type'] !== 'variable') {
            flash('access_denied', 'اجازه افزودن تنوع به این محصول را ندارید.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'vendor/myProducts'); exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $stock_input_var = isset($_POST['variation_stock']) ? trim($_POST['variation_stock']) : '0';
            $initial_stock_input_var = isset($_POST['variation_initial_stock']) ? trim($_POST['variation_initial_stock']) : $stock_input_var;

            $variation_data = [
                'parent_product_id' => $parent_product_id,
                'sku' => isset($_POST['variation_sku']) ? trim($_POST['variation_sku']) : null,
                'price' => isset($_POST['variation_price']) && $_POST['variation_price'] !== '' ? trim($_POST['variation_price']) : null,
                'stock_quantity' => $stock_input_var,
                'initial_stock_quantity' => $initial_stock_input_var,
                'image_url' => null, 
                'is_active' => isset($_POST['variation_is_active']) ? 1 : 0,
            ];
            $selected_attributes = isset($_POST['variation_attributes']) && is_array($_POST['variation_attributes']) ? $_POST['variation_attributes'] : [];

            $error = false;
            if (empty($selected_attributes)) {
                 flash('variation_action_fail', 'حداقل یک ویژگی باید برای تنوع انتخاب شود.', 'alert alert-danger');
                 $error = true;
            } elseif ((int)$variation_data['stock_quantity'] < 0) {
                 flash('variation_action_fail', 'موجودی تنوع نمی‌تواند منفی باشد.', 'alert alert-danger');
                 $error = true;
            } elseif ((int)$variation_data['initial_stock_quantity'] < 0) {
                 flash('variation_action_fail', 'موجودی اولیه تنوع نمی‌تواند منفی باشد.', 'alert alert-danger');
                 $error = true;
            } elseif (!empty($variation_data['price']) && (!is_numeric($variation_data['price']) || (float)$variation_data['price'] < 0) ) {
                 flash('variation_action_fail', 'قیمت تنوع نامعتبر است.', 'alert alert-danger');
                 $error = true;
            }
            
            if (!$error) {
                $result = $this->attributeModel->addVariation($variation_data, $selected_attributes);
                if (is_int($result) && $result > 0) { 
                    flash('variation_action_success', 'تنوع جدید با شناسه #' . $result . ' با موفقیت اضافه شد.');
                } else {
                    $errorMessage = 'خطا در افزودن تنوع.';
                    if ($result === 'duplicate_combination') {
                        $errorMessage = 'خطا: این ترکیب از ویژگی‌ها قبلاً برای این محصول ثبت شده است.';
                    } elseif (strpos((string)$result, 'db_error') === 0 || $result === 'pdo_exception' || $result === 'general_exception') {
                        $errorMessage = 'خطای داخلی سرور هنگام افزودن تنوع. لطفاً لاگ‌ها را بررسی کنید.';
                    }
                    flash('variation_action_fail', $errorMessage, 'alert alert-danger');
                }
            }
            header('Location: ' . BASE_URL . 'vendor/manageProductVariations/' . $parent_product_id);
            exit();
        }
        header('Location: ' . BASE_URL . 'vendor/manageProductVariations/' . $parent_product_id);
        exit();
    }
    
    public function editVariation($variation_id = null) {
        if (is_null($variation_id) || !is_numeric($variation_id)) { 
            flash('error_message', 'شناسه تنوع نامعتبر.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'vendor/myProducts'); exit();
        }
        $variation_id = (int)$variation_id;
        $variation = $this->attributeModel->getVariationById($variation_id);
        if (!$variation) { 
            flash('error_message', 'تنوع یافت نشد.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'vendor/myProducts'); exit();
        }
        $parentProduct = $this->productModel->getProductById($variation['parent_product_id']);
        if (!$parentProduct || $parentProduct['vendor_id'] != $_SESSION['user_id']) {
            flash('access_denied', 'اجازه ویرایش این تنوع را ندارید.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'vendor/myProducts'); exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $current_variation_image_url = isset($_POST['current_variation_image_url']) ? trim($_POST['current_variation_image_url']) : ($variation['image_url'] ?? null);
            
            $data_to_update = [
                'sku' => isset($_POST['variation_sku']) ? trim($_POST['variation_sku']) : null,
                'price' => isset($_POST['variation_price']) && $_POST['variation_price'] !== '' ? trim($_POST['variation_price']) : null,
                'stock_quantity' => isset($_POST['variation_stock']) ? (int)$_POST['variation_stock'] : 0,
                'initial_stock_quantity' => isset($_POST['variation_initial_stock_edit']) ? (int)trim($_POST['variation_initial_stock_edit']) : $variation['initial_stock_quantity'],
                'image_url' => $current_variation_image_url,
                'is_active' => isset($_POST['variation_is_active']) ? 1 : 0,
            ];
            $errors = [];

            if (!empty($data_to_update['price']) && (!is_numeric($data_to_update['price']) || (float)$data_to_update['price'] < 0)) { $errors['price_err'] = 'قیمت نامعتبر.';}
            if ($data_to_update['stock_quantity'] < 0) { $errors['stock_err'] = 'موجودی منفی مجاز نیست.';}
            if (isset($_POST['variation_initial_stock_edit']) && (!is_numeric($_POST['variation_initial_stock_edit']) || (int)$_POST['variation_initial_stock_edit'] < 0) ) { $errors['initial_stock_err'] = 'موجودی اولیه نامعتبر.';}

            $new_image_uploaded_path = null;
            if (isset($_FILES['variation_image']) && $_FILES['variation_image']['error'] == UPLOAD_ERR_OK) {
                if (!defined('FCPATH')) { $errors['image_err'] = 'خطای پیکربندی: FCPATH.'; }
                if (empty($errors['image_err'])) { 
                    $variation_upload_dir_abs = FCPATH . $this->variationUploadDir;
                    if (!is_dir($variation_upload_dir_abs)) { 
                        if (!mkdir($variation_upload_dir_abs, 0775, true)) { $errors['image_err'] = 'خطا در ایجاد پوشه آپلود تنوع.';}
                    }
                    if (empty($errors['image_err'])) { 
                        $file_info = pathinfo($_FILES["variation_image"]["name"]);
                        $file_type = strtolower($file_info['extension']);
                        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];  $max_file_size = 2 * 1024 * 1024;
                        if (!in_array($file_type, $allowed_types)) { $errors['image_err'] = 'فرمت‌های JPG, JPEG, PNG, GIF مجاز.';}
                        elseif ($_FILES["variation_image"]["size"] > $max_file_size) { $errors['image_err'] = 'حجم فایل > ۲مگابایت.';}
                        else {
                            $new_var_file_name = uniqid('var_'. $variation_id . '_', true) . '.' . $file_type;
                            if (move_uploaded_file($_FILES["variation_image"]["tmp_name"], $variation_upload_dir_abs . $new_var_file_name)) {
                                $new_image_uploaded_path = $this->variationUploadDir . $new_var_file_name;
                            } else { $errors['image_err'] = 'خطا در آپلود فایل تصویر تنوع.'; }
                        }
                    }
                }
            } elseif (isset($_FILES['variation_image']) && $_FILES['variation_image']['error'] != UPLOAD_ERR_NO_FILE) {
                $errors['image_err'] = 'خطایی در آپلود فایل تنوع. کد: ' . $_FILES['variation_image']['error'];
            }

            if (!empty($new_image_uploaded_path)) {
                if (!empty($current_variation_image_url) && defined('FCPATH') && file_exists(FCPATH . $current_variation_image_url)) {
                    unlink(FCPATH . $current_variation_image_url);
                }
                $data_to_update['image_url'] = $new_image_uploaded_path;
            }

            if (empty($errors)) {
                // ویژگی‌های تعریف‌کننده تنوع تغییر نمی‌کنند، فقط داده‌های خود تنوع
                if ($this->attributeModel->updateVariation($variation_id, $data_to_update)) { 
                    flash('variation_action_success', 'تنوع با موفقیت ویرایش شد.');
                    header('Location: ' . BASE_URL . 'vendor/manageProductVariations/' . $variation['parent_product_id']);
                    exit();
                } else {
                    flash('variation_action_fail', 'خطا در ویرایش تنوع در پایگاه داده.', 'alert alert-danger');
                    if ($new_image_uploaded_path && $data_to_update['image_url'] === $new_image_uploaded_path && defined('FCPATH') && file_exists(FCPATH . $new_image_uploaded_path)) {
                        unlink(FCPATH . $new_image_uploaded_path);
                        $data_to_update['image_url'] = $current_variation_image_url;
                    }
                }
            }
            
            $data_for_view = [
                'pageTitle' => 'ویرایش تنوع برای: ' . htmlspecialchars($parentProduct['name']),
                'parentProduct' => $parentProduct,
                'variation' => array_merge($variation, $data_to_update), 
                'errors' => $errors
            ];
             if(!empty($errors)) {
                 $error_msg_combined = "";
                 foreach($errors as $err_val) $error_msg_combined .= $err_val . "<br>";
                 flash('variation_form_error', $error_msg_combined, 'alert alert-danger');
             }
            $this->view('vendor/products/edit_variation', $data_for_view);
            exit();

        } else { // GET
            $data = [
                'pageTitle' => 'ویرایش تنوع برای: ' . htmlspecialchars($parentProduct['name']),
                'parentProduct' => $parentProduct,
                'variation' => $variation, 
                'errors' => []
            ];
            $this->view('vendor/products/edit_variation', $data);
        }
    }


    public function deleteVariation($variation_id = null) {
        if (is_null($variation_id) || !is_numeric($variation_id)) { 
            flash('error_message', 'شناسه تنوع نامعتبر.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'vendor/myProducts'); exit();
        }
        $variation_id = (int)$variation_id;
        $variation = $this->attributeModel->getVariationById($variation_id); 
        if (!$variation) { 
            flash('error_message', 'تنوع یافت نشد.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'vendor/myProducts'); exit();
        }
        $parentProduct = $this->productModel->getProductById($variation['parent_product_id']);
        if (!$parentProduct || $parentProduct['vendor_id'] != $_SESSION['user_id']) {
            flash('access_denied', 'اجازه حذف این تنوع را ندارید.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'vendor/myProducts'); exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
            $image_path_to_delete_var = (defined('FCPATH') && !empty($variation['image_url'])) ? FCPATH . $variation['image_url'] : null;

            if ($this->attributeModel->deleteVariation($variation_id)) {
                if ($image_path_to_delete_var && file_exists($image_path_to_delete_var)) {
                    unlink($image_path_to_delete_var);
                }
                flash('variation_action_success', 'تنوع با موفقیت حذف شد.');
            } else { 
                flash('variation_action_fail', 'خطا در حذف تنوع.', 'alert alert-danger');
            }
            header('Location: ' . BASE_URL . 'vendor/manageProductVariations/' . $variation['parent_product_id']);
            exit();
        } else {
             flash('error_message', 'درخواست نامعتبر برای حذف.', 'alert alert-danger');
             header('Location: ' . BASE_URL . 'vendor/manageProductVariations/' . $variation['parent_product_id']);
             exit();
        }
    }

    // --- Vendor Order Management ---
    public function orders() {
        $vendor_id = $_SESSION['user_id'];
        $orders = $this->orderModel->getOrdersForVendor($vendor_id);

        $data = [
            'pageTitle' => 'سفارشات محصولات شما',
            'orders' => $orders
        ];
        $this->view('vendor/orders/index', $data);
    }

    public function orderDetails($order_id = null) {
        if (is_null($order_id) || !is_numeric($order_id)) {
            flash('error_message', 'شناسه سفارش نامعتبر است.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'vendor/orders');
            exit();
        }
        $vendor_id = $_SESSION['user_id'];
        $order = $this->orderModel->getOrderDetailsById((int)$order_id, null, $vendor_id); 

        if ($order && !empty($order['items'])) { 
            $customer = $this->userModel->findUserById($order['user_id']);
            $data = [
                'pageTitle' => 'جزئیات سفارش #' . htmlspecialchars($order['id']) . ' (محصولات شما)',
                'order' => $order, 
                'customer' => $customer
            ];
            $this->view('vendor/orders/details', $data);
        } else {
            flash('error_message', 'سفارش مورد نظر یافت نشد یا هیچ محصولی از شما در این سفارش وجود ندارد.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'vendor/orders');
            exit();
        }
    }

    // --- Vendor Payout Management ---
    
    public function requestVendorPayout() {
        $vendor_id = $_SESSION['user_id'];
        $vendor_user = $this->userModel->findUserById($vendor_id);
        // This is the actual current balance based on eligible items
        $current_withdrawable_from_items = $this->orderModel->getVendorWithdrawableBalance($vendor_id); 

        error_log("Vendor Request Payout - Vendor ID: {$vendor_id}, Balance from model: {$current_withdrawable_from_items}");

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            // This amount comes from the hidden field in the dashboard, which should be $data['withdrawable_balance']
            $requested_amount_from_form = isset($_POST['requested_amount']) ? (float)trim($_POST['requested_amount']) : 0;
            $payment_details = isset($_POST['payment_details']) ? trim($_POST['payment_details']) : ($vendor_user['vendor_payment_details'] ?? ''); 

            error_log("Vendor Request Payout - POST - Requested Amount from Form: {$requested_amount_from_form}, Payment Details: {$payment_details}");

            $data_for_view = [ // This data would be used if re-rendering a separate payout form
                'pageTitle' => 'ثبت درخواست تسویه',
                'withdrawable_balance' => $current_withdrawable_from_items, // Use the freshly calculated balance
                'requested_amount' => $requested_amount_from_form, // What user submitted (should match balance if form is on dashboard)
                'payment_details' => $payment_details,
                'unpaid_items' => $this->orderModel->getUnpaidOrderItemsForVendor($vendor_id) ?: [], // For displaying on error page if needed
                'amount_err' => '',
                'details_err' => ''
            ];
            
            $min_payout = defined('MIN_VENDOR_PAYOUT_AMOUNT') ? (float)MIN_VENDOR_PAYOUT_AMOUNT : 50000; 

            if ($requested_amount_from_form < $min_payout) {
                $data_for_view['amount_err'] = 'مبلغ درخواستی باید حداقل ' . number_format($min_payout) . ' تومان باشد.';
            } elseif ($requested_amount_from_form > $current_withdrawable_from_items) {
                // This condition should ideally not be met if the form correctly sends the total withdrawable balance.
                // If it is met, it means the form's hidden value was different or tampered with.
                $data_for_view['amount_err'] = 'مبلغ درخواستی (' . number_format($requested_amount_from_form) . ') نمی‌تواند بیشتر از موجودی قابل برداشت شما (' . number_format($current_withdrawable_from_items) . ') باشد.';
            }
            if (empty($payment_details)) {
                $data_for_view['details_err'] = 'لطفاً جزئیات حساب بانکی خود را وارد کنید.';
            }

            if (empty($data_for_view['amount_err']) && empty($data_for_view['details_err'])) {
                $unpaid_items_for_payout = $this->orderModel->getUnpaidOrderItemsForVendor($vendor_id);
                $order_item_ids_for_payout = [];
                $calculated_total_for_payout_items = 0;

                if (empty($unpaid_items_for_payout)) {
                    flash('payout_fail', 'هیچ آیتم پرداخت نشده‌ای برای تسویه یافت نشد.', 'alert alert-warning');
                    header('Location: ' . BASE_URL . 'vendor/dashboard');
                    exit();
                }

                foreach($unpaid_items_for_payout as $item){
                    if (isset($item['order_item_id']) && isset($item['vendor_earning'])) {
                        $order_item_ids_for_payout[] = $item['order_item_id'];
                        $calculated_total_for_payout_items += (float)$item['vendor_earning'];
                    }
                }
                
                // Ensure the requested amount (which is the total withdrawable from dashboard form) matches the sum of items to be paid out
                if (abs($requested_amount_from_form - $calculated_total_for_payout_items) > 0.01 && $calculated_total_for_payout_items > 0) { 
                     // Small tolerance for float comparison
                     error_log("Vendor Request Payout - Amount Mismatch. Form: {$requested_amount_from_form}, Calculated from items: {$calculated_total_for_payout_items}");
                     flash('payout_fail', 'مغایرت در مبلغ قابل برداشت و آیتم‌های انتخابی. لطفاً صفحه را رفرش کرده و دوباره تلاش کنید.', 'alert alert-danger');
                } else {
                    $payout_id = $this->orderModel->requestVendorPayout(
                        $vendor_id, 
                        $requested_amount_from_form, // Use the amount from the form (which should be the total withdrawable)
                        $order_item_ids_for_payout, 
                        'bank_transfer', 
                        $payment_details
                    );

                    if (is_int($payout_id) && $payout_id > 0) {
                        flash('payout_success', 'درخواست تسویه شما برای مبلغ ' . number_format($requested_amount_from_form) . ' تومان با شناسه پیگیری #' . $payout_id . ' با موفقیت ثبت شد.');
                        header('Location: ' . BASE_URL . 'vendor/payouts'); // Redirect to payout history
                        exit();
                    } else {
                        $error_key = is_string($payout_id) ? $payout_id : 'payout_request_fail_db';
                        flash($error_key, 'خطا در ثبت درخواست تسویه. (' . htmlspecialchars((string)$payout_id) . ')', 'alert alert-danger');
                    }
                }
            }
            // If errors, redirect back to dashboard with flash messages
            // The dashboard view will pick up flash messages.
            // To pass back form data and specific errors if not using a separate form page is trickier with redirects.
            // For now, just redirecting to dashboard. If a separate form page is used, $this->view('vendor/payouts/request_form', $data_for_view); would be appropriate.
             flash('payout_fail', ($data_for_view['amount_err'] ?? '') .'<br>'. ($data_for_view['details_err'] ?? ''), 'alert alert-danger');
             header('Location: ' . BASE_URL . 'vendor/dashboard');
             exit();

        } else { 
            // GET request to vendor/requestPayout should ideally redirect to dashboard or a specific payout page
            // as the form is currently on the dashboard itself.
            flash('info_message', 'برای درخواست تسویه، از فرم موجود در داشبورد استفاده کنید.', 'alert alert-info');
            header('Location: ' . BASE_URL . 'vendor/dashboard');
            exit();
        }
    }

    public function payoutHistory() {
        $vendor_id = $_SESSION['user_id'];
        $payouts = $this->orderModel->getPayoutRequestsByVendorId($vendor_id);
        $data = [
            'pageTitle' => 'تاریخچه تسویه حساب‌ها',
            'payouts' => $payouts
        ];
        $this->view('vendor/payouts/history', $data);
    }
}
?>
