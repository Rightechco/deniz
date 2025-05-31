<?php
// app/controllers/AdminController.php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AdminController extends Controller {
    private $orderModel;
    private $userModel;
    private $productModel;
    private $categoryModel;
    private $attributeModel; 
    private $uploadDir = 'uploads/products/'; 
    private $variationUploadDir = 'uploads/variations/'; 

    public function __construct() {
        $this->orderModel = $this->model('Order');
        $this->userModel = $this->model('User');
        $this->productModel = $this->model('Product');
        $this->categoryModel = $this->model('Category');
        $this->attributeModel = $this->model('ProductAttribute'); 

        if (session_status() == PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            flash('auth_required', 'برای دسترسی به این بخش، لطفاً ابتدا وارد شوید یا با حساب ادمین وارد شوید.', 'alert alert-danger');
            $redirect_url = isset($_SESSION['user_id']) ? BASE_URL : BASE_URL . 'auth/login'; 
            if (isset($_SESSION['user_id']) && $_SESSION['user_role'] !== 'admin') {
                $role = $_SESSION['user_role'];
                 switch ($role) {
                    case 'customer': $redirect_url = BASE_URL . 'customer/orders'; break;
                    case 'vendor': $redirect_url = BASE_URL . 'vendor/dashboard'; break;
                    case 'affiliate': $redirect_url = BASE_URL . 'affiliate/dashboard'; break;
                }
            }
            header('Location: ' . $redirect_url);
            exit();
        }
    }
    
    public function dashboard() { 
        header('Location: ' . BASE_URL . 'admin/reports'); 
        exit(); 
    }
    

    // --- Order Management ---
    public function orders() { 
        $orders = $this->orderModel->getAllOrders();
        $data = [
            'pageTitle' => 'مدیریت سفارشات',
            'orders' => $orders
        ];
        $this->view('admin/orders/index', $data);
    }

    public function orderDetails($order_id = null) { 
        if (is_null($order_id) || !is_numeric($order_id)) {
            flash('error_message', 'شناسه سفارش نامعتبر است.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/orders');
            exit();
        }
        $order = $this->orderModel->getOrderDetailsById((int)$order_id);
        if ($order) {
            $customer = $this->userModel->findUserById($order['user_id']);
            $data = [
                'pageTitle' => 'جزئیات سفارش #' . htmlspecialchars($order['id']),
                'order' => $order,
                'customer' => $customer
            ];
            $this->view('admin/orders/details', $data);
        } else {
            flash('error_message', 'سفارش مورد نظر یافت نشد.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/orders');
            exit();
        }
    }

    public function updateOrderStatus($order_id = null) { 
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $order_id && is_numeric($order_id)) {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $order_status = isset($_POST['order_status']) ? trim($_POST['order_status']) : null;
            $payment_status = isset($_POST['payment_status']) ? trim($_POST['payment_status']) : null;
            $allowed_order_statuses = ['pending_confirmation', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
            $allowed_payment_statuses = ['pending', 'pending_on_delivery', 'paid', 'failed', 'refunded'];

            if (in_array($order_status, $allowed_order_statuses) && in_array($payment_status, $allowed_payment_statuses)) {
                if ($this->orderModel->updateOrderStatus((int)$order_id, $order_status, $payment_status)) {
                    flash('order_status_updated', 'وضعیت سفارش #' . $order_id . ' با موفقیت به‌روز شد.');
                } else {
                    flash('error_message', 'خطا در به‌روزرسانی وضعیت سفارش.', 'alert alert-danger');
                }
            } else {
                flash('error_message', 'مقادیر وضعیت انتخاب شده نامعتبر هستند.', 'alert alert-danger');
            }
            header('Location: ' . BASE_URL . 'admin/orderDetails/' . $order_id);
            exit();
        } else {
            flash('error_message', 'درخواست نامعتبر.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/orders');
            exit();
        }
    }

    // --- Product Management ---
    public function products() { 
        $productsFromDb = $this->productModel->getAllProducts();
        $products_to_view = []; 

        if ($productsFromDb) {
            foreach ($productsFromDb as $product_data_item) { 
                $product_data_item['variations_details'] = []; 
                $product_data_item['initial_total_stock'] = isset($product_data_item['initial_stock_quantity']) ? (int)$product_data_item['initial_stock_quantity'] : 0; 
                $product_data_item['current_total_stock'] = isset($product_data_item['stock_quantity']) ? (int)$product_data_item['stock_quantity'] : 0; 
                $product_data_item['sales_count_total_product'] = 0; 
                $product_data_item['remaining_total_stock'] = 0;

                if ($product_data_item['product_type'] === 'variable') {
                    $variations = $this->attributeModel->getVariationsForProduct($product_data_item['id']);
                    $product_data_item['variations_details'] = $variations; 
                    
                    $totalInitialVariableStock = 0;
                    $totalCurrentVariableStock = 0;
                    $totalVariableSales = 0; 
                    if ($variations) {
                        foreach ($variations as $key => $variation_item) { 
                            if (isset($variation_item['is_active']) && $variation_item['is_active']) {
                                $initial_stock = isset($variation_item['initial_stock_quantity']) ? (int)$variation_item['initial_stock_quantity'] : 0;
                                $current_stock = isset($variation_item['current_stock_quantity']) ? (int)$variation_item['current_stock_quantity'] : (isset($variation_item['stock_quantity']) ? (int)$variation_item['stock_quantity'] : 0);
                                $sales = isset($variation_item['sales_count']) ? (int)$variation_item['sales_count'] : 0;

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
                } else { 
                    $product_data_item['sales_count_total_product'] = $this->productModel->getSalesCount($product_data_item['id']);
                }
                
                $product_data_item['remaining_total_stock'] = $product_data_item['initial_total_stock'] - $product_data_item['sales_count_total_product'];
                if ($product_data_item['remaining_total_stock'] < 0) {
                    $product_data_item['remaining_total_stock'] = 0;
                }
                
                $products_to_view[] = $product_data_item;
            }
        }

        $data = [
            'pageTitle' => 'مدیریت محصولات',
            'products' => $products_to_view 
        ];
        $this->view('admin/products/index', $data);
    }
    

    public function addProduct() { 
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize POST data
            $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            // ... (sanitize other fields similarly) ...
            $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $price_input = trim(filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
            $stock_quantity_input = trim(filter_input(INPUT_POST, 'stock_quantity', FILTER_SANITIZE_NUMBER_INT));
            $category_id_input = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
            $product_type_input = filter_input(INPUT_POST, 'product_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $vendor_id_input = filter_input(INPUT_POST, 'vendor_id', FILTER_SANITIZE_NUMBER_INT);
            $affiliate_commission_type_input = filter_input(INPUT_POST, 'affiliate_commission_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $affiliate_commission_value_input = filter_input(INPUT_POST, 'affiliate_commission_value', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);


            $data = [
                'name' => $name,
                'description' => $description,
                'price' => $price_input,
                'stock_quantity' => $stock_quantity_input,
                'initial_stock_quantity' => $stock_quantity_input, 
                'category_id' => !empty($category_id_input) ? (int)$category_id_input : null,
                'product_type' => $product_type_input ?: 'simple',
                'vendor_id' => !empty($vendor_id_input) ? (int)$vendor_id_input : null,
                'affiliate_commission_type' => $affiliate_commission_type_input ?: 'none',
                'affiliate_commission_value' => ($affiliate_commission_type_input !== 'none' && !empty($affiliate_commission_value_input)) ? (float)$affiliate_commission_value_input : null,
                'image_url' => '', // Will be set by handleImageUpload
                'name_err' => '', 'price_err' => '', 'stock_quantity_err' => '', 'image_err' => '', 'product_type_err' => '', 'vendor_id_err' => '', 'affiliate_commission_err' => ''
            ];

            // --- Validation ---
            if (empty($data['name'])) { $data['name_err'] = 'لطفاً نام محصول را وارد کنید.'; }
            if (!in_array($data['product_type'], ['simple', 'variable'])) { $data['product_type_err'] = 'نوع محصول نامعتبر است.'; }

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
            // ... (سایر اعتبارسنجی‌ها مانند کمیسیون همکاری) ...
             if ($data['affiliate_commission_type'] !== 'none' && ($data['affiliate_commission_value'] === null || !is_numeric($data['affiliate_commission_value']) || (float)$data['affiliate_commission_value'] < 0) ) {
                $data['affiliate_commission_err'] = 'مقدار کمیسیون همکاری نامعتبر است.';
            }


            // Main Image Upload
            $main_image_filename = null;
            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == UPLOAD_ERR_OK) {
                $main_image_filename = $this->productModel->handleImageUpload($_FILES['product_image'], 'products');
                if ($main_image_filename) {
                    $data['image_url'] = 'uploads/products/' . $main_image_filename; // Store relative path
                } else {
                    $data['image_err'] = 'خطا در آپلود تصویر اصلی.';
                }
            } elseif (isset($_FILES['product_image']) && $_FILES['product_image']['error'] != UPLOAD_ERR_NO_FILE) {
                 $data['image_err'] = 'خطا در آپلود فایل تصویر اصلی. کد خطا: ' . $_FILES['product_image']['error'];
            }

            if (empty($data['name_err']) && empty($data['price_err']) && empty($data['stock_quantity_err']) && empty($data['image_err']) && empty($data['product_type_err']) && empty($data['affiliate_commission_err'])) {
                $product_id = $this->productModel->addProduct($data);
                if ($product_id) {
                    // Handle configurable attributes
                    if ($data['product_type'] === 'variable' && isset($_POST['configurable_attributes']) && is_array($_POST['configurable_attributes'])) {
                        $this->attributeModel->setConfigurableAttributesForProduct($product_id, array_map('intval', $_POST['configurable_attributes']));
                    }
                    // Handle Gallery Images
                    if (isset($_FILES['gallery_images'])) {
                        $gallery_alt_texts = $_POST['gallery_alt_texts'] ?? [];
                        foreach ($_FILES['gallery_images']['name'] as $key => $name) {
                            if ($_FILES['gallery_images']['error'][$key] == UPLOAD_ERR_OK) {
                                $gallery_file_data = [
                                    'name' => $_FILES['gallery_images']['name'][$key],
                                    'type' => $_FILES['gallery_images']['type'][$key],
                                    'tmp_name' => $_FILES['gallery_images']['tmp_name'][$key],
                                    'error' => $_FILES['gallery_images']['error'][$key],
                                    'size' => $_FILES['gallery_images']['size'][$key]
                                ];
                                $alt_text = isset($gallery_alt_texts[$key]) ? trim($gallery_alt_texts[$key]) : null;
                                $this->productModel->addGalleryImage($product_id, $gallery_file_data, $alt_text);
                            }
                        }
                    }
                    flash('product_action_success', 'محصول جدید با موفقیت اضافه شد.');
                    header('Location: ' . BASE_URL . ($data['product_type'] === 'variable' ? 'admin/manageProductVariations/' . $product_id : 'admin/products'));
                    exit();
                } else {
                    flash('product_action_fail', 'خطا در افزودن محصول به پایگاه داده.', 'alert alert-danger');
                    if ($main_image_filename && defined('FCPATH') && file_exists(FCPATH . 'uploads/products/' . $main_image_filename)) {
                        unlink(FCPATH . 'uploads/products/' . $main_image_filename);
                    }
                }
            } else {
                 flash('product_form_error', 'لطفاً تمام فیلدهای الزامی را به درستی پر کنید و خطاهای موجود را برطرف نمایید.', 'alert alert-danger');
                 if ($main_image_filename && defined('FCPATH') && file_exists(FCPATH . 'uploads/products/' . $main_image_filename)) {
                    unlink(FCPATH . 'uploads/products/' . $main_image_filename);
                 }
            }
        }
        // For GET request or if POST fails
        $data_for_view = $data ?? [];
        $data_for_view['pageTitle'] = 'افزودن محصول جدید';
        $data_for_view['categories'] = $this->categoryModel->getAllCategories();
        $data_for_view['all_attributes'] = $this->attributeModel->getAllAttributesWithValues();
        $data_for_view['vendors'] = $this->userModel->getUsersByRole('vendor');
        $data_for_view['configurable_attributes_for_product'] = []; // For new product, none are selected yet
        $this->view('admin/products/add', $data_for_view);
    }

       public function editProduct($id = null) {
        if (is_null($id) || !is_numeric($id)) { 
            flash('error_message', 'شناسه محصول نامعتبر است.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/products'); exit();
        }
        $id = (int)$id;
        $product = $this->productModel->getProductById($id);
        if (!$product) { 
            flash('error_message', 'محصولی با این شناسه یافت نشد.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/products'); exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            $price_input = trim(filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
            $stock_quantity_input = trim(filter_input(INPUT_POST, 'stock_quantity', FILTER_SANITIZE_NUMBER_INT));
            $initial_stock_quantity_input = filter_input(INPUT_POST, 'initial_stock_quantity_edit', FILTER_SANITIZE_NUMBER_INT);
            $category_id_input = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
            $product_type_input = filter_input(INPUT_POST, 'product_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $vendor_id_input = filter_input(INPUT_POST, 'vendor_id', FILTER_SANITIZE_NUMBER_INT);
            $affiliate_commission_type_input = filter_input(INPUT_POST, 'affiliate_commission_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $affiliate_commission_value_input = filter_input(INPUT_POST, 'affiliate_commission_value', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $delete_current_image_input = filter_input(INPUT_POST, 'delete_current_image', FILTER_VALIDATE_BOOLEAN);

            $current_image_url = isset($_POST['current_image_url']) ? trim($_POST['current_image_url']) : ($product['image_url'] ?? '');
            
            $data = [
                'id' => $id, 'name' => $name, 'description' => $description, 'price' => $price_input,
                'stock_quantity' => $stock_quantity_input,
                'initial_stock_quantity' => $initial_stock_quantity_input !== null ? (int)$initial_stock_quantity_input : $product['initial_stock_quantity'],
                'category_id' => !empty($category_id_input) ? (int)$category_id_input : null,
                'product_type' => $product_type_input ?: 'simple',
                'vendor_id' => !empty($vendor_id_input) ? (int)$vendor_id_input : $product['vendor_id'],
                'affiliate_commission_type' => $affiliate_commission_type_input ?: ($product['affiliate_commission_type'] ?? 'none'),
                'affiliate_commission_value' => ($affiliate_commission_type_input !== 'none' && !empty($affiliate_commission_value_input)) ? (float)$affiliate_commission_value_input : ($product['affiliate_commission_value'] ?? null),
                'image_file' => $_FILES['product_image'] ?? null,
                'delete_current_image' => $delete_current_image_input,
                'image_url' => $current_image_url, 
                'name_err' => '', 'price_err' => '', 'stock_quantity_err' => '', 'image_err' => '', 'product_type_err' => '', 'affiliate_commission_err' => ''
            ];
            // ... (Validation logic) ...

            if (empty($data['name_err']) && empty($data['price_err']) && empty($data['stock_quantity_err']) && empty($data['image_err']) && empty($data['product_type_err']) && empty($data['affiliate_commission_err'])) {
                
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
                    if (isset($_POST['delete_gallery_images']) && is_array($_POST['delete_gallery_images'])) {
                        foreach ($_POST['delete_gallery_images'] as $image_id_to_delete) {
                            $this->productModel->deleteGalleryImage((int)$image_id_to_delete);
                        }
                    }
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
                    if (isset($_POST['existing_gallery_alt_texts']) && is_array($_POST['existing_gallery_alt_texts'])) {
                        foreach($_POST['existing_gallery_alt_texts'] as $img_id => $alt_text) {
                            $this->productModel->updateGalleryImageAltText((int)$img_id, trim($alt_text));
                        }
                    }

                    flash('product_action_success', 'محصول با موفقیت ویرایش شد.');
                    header('Location: ' . BASE_URL . 'admin/products'); 
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
        $data_for_view['gallery_images'] = $this->productModel->getGalleryImages($id); 
        $data_for_view['vendors'] = $this->userModel->getUsersByRole('vendor');
        
        if((isset($data_for_view['image_err']) && !empty($data_for_view['image_err'])) || (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == UPLOAD_ERR_NO_FILE && empty($data_for_view['image_url'])) ){
            $data_for_view['image_url'] = $product['image_url']; 
        }
        $this->view('admin/products/edit', $data_for_view);
    }
    
    public function deleteProduct($id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $id && is_numeric($id)) {
            $id = (int)$id;
            $product = $this->productModel->getProductById($id);
            if (!$product) {
                 flash('error_message', 'محصول مورد نظر برای حذف یافت نشد.', 'alert alert-danger');
                 header('Location: ' . BASE_URL . 'admin/products');
                 exit();
            }
            // Delete main image is handled by ProductModel->deleteProduct if it's implemented there
            // Gallery images are deleted via CASCADE or explicitly in ProductModel->deleteProduct
            if ($this->productModel->deleteProduct($id)) { 
                flash('product_deleted_success', 'محصول "' . htmlspecialchars($product['name']) . '" با موفقیت حذف شد.');
            } else {
                flash('product_action_fail', 'خطا در حذف محصول از پایگاه داده.', 'alert alert-danger');
            }
            header('Location: ' . BASE_URL . 'admin/products');
            exit();
        } else {
            flash('error_message', 'درخواست نامعتبر برای حذف محصول.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/products');
            exit();
        }
    }
    
    // --- Category Management ---
    public function categories() {
        $categories = $this->categoryModel->getAllCategories();
        $data = [
            'pageTitle' => 'مدیریت دسته‌بندی‌ها',
            'categories' => $categories
        ];
        $this->view('admin/categories/index', $data);
    }

    public function addCategory() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data = [
                'name' => trim($_POST['name']),
                'description' => trim($_POST['description']),
                'parent_id' => isset($_POST['parent_id']) ? trim($_POST['parent_id']) : '',
                'name_err' => ''
            ];
            if (empty($data['name'])) { $data['name_err'] = 'لطفاً نام دسته‌بندی را وارد کنید.';}

            if (empty($data['name_err'])) {
                if ($this->categoryModel->addCategory($data)) {
                    flash('category_action_success', 'دسته‌بندی جدید با موفقیت اضافه شد.');
                    header('Location: ' . BASE_URL . 'admin/categories');
                    exit();
                } else {
                    flash('category_action_fail', 'خطا در افزودن دسته‌بندی.', 'alert alert-danger');
                    $data['pageTitle'] = 'افزودن دسته‌بندی جدید';
                    $data['all_categories'] = $this->categoryModel->getAllCategories();
                    $this->view('admin/categories/add', $data);
                }
            } else {
                $data['pageTitle'] = 'افزودن دسته‌بندی جدید';
                $data['all_categories'] = $this->categoryModel->getAllCategories();
                flash('category_form_error', 'لطفاً خطاهای فرم را برطرف کنید.', 'alert alert-danger');
                $this->view('admin/categories/add', $data);
            }
        } else {
            $data = [
                'pageTitle' => 'افزودن دسته‌بندی جدید',
                'name' => '', 'description' => '', 'parent_id' => '',
                'all_categories' => $this->categoryModel->getAllCategories(),
                'name_err' => ''
            ];
            $this->view('admin/categories/add', $data);
        }
    }

    public function editCategory($id = null) {
        if (is_null($id) || !is_numeric($id)) {
            flash('error_message', 'شناسه دسته‌بندی نامعتبر است.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/categories');
            exit();
        }
        $id = (int)$id;
        $category_for_initial_data = $this->categoryModel->getCategoryById($id);
         if (!$category_for_initial_data && $_SERVER['REQUEST_METHOD'] !== 'POST') {
             flash('error_message', 'دسته‌بندی مورد نظر یافت نشد.', 'alert alert-danger');
             header('Location: ' . BASE_URL . 'admin/categories'); exit();
        }


        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data = [
                'id' => $id,
                'name' => trim($_POST['name']),
                'description' => trim($_POST['description']),
                'parent_id' => isset($_POST['parent_id']) ? trim($_POST['parent_id']) : '',
                'name_err' => ''
            ];
            if (empty($data['name'])) { $data['name_err'] = 'لطفاً نام دسته‌بندی را وارد کنید.';}

            if (empty($data['name_err'])) {
                if ($this->categoryModel->updateCategory($data)) {
                    flash('category_action_success', 'دسته‌بندی با موفقیت ویرایش شد.');
                    header('Location: ' . BASE_URL . 'admin/categories');
                    exit();
                } else {
                    flash('category_action_fail', 'خطا در ویرایش دسته‌بندی.', 'alert alert-danger');
                    // $data['pageTitle'] = 'ویرایش دسته‌بندی: ' . htmlspecialchars($data['name']); 
                    // $data['all_categories'] = $this->categoryModel->getAllCategories();
                    // $this->view('admin/categories/edit', $data);
                }
            }
            // اگر خطا وجود داشت یا آپدیت ناموفق بود، فرم را با داده‌های فعلی و خطاها نمایش بده
            $data['pageTitle'] = 'ویرایش دسته‌بندی: ' . ($category_for_initial_data ? htmlspecialchars($category_for_initial_data['name']) : htmlspecialchars($data['name']));
            $data['all_categories'] = $this->categoryModel->getAllCategories();
            if(!empty($data['name_err'])) flash('category_form_error', $data['name_err'], 'alert alert-danger');
            $this->view('admin/categories/edit', $data);
            exit();

        } else { // GET
            $category = $category_for_initial_data;
            $data = [
                'pageTitle' => 'ویرایش دسته‌بندی: ' . htmlspecialchars($category['name']),
                'id' => $category['id'],
                'name' => $category['name'],
                'description' => $category['description'],
                'parent_id' => $category['parent_id'],
                'all_categories' => $this->categoryModel->getAllCategories(), 
                'name_err' => ''
            ];
            $this->view('admin/categories/edit', $data);
        }
    }

    public function deleteCategory($id = null) {
         if ($_SERVER['REQUEST_METHOD'] == 'POST' && $id && is_numeric($id)) {
            $id = (int)$id;
            $category = $this->categoryModel->getCategoryById($id);
            if (!$category) {
                flash('error_message', 'دسته‌بندی مورد نظر یافت نشد.', 'alert alert-danger');
                header('Location: ' . BASE_URL . 'admin/categories');
                exit();
            }
            if ($this->categoryModel->deleteCategory($id)) {
                flash('category_action_success', 'دسته‌بندی "' . htmlspecialchars($category['name']) . '" با موفقیت حذف شد.');
            } else {
                flash('category_action_fail', 'خطا در حذف دسته‌بندی. ممکن است محصولاتی به این دسته‌بندی متصل باشند.', 'alert alert-danger');
            }
            header('Location: ' . BASE_URL . 'admin/categories');
            exit();
        } else {
            flash('error_message', 'درخواست نامعتبر برای حذف دسته‌بندی.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/categories');
            exit();
        }
    }
    
    // --- Attribute Management ---
    public function attributes() {
        $attributes = $this->attributeModel->getAllAttributes(); 
        $data = [
            'pageTitle' => 'مدیریت ویژگی‌ها',
            'attributes' => $attributes
        ];
        $this->view('admin/attributes/index', $data);
    }

    public function addAttribute() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data = [
                'name' => trim($_POST['name']),
                'name_err' => ''
            ];
            if (empty($data['name'])) {
                $data['name_err'] = 'لطفاً نام ویژگی را وارد کنید.';
            } elseif ($this->attributeModel->getAttributeByName($data['name'])) { 
                 $data['name_err'] = 'این نام ویژگی قبلاً ثبت شده است.';
            }

            if (empty($data['name_err'])) {
                if ($this->attributeModel->addAttribute($data)) {
                    flash('attribute_action_success', 'ویژگی جدید با موفقیت اضافه شد.');
                    header('Location: ' . BASE_URL . 'admin/attributes');
                    exit();
                } else {
                    flash('attribute_action_fail', 'خطا در افزودن ویژگی.', 'alert alert-danger');
                }
            }
            $data['pageTitle'] = 'افزودن ویژگی جدید';
            if(!empty($data['name_err'])) flash('attribute_form_error', $data['name_err'], 'alert alert-danger');
            $this->view('admin/attributes/add', $data);
            exit(); // Ensure no further processing after rendering form with errors

        } else { 
            $data = [
                'pageTitle' => 'افزودن ویژگی جدید',
                'name' => '',
                'name_err' => ''
            ];
            $this->view('admin/attributes/add', $data);
        }
    }
    
    public function editAttribute($id = null) {
        if (is_null($id) || !is_numeric($id)) { header('Location: ' . BASE_URL . 'admin/attributes'); exit(); }
        $id = (int)$id;
        $originalAttribute = $this->attributeModel->getAttributeById($id); // Get current attribute for title
        if (!$originalAttribute && $_SERVER['REQUEST_METHOD'] !== 'POST') { // If not found on GET, redirect
             flash('error_message', 'ویژگی مورد نظر یافت نشد.', 'alert alert-danger');
             header('Location: ' . BASE_URL . 'admin/attributes'); exit();
        }


        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data = [
                'id' => $id,
                'name' => trim($_POST['name']),
                'name_err' => ''
            ];
            // $originalAttribute was fetched above or needs to be fetched if not a POST initially
            if (!$originalAttribute) $originalAttribute = $this->attributeModel->getAttributeById($id);


            if (empty($data['name'])) {
                $data['name_err'] = 'لطفاً نام ویژگی را وارد کنید.';
            } elseif ($originalAttribute && $data['name'] !== $originalAttribute['name'] && $this->attributeModel->getAttributeByName($data['name'])) {
                 $data['name_err'] = 'این نام ویژگی قبلاً برای ویژگی دیگری ثبت شده است.';
            }

            if (empty($data['name_err'])) {
                if ($this->attributeModel->updateAttribute($data)) {
                    flash('attribute_action_success', 'ویژگی با موفقیت ویرایش شد.');
                    header('Location: ' . BASE_URL . 'admin/attributes');
                    exit();
                } else {
                    flash('attribute_action_fail', 'خطا در ویرایش ویژگی.', 'alert alert-danger');
                }
            }
            $data['pageTitle'] = 'ویرایش ویژگی: ' . ($originalAttribute ? htmlspecialchars($originalAttribute['name']) : '');
            if(!empty($data['name_err'])) flash('attribute_form_error', $data['name_err'], 'alert alert-danger');
            $this->view('admin/attributes/edit', $data);
            exit();

        } else { // GET request
            $attribute = $originalAttribute; // Already fetched
            $data = [
                'pageTitle' => 'ویرایش ویژگی: ' . htmlspecialchars($attribute['name']),
                'id' => $attribute['id'],
                'name' => $attribute['name'],
                'name_err' => ''
            ];
            $this->view('admin/attributes/edit', $data);
        }
    }

    public function deleteAttribute($id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $id && is_numeric($id)) {
            $id = (int)$id;
            $attribute = $this->attributeModel->getAttributeById($id);
            if (!$attribute) { 
                flash('error_message', 'ویژگی مورد نظر یافت نشد.', 'alert alert-danger');
                header('Location: ' . BASE_URL . 'admin/attributes');
                exit();
            }

            if ($this->attributeModel->deleteAttribute($id)) {
                flash('attribute_action_success', 'ویژگی "' . htmlspecialchars($attribute['name']) . '" و مقادیر مرتبط با آن با موفقیت حذف شدند.');
            } else {
                flash('attribute_action_fail', 'خطا در حذف ویژگی.', 'alert alert-danger');
            }
            header('Location: ' . BASE_URL . 'admin/attributes');
            exit();
        } else {
            flash('error_message', 'درخواست نامعتبر.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/attributes');
            exit();
        }
    }

    // --- Attribute Value Management ---
    public function attributeValues($attribute_id = null) {
        if (is_null($attribute_id) || !is_numeric($attribute_id)) { header('Location: ' . BASE_URL . 'admin/attributes'); exit(); }
        $attribute_id = (int)$attribute_id;
        $attribute = $this->attributeModel->getAttributeById($attribute_id);
        if (!$attribute) { 
            flash('error_message', 'ویژگی والد یافت نشد.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/attributes'); exit(); 
        }

        $values = $this->attributeModel->getValuesByAttributeId($attribute_id);
        $data = [
            'pageTitle' => 'مدیریت مقادیر برای ویژگی: ' . htmlspecialchars($attribute['name']),
            'attribute' => $attribute,
            'values' => $values,
            'value_input' => '', 
            'value_err' => ''
        ];
        $this->view('admin/attributes/values_index', $data);
    }

    public function addAttributeValue($attribute_id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $attribute_id && is_numeric($attribute_id)) {
            $attribute_id = (int)$attribute_id;
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $value_input = trim($_POST['value']);
            $value_err = '';

            if (empty($value_input)) {
                $value_err = 'لطفاً مقدار ویژگی را وارد کنید.';
            }
            
            if (empty($value_err)) {
                $data_to_add = ['attribute_id' => $attribute_id, 'value' => $value_input];
                if ($this->attributeModel->addAttributeValue($data_to_add)) {
                    flash('attribute_value_action_success', 'مقدار جدید با موفقیت به ویژگی اضافه شد.');
                } else {
                    flash('attribute_value_action_fail', 'خطا در افزودن مقدار. ممکن است این مقدار قبلاً برای این ویژگی ثبت شده باشد.', 'alert alert-danger');
                }
            } else {
                 flash('attribute_value_form_error', $value_err, 'alert alert-danger');
            }
            header('Location: ' . BASE_URL . 'admin/attributeValues/' . $attribute_id);
            exit();
        } else {
            // Redirect if not a POST request or invalid attribute_id
            $redirect_to = $attribute_id && is_numeric($attribute_id) ? 'admin/attributeValues/' . $attribute_id : 'admin/attributes';
            header('Location: ' . BASE_URL . $redirect_to);
            exit();
        }
    }
    
    public function editAttributeValue($value_id = null) {
        if (is_null($value_id) || !is_numeric($value_id)) { header('Location: ' . BASE_URL . 'admin/attributes'); exit(); }
        $value_id = (int)$value_id;
        $attributeValue = $this->attributeModel->getAttributeValueById($value_id);
        if (!$attributeValue) { 
            flash('error_message', 'مقدار ویژگی یافت نشد.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/attributes'); exit(); 
        }
        $attribute = $this->attributeModel->getAttributeById($attributeValue['attribute_id']); // For page title and redirect

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $data = [
                'id' => $value_id,
                'value' => trim($_POST['value']),
                'attribute_id' => $attributeValue['attribute_id'], 
                'value_err' => ''
            ];

            if (empty($data['value'])) {
                $data['value_err'] = 'مقدار ویژگی نمی‌تواند خالی باشد.';
            }
            
            if (empty($data['value_err'])) {
                if ($this->attributeModel->updateAttributeValue($data)) {
                    flash('attribute_value_action_success', 'مقدار ویژگی با موفقیت ویرایش شد.');
                    header('Location: ' . BASE_URL . 'admin/attributeValues/' . $data['attribute_id']);
                    exit();
                } else {
                    flash('attribute_value_action_fail', 'خطا در ویرایش مقدار ویژگی. ممکن است این مقدار قبلاً برای این ویژگی ثبت شده باشد.', 'alert alert-danger');
                }
            }
            
            $data['pageTitle'] = 'ویرایش مقدار برای ویژگی: ' . ($attribute ? htmlspecialchars($attribute['name']) : '');
            $data['current_value'] = $attributeValue['value']; // For form repopulation if needed
            $data['attribute_name'] = ($attribute ? $attribute['name'] : ''); // For display in view
            if(!empty($data['value_err'])) flash('attribute_value_form_error', $data['value_err'], 'alert alert-danger');
            $this->view('admin/attributes/edit_value', $data);
            exit();

        } else { // GET request
            $data = [
                'pageTitle' => 'ویرایش مقدار برای ویژگی: ' . ($attribute ? htmlspecialchars($attribute['name']) : ''),
                'id' => $attributeValue['id'],
                'value' => $attributeValue['value'],
                'attribute_id' => $attributeValue['attribute_id'],
                'attribute_name' => ($attribute ? $attribute['name'] : ''),
                'value_err' => ''
            ];
            $this->view('admin/attributes/edit_value', $data);
        }
    }

    public function deleteAttributeValue($value_id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $value_id && is_numeric($value_id)) {
            $value_id = (int)$value_id;
            $attributeValue = $this->attributeModel->getAttributeValueById($value_id); 
            if (!$attributeValue) { 
                flash('error_message', 'مقدار ویژگی مورد نظر یافت نشد.', 'alert alert-danger');
                header('Location: ' . BASE_URL . 'admin/attributes'); 
                exit();
            }
            $redirect_attribute_id = $attributeValue['attribute_id']; // Store before delete

            if ($this->attributeModel->deleteAttributeValue($value_id)) {
                flash('attribute_value_action_success', 'مقدار ویژگی با موفقیت حذف شد.');
            } else {
                flash('attribute_value_action_fail', 'خطا در حذف مقدار ویژگی.', 'alert alert-danger');
            }
            header('Location: ' . BASE_URL . 'admin/attributeValues/' . $redirect_attribute_id);
            exit();
        } else {
            // Redirect if not POST or invalid value_id
            $redirect_to = 'admin/attributes';
            if ($value_id && is_numeric($value_id) && ($attrVal = $this->attributeModel->getAttributeValueById((int)$value_id))) {
                $redirect_to = 'admin/attributeValues/' . $attrVal['attribute_id'];
            }
            flash('error_message', 'درخواست نامعتبر.', 'alert alert-danger');
            header('Location: ' . BASE_URL . $redirect_to);
            exit();
        }
    }

    // --- Product Variation Management ---
    public function manageProductVariations($parent_product_id = null) {
        if (is_null($parent_product_id) || !is_numeric($parent_product_id)) {
            flash('error_message', 'شناسه محصول والد نامعتبر است.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/products');
            exit();
        }
        $parent_product_id = (int)$parent_product_id;
        $parentProduct = $this->productModel->getProductById($parent_product_id);

        if (!$parentProduct || $parentProduct['product_type'] !== 'variable') {
            flash('error_message', 'محصول والد یافت نشد یا از نوع متغیر نیست.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/products');
            exit();
        }

        $configurableAttributes = $this->attributeModel->getConfigurableAttributeDetailsForProduct($parent_product_id);
        $existingVariations = $this->attributeModel->getVariationsForProduct($parent_product_id);

        $data = [
            'pageTitle' => 'مدیریت تنوع‌ها برای محصول: ' . htmlspecialchars($parentProduct['name']),
            'parentProduct' => $parentProduct,
            'configurableAttributes' => $configurableAttributes,
            'existingVariations' => $existingVariations,
            'variation_sku' => '', 'variation_price' => '', 'variation_stock' => '0', 'variation_initial_stock' => '0',
            'variation_image_url' => '', 'selected_attributes' => []
        ];
        $this->view('admin/products/manage_variations', $data);
    }

    public function addVariation($parent_product_id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $parent_product_id && is_numeric($parent_product_id)) {
            $parent_product_id = (int)$parent_product_id;
            $parentProduct = $this->productModel->getProductById($parent_product_id);
            if (!$parentProduct || $parentProduct['product_type'] !== 'variable') {
                flash('error_message', 'محصول والد نامعتبر.', 'alert alert-danger');
                header('Location: ' . BASE_URL . 'admin/products'); exit();
            }

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $stock_input = isset($_POST['variation_stock']) ? trim($_POST['variation_stock']) : '0';
            $variation_data = [
                'parent_product_id' => $parent_product_id,
                'sku' => isset($_POST['variation_sku']) ? trim($_POST['variation_sku']) : null,
                'price' => isset($_POST['variation_price']) ? trim($_POST['variation_price']) : null,
                'stock_quantity' => $stock_input,
                'initial_stock_quantity' => $stock_input, // Set initial stock same as current stock on creation
                'image_url' => null, // Placeholder for future image upload for variation
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
                    } elseif (strpos($result, 'db_error') === 0 || $result === 'pdo_exception' || $result === 'general_exception') {
                        $errorMessage = 'خطای داخلی سرور هنگام افزودن تنوع. لطفاً لاگ‌ها را بررسی کنید.';
                    }
                    flash('variation_action_fail', $errorMessage, 'alert alert-danger');
                }
            }
            header('Location: ' . BASE_URL . 'admin/manageProductVariations/' . $parent_product_id);
            exit();
        } else {
            header('Location: ' . BASE_URL . 'admin/products');
            exit();
        }
    }
    
    public function deleteVariation($variation_id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $variation_id && is_numeric($variation_id)) {
            $variation_id = (int)$variation_id;
            $variation = $this->attributeModel->getVariationById($variation_id); 

            if (!$variation) {
                flash('variation_action_fail', 'تنوع مورد نظر یافت نشد.', 'alert alert-danger');
                header('Location: ' . BASE_URL . 'admin/products'); // Or a more relevant page
                exit();
            }
            
            // Placeholder for deleting variation-specific image if implemented
            // if (!empty($variation['image_url']) && defined('FCPATH') && file_exists(FCPATH . $variation['image_url'])) {
            //     unlink(FCPATH . $variation['image_url']);
            // }

            if ($this->attributeModel->deleteVariation($variation_id)) {
                flash('variation_action_success', 'تنوع با موفقیت حذف شد.');
            } else {
                flash('variation_action_fail', 'خطا در حذف تنوع.', 'alert alert-danger');
            }
            header('Location: ' . BASE_URL . 'admin/manageProductVariations/' . $variation['parent_product_id']);
            exit();
        } else {
            flash('error_message', 'درخواست نامعتبر.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/products');
            exit();
        }
    }


    /**
     * نمایش فرم ویرایش یک تنوع محصول و پردازش آن
     * URL: admin/editVariation/VARIATION_ID
     */
    public function editVariation($variation_id = null) {
        if (is_null($variation_id) || !is_numeric($variation_id)) {
            flash('error_message', 'شناسه تنوع نامعتبر است.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/products'); // یا یک صفحه مناسب‌تر
            exit();
        }
        $variation_id = (int)$variation_id;
        $variation = $this->attributeModel->getVariationById($variation_id);

        if (!$variation) {
            flash('error_message', 'تنوع مورد نظر یافت نشد.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/products'); // یا یک صفحه مناسب‌تر
            exit();
        }
        
        $parentProduct = $this->productModel->getProductById($variation['parent_product_id']);
        if (!$parentProduct) { // این حالت نباید رخ دهد اگر داده‌ها سازگار باشند
            flash('error_message', 'محصول والد برای این تنوع یافت نشد.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/products');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $current_variation_image_url = isset($_POST['current_variation_image_url']) ? trim($_POST['current_variation_image_url']) : ($variation['image_url'] ?? null);

            $data_to_update = [
                'sku' => isset($_POST['variation_sku']) ? trim($_POST['variation_sku']) : null,
                'price' => isset($_POST['variation_price']) && $_POST['variation_price'] !== '' ? trim($_POST['variation_price']) : null,
                'stock_quantity' => isset($_POST['variation_stock']) ? (int)$_POST['variation_stock'] : 0,
                'image_url' => $current_variation_image_url, // پیش‌فرض تصویر فعلی
                'is_active' => isset($_POST['variation_is_active']) ? 1 : 0,
                // initial_stock_quantity معمولاً در ویرایش تغییر نمی‌کند مگر اینکه فیلد جداگانه برای آن بگذاریم
                // 'initial_stock_quantity' => isset($_POST['variation_initial_stock']) ? (int)$_POST['variation_initial_stock'] : $variation['initial_stock_quantity'],
            ];
            
            $errors = [];
            if (!empty($data_to_update['price']) && (!is_numeric($data_to_update['price']) || (float)$data_to_update['price'] < 0)) {
                $errors['price_err'] = 'قیمت تنوع نامعتبر است.';
            }
            if ($data_to_update['stock_quantity'] < 0) {
                $errors['stock_err'] = 'موجودی تنوع نمی‌تواند منفی باشد.';
            }
            // می‌توان اعتبارسنجی برای یکتایی SKU اضافه کرد (در صورت استفاده)

            // پردازش آپلود تصویر جدید برای تنوع (اگر فایلی انتخاب شده باشد)
            $new_image_uploaded_path = null;
            if (isset($_FILES['variation_image']) && $_FILES['variation_image']['error'] == UPLOAD_ERR_OK) {
                if (!defined('FCPATH')) { $errors['image_err'] = 'خطای پیکربندی: FCPATH تعریف نشده است.'; }
                if (empty($errors['image_err'])) { 
                    $variation_upload_dir_abs = FCPATH . $this->variationUploadDir;
                    if (!is_dir($variation_upload_dir_abs)) { 
                        if (!mkdir($variation_upload_dir_abs, 0775, true)) { $errors['image_err'] = 'خطا در ایجاد پوشه آپلود تنوع.';}
                    }
                    if (empty($errors['image_err'])) { 
                        $file_info = pathinfo($_FILES["variation_image"]["name"]);
                        $file_type = strtolower($file_info['extension']);
                        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];  
                        $max_file_size = 2 * 1024 * 1024; // 2MB

                        if (!in_array($file_type, $allowed_types)) { $errors['image_err'] = 'فرمت‌های JPG, JPEG, PNG, GIF مجاز هستند.';}
                        elseif ($_FILES["variation_image"]["size"] > $max_file_size) { $errors['image_err'] = 'حجم فایل بیشتر از ۲ مگابایت نباشد.';}
                        else {
                            $new_var_file_name = uniqid('variation_', true) . '.' . $file_type;
                            if (move_uploaded_file($_FILES["variation_image"]["tmp_name"], $variation_upload_dir_abs . $new_var_file_name)) {
                                $new_image_uploaded_path = $this->variationUploadDir . $new_var_file_name;
                            } else { $errors['image_err'] = 'خطا در آپلود فایل تصویر تنوع.'; }
                        }
                    }
                }
            } elseif (isset($_FILES['variation_image']) && $_FILES['variation_image']['error'] != UPLOAD_ERR_NO_FILE) {
                $errors['image_err'] = 'خطایی در آپلود فایل تنوع رخ داد. کد: ' . $_FILES['variation_image']['error'];
            }

            if (!empty($new_image_uploaded_path)) {
                // اگر تصویر جدید با موفقیت آپلود شد، تصویر قدیمی (اگر وجود داشت) را حذف کن
                if (!empty($current_variation_image_url) && defined('FCPATH') && file_exists(FCPATH . $current_variation_image_url)) {
                    unlink(FCPATH . $current_variation_image_url);
                }
                $data_to_update['image_url'] = $new_image_uploaded_path;
            } // در غیر این صورت، image_url همان مقدار قبلی (current_variation_image_url) باقی می‌ماند


            if (empty($errors)) {
                // ویژگی‌های این تنوع تغییر نمی‌کنند، فقط مقادیر خود تنوع آپدیت می‌شوند
                // متد updateVariation در مدل ProductAttribute، فقط $data (مقادیر تنوع) را می‌گیرد، نه $attributesData
                // زیرا ترکیب ویژگی‌های یک تنوع معمولاً ثابت است. اگر بخواهیم تغییر دهیم، باید تنوع را حذف و دوباره ایجاد کنیم.
                if ($this->attributeModel->updateVariation($variation_id, $data_to_update, [] /* آرایه خالی برای attributesData */)) {
                    flash('variation_action_success', 'تنوع با موفقیت ویرایش شد.');
                    header('Location: ' . BASE_URL . 'admin/manageProductVariations/' . $variation['parent_product_id']);
                    exit();
                } else {
                    flash('variation_action_fail', 'خطا در ویرایش تنوع در پایگاه داده.', 'alert alert-danger');
                    // اگر آپلود تصویر جدید موفق بود اما ذخیره در دیتابیس شکست خورد، تصویر جدید را حذف کن
                    if ($new_image_uploaded_path && $data_to_update['image_url'] === $new_image_uploaded_path && defined('FCPATH') && file_exists(FCPATH . $new_image_uploaded_path)) {
                        unlink(FCPATH . $new_image_uploaded_path);
                        $data_to_update['image_url'] = $current_variation_image_url; // بازگرداندن به تصویر قبلی
                    }
                }
            }
            // اگر خطا وجود دارد، فرم را با خطاها و داده‌های فعلی (که ممکن است شامل تصویر جدید آپلود شده باشد) نمایش بده
            $data_for_view = [
                'pageTitle' => 'ویرایش تنوع برای: ' . htmlspecialchars($parentProduct['name']),
                'parentProduct' => $parentProduct,
                'variation' => array_merge($variation, $data_to_update), // ترکیب داده‌های فعلی با داده‌های فرم
                'errors' => $errors
            ];
             if(!empty($errors)) {
                 $error_msg_combined = "";
                 foreach($errors as $err_key => $err_val) $error_msg_combined .= $err_val . "<br>";
                 flash('variation_form_error', $error_msg_combined, 'alert alert-danger');
             }
            $this->view('admin/products/edit_variation', $data_for_view);


        } else { // درخواست GET
            $data = [
                'pageTitle' => 'ویرایش تنوع برای: ' . htmlspecialchars($parentProduct['name']),
                'parentProduct' => $parentProduct,
                'variation' => $variation, // شامل $variation['attributes']
                'errors' => []
            ];
            $this->view('admin/products/edit_variation', $data);
        }
    }
    
   
    // --- Admin Payout Management ---
    public function payoutRequests() {
        $payouts = $this->orderModel->getAllPayoutRequests();
        $data = [
            'pageTitle' => 'درخواست‌های تسویه حساب فروشندگان',
            'payouts' => $payouts
        ];
        $this->view('admin/payouts/index', $data);
    }

    public function processPayout($payout_id = null) {
        if (is_null($payout_id) || !is_numeric($payout_id)) {
            flash('error_message', 'شناسه درخواست تسویه نامعتبر است.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/payoutRequests');
            exit();
        }
        $payout_id = (int)$payout_id;
        $payoutRequest = $this->orderModel->getPayoutRequestById($payout_id);

        if (!$payoutRequest) {
            flash('error_message', 'درخواست تسویه مورد نظر یافت نشد.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/payoutRequests');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            $new_status = isset($_POST['payout_status']) ? trim($_POST['payout_status']) : $payoutRequest['status'];
            $payout_amount_paid = isset($_POST['payout_amount_paid']) && $_POST['payout_amount_paid'] !== '' ? (float)trim($_POST['payout_amount_paid']) : null;
            $admin_notes = isset($_POST['admin_notes']) ? trim($_POST['admin_notes']) : null;
            $payment_details_admin_input = isset($_POST['payment_details_admin']) ? trim($_POST['payment_details_admin']) : null;
            $admin_user_id_processing = $_SESSION['user_id']; 

            $allowed_statuses = ['requested', 'processing', 'completed', 'rejected', 'cancelled'];

            if (!in_array($new_status, $allowed_statuses)) {
                flash('error_message', 'وضعیت انتخاب شده نامعتبر است.', 'alert alert-danger');
            } elseif ($new_status === 'completed' && ($payout_amount_paid === null || $payout_amount_paid <= 0)) {
                flash('error_message', 'برای وضعیت "تکمیل شده"، مبلغ پرداخت شده باید وارد شود و بیشتر از صفر باشد.', 'alert alert-danger');
            } else {
                if ($new_status === 'completed' && $payout_amount_paid === null) {
                     $payout_amount_paid = (float)$payoutRequest['requested_amount'];
                }

                if ($this->orderModel->processVendorPayout(
                    $payout_id, 
                    $new_status, 
                    $admin_user_id_processing, 
                    $payout_amount_paid,       
                    $admin_notes,              
                    $payment_details_admin_input 
                )) {
                    flash('payout_processed_success', 'درخواست تسویه با موفقیت پردازش شد.');
                } else {
                    flash('payout_processed_fail', 'خطا در پردازش درخواست تسویه.', 'alert alert-danger');
                }
            }
            header('Location: ' . BASE_URL . 'admin/processPayout/' . $payout_id); 
            exit();

        } else { // GET request
            $payout_order_items = [];
            if (method_exists($this->orderModel, 'getOrderItemsByPayoutId')) {
                 $payout_order_items = $this->orderModel->getOrderItemsByPayoutId($payout_id);
            } else {
                error_log("AdminController::processPayout - Method getOrderItemsByPayoutId does not exist in OrderModel.");
            }

            $data = [
                'pageTitle' => 'پردازش درخواست تسویه #' . htmlspecialchars($payoutRequest['id']) . ' برای ' . htmlspecialchars($payoutRequest['vendor_full_name'] ?? $payoutRequest['vendor_username']),
                'payoutRequest' => $payoutRequest,
                'payoutOrderItems' => $payout_order_items ?: []
            ];
            $this->view('admin/payouts/process', $data);
        }
    }

    // --- Platform Commissions Management ---
    public function platformCommissions() {
        $orders_with_commission = $this->orderModel->getOrdersWithPlatformCommission();
        $grand_total_commission = $this->orderModel->getTotalPlatformCommission();

        $data = [
            'pageTitle' => 'گزارش کمیسیون‌های فروشگاه',
            'orders_with_commission' => $orders_with_commission,
            'grand_total_commission' => $grand_total_commission
        ];
        $this->view('admin/commissions/index', $data);
    }

    // --- Reports and Exports ---
    public function reports() {
        $data = [
            'pageTitle' => 'گزارشات و خروجی اکسل'
        ];
        $this->view('admin/reports/index', $data);
    }

    public function exportProducts() {
        if (ob_get_level()) { ob_end_clean(); } 

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $start_date_input = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
            $end_date_input = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

            $start_date_gregorian = null;
            if ($start_date_input && function_exists('to_gregorian_date')) {
                $start_date_gregorian = to_gregorian_date($start_date_input);
                if ($start_date_gregorian) $start_date_gregorian .= ' 00:00:00';
            } elseif ($start_date_input) {
                 $start_date_gregorian = $start_date_input . ' 00:00:00'; 
            }

            $end_date_gregorian = null;
            if ($end_date_input && function_exists('to_gregorian_date')) {
                $end_date_gregorian = to_gregorian_date($end_date_input);
                if ($end_date_gregorian) $end_date_gregorian .= ' 23:59:59';
            } elseif ($end_date_input) {
                 $end_date_gregorian = $end_date_input . ' 23:59:59';
            }
            
            $products_data = $this->productModel->getProductsForExport($start_date_gregorian, $end_date_gregorian);

            if (empty($products_data)) {
                flash('report_message', 'هیچ محصولی برای بازه زمانی انتخاب شده یافت نشد.', 'alert alert-info');
                header('Location: ' . BASE_URL . 'admin/reports');
                exit();
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('لیست محصولات و تنوع ها');
            $sheet->setRightToLeft(true);

            $headers = [
                'شناسه محصول والد', 'نام محصول والد', 'نوع محصول', 'دسته', 'فروشنده', 'تاریخ ایجاد محصول',
                'شناسه تنوع', 'ویژگی‌های تنوع', 'SKU تنوع',
                'قیمت (تومان)', 'موجودی اولیه', 'موجودی فعلی', 'فروش', 'باقی‌مانده (از اولیه)',
                'توضیحات محصول'
            ];
            $column = 'A';
            foreach ($headers as $header_title) { 
                $sheet->setCellValue($column . '1', $header_title);
                $column++;
            }
            $lastHeaderColumn = chr(ord($column)-1);
            $sheet->getStyle('A1:' . $lastHeaderColumn . '1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Vazirmatn'],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4A86E8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);

            $rowNumber = 2; 
            foreach ($products_data as $product) {
                if (isset($product['product_type']) && $product['product_type'] === 'variable') {
                    $variations = $this->attributeModel->getVariationsForProduct($product['id']);
                    if (!empty($variations)) {
                        foreach ($variations as $variation) {
                            $attrs_display_var = [];
                            if (!empty($variation['attributes'])) {
                                foreach($variation['attributes'] as $attr_val) {
                                    $attrs_display_var[] = ($attr_val['attribute_name'] ?? '') . ':' . ($attr_val['attribute_value'] ?? '');
                                }
                            }

                            $sheet->setCellValue('A' . $rowNumber, $product['id'] ?? '');
                            $sheet->setCellValue('B' . $rowNumber, $product['name'] ?? '');
                            $sheet->setCellValue('C' . $rowNumber, 'متغیر');
                            $sheet->setCellValue('D' . $rowNumber, $product['category_name'] ?? '-');
                            $sheet->setCellValue('E' . $rowNumber, (isset($product['vendor_full_name']) && !empty(trim($product['vendor_full_name']))) ? $product['vendor_full_name'] : ($product['vendor_username'] ?? 'فروشگاه'));
                            $sheet->setCellValue('F' . $rowNumber, isset($product['created_at']) ? to_jalali_datetime($product['created_at']) : '');
                            
                            $sheet->setCellValue('G' . $rowNumber, $variation['id'] ?? '');
                            $sheet->setCellValue('H' . $rowNumber, implode(' | ', $attrs_display_var)); 
                            $sheet->setCellValue('I' . $rowNumber, $variation['sku'] ?? '');
                            $sheet->setCellValue('J' . $rowNumber, ($variation['price'] !== null) ? (float)$variation['price'] : '');
                            $sheet->setCellValue('K' . $rowNumber, (int)($variation['initial_stock_quantity'] ?? 0));
                            $sheet->setCellValue('L' . $rowNumber, (int)($variation['current_stock_quantity'] ?? 0));
                            $sheet->setCellValue('M' . $rowNumber, (int)($variation['sales_count'] ?? 0));
                            $sheet->setCellValue('N' . $rowNumber, (int)($variation['remaining_stock_from_initial'] ?? 0));
                            $sheet->setCellValue('O' . $rowNumber, isset($product['description']) ? strip_tags($product['description']) : '');
                            
                            $sheet->getStyle('J' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');
                            $sheet->getStyle('A' . $rowNumber . ':' . $lastHeaderColumn . $rowNumber)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                            $sheet->getStyle('A' . $rowNumber . ':' . $lastHeaderColumn . $rowNumber)->getFont()->setName('Vazirmatn');
                            $rowNumber++;
                        }
                    } else { 
                        // ردیف برای محصول متغیر بدون تنوع
                        $sheet->setCellValue('A' . $rowNumber, $product['id'] ?? '');
                        $sheet->setCellValue('B' . $rowNumber, $product['name'] ?? '');
                        $sheet->setCellValue('C' . $rowNumber, 'متغیر (بدون تنوع)');
                        $sheet->setCellValue('D' . $rowNumber, $product['category_name'] ?? '-');
                        $sheet->setCellValue('E' . $rowNumber, (isset($product['vendor_full_name']) && !empty(trim($product['vendor_full_name']))) ? $product['vendor_full_name'] : ($product['vendor_username'] ?? 'فروشگاه'));
                        $sheet->setCellValue('F' . $rowNumber, isset($product['created_at']) ? to_jalali_datetime($product['created_at']) : '');
                        for ($col_idx = ord('G'); $col_idx <= ord('N'); $col_idx++) {
                            $sheet->setCellValue(chr($col_idx) . $rowNumber, '-');
                        }
                        $sheet->setCellValue('O' . $rowNumber, isset($product['description']) ? strip_tags($product['description']) : '');
                        $sheet->getStyle('A' . $rowNumber . ':' . $lastHeaderColumn . $rowNumber)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                        $sheet->getStyle('A' . $rowNumber . ':' . $lastHeaderColumn . $rowNumber)->getFont()->setName('Vazirmatn');
                        $rowNumber++;
                    }
                } else { // محصول ساده
                    $sales_count = $this->productModel->getSalesCount($product['id']);
                    $initial_stock = isset($product['initial_stock_quantity']) ? (int)$product['initial_stock_quantity'] : 0;
                    $current_stock = isset($product['stock_quantity']) ? (int)$product['stock_quantity'] : 0;
                    $remaining_stock = $initial_stock - $sales_count;
                    if ($remaining_stock < 0) $remaining_stock = 0;

                    $sheet->setCellValue('A' . $rowNumber, $product['id'] ?? '');
                    $sheet->setCellValue('B' . $rowNumber, $product['name'] ?? '');
                    $sheet->setCellValue('C' . $rowNumber, 'ساده');
                    $sheet->setCellValue('D' . $rowNumber, $product['category_name'] ?? '-');
                    $sheet->setCellValue('E' . $rowNumber, (isset($product['vendor_full_name']) && !empty(trim($product['vendor_full_name']))) ? $product['vendor_full_name'] : ($product['vendor_username'] ?? 'فروشگاه'));
                    $sheet->setCellValue('F' . $rowNumber, isset($product['created_at']) ? to_jalali_datetime($product['created_at']) : '');
                    for ($col_idx = ord('G'); $col_idx <= ord('I'); $col_idx++) {
                        $sheet->setCellValue(chr($col_idx) . $rowNumber, ''); // ستون‌های مربوط به تنوع خالی هستند
                    }
                    $sheet->setCellValue('J' . $rowNumber, ($product['price'] !== null) ? (float)$product['price'] : '');
                    $sheet->setCellValue('K' . $rowNumber, $initial_stock);
                    $sheet->setCellValue('L' . $rowNumber, $current_stock);
                    $sheet->setCellValue('M' . $rowNumber, $sales_count);
                    $sheet->setCellValue('N' . $rowNumber, $remaining_stock);
                    $sheet->setCellValue('O' . $rowNumber, isset($product['description']) ? strip_tags($product['description']) : '');
                    
                    $sheet->getStyle('J' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle('A' . $rowNumber . ':' . $lastHeaderColumn . $rowNumber)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    $sheet->getStyle('A' . $rowNumber . ':' . $lastHeaderColumn . $rowNumber)->getFont()->setName('Vazirmatn');
                    $rowNumber++;
                }
            }

            foreach (range('A', $lastHeaderColumn) as $columnID) { 
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }
            
            $writer = new Xlsx($spreadsheet);
            $filename = "products_and_variations_export_" . date('Ymd_His') . ".xlsx";
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            if (ob_get_level()) { ob_end_clean(); }
            
            try {
                $writer->save('php://output');
            } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
                error_log("PhpSpreadsheet Writer Exception: " . $e->getMessage());
                if (ob_get_level()) { ob_end_clean(); } 
                flash('report_message', 'خطا در ایجاد فایل اکسل: ' . $e->getMessage(), 'alert alert-danger');
                echo "خطا در ایجاد فایل اکسل. لطفاً لاگ سرور را بررسی کنید. پیام: " . $e->getMessage();
                exit();
            }
            exit(); 
        } else {
            flash('error_message', 'درخواست نامعتبر برای خروجی.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/reports');
            exit();
        }
    }

   

    public function exportOrders() {
        // اطمینان از عدم وجود خروجی قبلی برای جلوگیری از خطای "headers already sent"
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $start_date_input = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
            $end_date_input = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
            $order_status_filter = !empty($_POST['order_status']) ? trim($_POST['order_status']) : null;

            $start_date_gregorian = null;
            if ($start_date_input && function_exists('to_gregorian_date')) {
                $start_date_gregorian = to_gregorian_date($start_date_input);
                if ($start_date_gregorian) $start_date_gregorian .= ' 00:00:00';
            } elseif ($start_date_input) { $start_date_gregorian = $start_date_input . ' 00:00:00';}

            $end_date_gregorian = null;
            if ($end_date_input && function_exists('to_gregorian_date')) {
                $end_date_gregorian = to_gregorian_date($end_date_input);
                if ($end_date_gregorian) $end_date_gregorian .= ' 23:59:59';
            } elseif ($end_date_input) { $end_date_gregorian = $end_date_input . ' 23:59:59';}
            
            $order_items_data = $this->orderModel->getDetailedOrderItemsForExport($start_date_gregorian, $end_date_gregorian, $order_status_filter);

            if (empty($order_items_data)) {
                flash('report_message', 'هیچ سفارشی برای بازه زمانی و وضعیت انتخاب شده یافت نشد.', 'alert alert-info');
                if (ob_get_level()) { ob_end_clean(); } 
                header('Location: ' . BASE_URL . 'admin/reports');
                exit();
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('جزئیات آیتم‌های سفارش');
            $sheet->setRightToLeft(true);

            $headers = [
                'شناسه سفارش', 'تاریخ سفارش (شمسی)', 'نام مشتری', 'ایمیل مشتری', 'تلفن مشتری', 
                'آدرس ارسال', 'شهر', 'کد پستی', 'مبلغ کل سفارش', 'وضعیت سفارش', 'وضعیت پرداخت', 'روش پرداخت',
                'شناسه آیتم', 'نام محصول/تنوع', 'تعداد', 'قیمت واحد (خرید)', 'جمع جزء آیتم',
                'شناسه محصول والد', 'شناسه تنوع', 'SKU تنوع', 'فروشنده', 
                'نرخ کمیسیون (%)', 'مبلغ کمیسیون فروشگاه', 'درآمد فروشنده', 'وضعیت تسویه فروشنده'
            ];
            $sheet->fromArray([$headers], NULL, 'A1');
            $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Vazirmatn'],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4A86E8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
            ]);

            $rowNumber = 2;
            foreach ($order_items_data as $item) {
                $vendor_display_name = 'فروشگاه'; 
                if (isset($item['item_vendor_id']) && $item['item_vendor_id'] !== null) {
                    if (isset($item['vendor_full_name']) && !empty(trim($item['vendor_full_name']))) {
                        $vendor_display_name = $item['vendor_full_name'];
                    } elseif (isset($item['vendor_username']) && !empty($item['vendor_username'])) {
                        $vendor_display_name = $item['vendor_username'];
                    } else {
                        $vendor_display_name = 'فروشنده #' . $item['item_vendor_id'];
                    }
                }

                $sheet->setCellValue('A' . $rowNumber, $item['order_id'] ?? '');
                $sheet->setCellValue('B' . $rowNumber, isset($item['order_date']) ? to_jalali_datetime($item['order_date']) : '');
                $sheet->setCellValue('C' . $rowNumber, (isset($item['customer_first_name']) ? $item['customer_first_name'] : '') . ' ' . (isset($item['customer_last_name']) ? $item['customer_last_name'] : ''));
                $sheet->setCellValue('D' . $rowNumber, $item['customer_email'] ?? '');
                $sheet->setCellValue('E' . $rowNumber, $item['customer_phone'] ?? '');
                $sheet->setCellValue('F' . $rowNumber, $item['shipping_address'] ?? '');
                $sheet->setCellValue('G' . $rowNumber, $item['shipping_city'] ?? '');
                $sheet->setCellValue('H' . $rowNumber, $item['shipping_postal_code'] ?? '');
                $sheet->setCellValue('I' . $rowNumber, isset($item['order_total_amount']) ? (float)$item['order_total_amount'] : 0);
                $sheet->setCellValue('J' . $rowNumber, $item['order_status'] ?? '');
                $sheet->setCellValue('K' . $rowNumber, $item['payment_status'] ?? '');
                $sheet->setCellValue('L' . $rowNumber, $item['payment_method'] ?? '');
                $sheet->setCellValue('M' . $rowNumber, $item['order_item_id'] ?? '');
                $sheet->setCellValue('N' . $rowNumber, $item['item_product_name'] ?? '');
                $sheet->setCellValue('O' . $rowNumber, isset($item['item_quantity']) ? (int)$item['item_quantity'] : 0);
                $sheet->setCellValue('P' . $rowNumber, isset($item['item_price_at_purchase']) ? (float)$item['item_price_at_purchase'] : 0);
                $sheet->setCellValue('Q' . $rowNumber, isset($item['item_sub_total']) ? (float)$item['item_sub_total'] : 0);
                $sheet->setCellValue('R' . $rowNumber, $item['product_id'] ?? '');
                $sheet->setCellValue('S' . $rowNumber, $item['variation_id'] ?? '');
                $sheet->setCellValue('T' . $rowNumber, $item['variation_sku'] ?? '');
                $sheet->setCellValue('U' . $rowNumber, $vendor_display_name);
                $sheet->setCellValue('V' . $rowNumber, (isset($item['platform_commission_rate']) && $item['platform_commission_rate'] !== null) ? ((float)$item['platform_commission_rate'] * 100) . '%' : '-');
                $sheet->setCellValue('W' . $rowNumber, (isset($item['platform_commission_amount']) && $item['platform_commission_amount'] !== null) ? (float)$item['platform_commission_amount'] : '0');
                $sheet->setCellValue('X' . $rowNumber, (isset($item['vendor_earning']) && $item['vendor_earning'] !== null) ? (float)$item['vendor_earning'] : '0');
                $sheet->setCellValue('Y' . $rowNumber, $item['payout_status'] ?? 'unpaid');

                $sheet->getStyle('I' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('P' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('Q' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('W' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('X' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('A' . $rowNumber . ':' . $sheet->getHighestColumn() . $rowNumber)->getFont()->setName('Vazirmatn');
                $sheet->getStyle('A' . $rowNumber . ':' . $sheet->getHighestColumn() . $rowNumber)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $rowNumber++;
            }

            foreach (range('A', $sheet->getHighestColumn()) as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }
            
            $writer = new Xlsx($spreadsheet);
            $filename = "orders_export_" . date('Ymd_His') . ".xlsx";
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            try {
                $writer->save('php://output');
            } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
                error_log("PhpSpreadsheet Writer Exception for Orders: " . $e->getMessage());
                if (ob_get_level()) { ob_end_clean(); }
                flash('report_message', 'خطا در ایجاد فایل اکسل سفارشات: ' . $e->getMessage(), 'alert alert-danger');
                echo "خطا در ایجاد فایل اکسل سفارشات. لطفاً لاگ سرور را بررسی کنید.";
                exit();
            }
            exit();
        } else {
            flash('error_message', 'درخواست نامعتبر برای خروجی.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/reports');
            exit();
        }
    }

 public function exportPlatformCommissions() {
        if (ob_get_level()) { ob_end_clean(); } // Clean output buffer

        if ($_SERVER['REQUEST_METHOD'] == 'POST' || isset($_GET['export'])) { // Allow GET with a flag too for simplicity
            // Use filter_input for POST data
            $start_date_input = isset($_POST['start_date']) ? filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS) : (isset($_GET['start_date']) ? filter_input(INPUT_GET, 'start_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null);
            $end_date_input = isset($_POST['end_date']) ? filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS) : (isset($_GET['end_date']) ? filter_input(INPUT_GET, 'end_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null);

            $start_date_gregorian = null;
            if ($start_date_input && function_exists('to_gregorian_date')) {
                $start_date_gregorian = to_gregorian_date($start_date_input); // Assuming to_gregorian_date returns YYYY-MM-DD
                if ($start_date_gregorian) $start_date_gregorian .= ' 00:00:00';
            } elseif ($start_date_input) { 
                // Basic validation if date is already Gregorian (e.g., YYYY-MM-DD)
                if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $start_date_input)) {
                    $start_date_gregorian = $start_date_input . ' 00:00:00';
                }
            }

            $end_date_gregorian = null;
            if ($end_date_input && function_exists('to_gregorian_date')) {
                $end_date_gregorian = to_gregorian_date($end_date_input);
                if ($end_date_gregorian) $end_date_gregorian .= ' 23:59:59';
            } elseif ($end_date_input) { 
                if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $end_date_input)) {
                    $end_date_gregorian = $end_date_input . ' 23:59:59';
                }
            }
            
            // Ensure OrderModel has getOrdersWithPlatformCommission method
            if (!method_exists($this->orderModel, 'getOrdersWithPlatformCommission')) {
                error_log("AdminController::exportPlatformCommissions - Method getOrdersWithPlatformCommission does not exist in OrderModel.");
                flash('report_message', 'خطای سیستمی: تابع گزارش کمیسیون فروشگاه در دسترس نیست.', 'alert alert-danger');
                header('Location: ' . BASE_URL . 'admin/reports');
                exit();
            }
            $orders_data = $this->orderModel->getOrdersWithPlatformCommission($start_date_gregorian, $end_date_gregorian);

            if (empty($orders_data)) {
                flash('report_message', 'هیچ کمیسیونی برای بازه زمانی انتخاب شده یافت نشد.', 'alert alert-info');
                header('Location: ' . BASE_URL . 'admin/reports');
                exit();
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('گزارش کمیسیون فروشگاه');
            $sheet->setRightToLeft(true);

            $headers = [
                'شناسه سفارش', 'تاریخ سفارش (شمسی)', 'مشتری', 'مبلغ کل سفارش (تومان)', 'کمیسیون فروشگاه (تومان)'
            ];
            $sheet->fromArray([$headers], NULL, 'A1');

            // Style header row
            $headerStyleArray = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4285F4']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
            ];
            $sheet->getStyle('A1:E1')->applyFromArray($headerStyleArray);


            $rowNumber = 2;
            $total_commission_for_period = 0;
            foreach ($orders_data as $order) {
                $commission_amount = isset($order['total_order_platform_commission']) ? (float)$order['total_order_platform_commission'] : 0;
                $total_commission_for_period += $commission_amount;

                $sheet->setCellValue('A' . $rowNumber, isset($order['order_id']) ? $order['order_id'] : 'N/A'); // Use order_id from alias
                // Use order_date which is the alias for o.created_at
                $sheet->setCellValue('B' . $rowNumber, isset($order['order_date']) && function_exists('to_jalali_datetime') ? to_jalali_datetime($order['order_date']) : ($order['order_date'] ?? 'N/A'));
                
                // Customer name already fetched in the main query
                $customer_display_name = trim($order['customer_full_name'] ?? '');
                if (empty($customer_display_name)) {
                    $customer_display_name = $order['customer_username'] ?? 'نامشخص';
                }
                $sheet->setCellValue('C' . $rowNumber, $customer_display_name);
                
                $sheet->setCellValue('D' . $rowNumber, (float)($order['order_total'] ?? 0)); // Use order_total from alias
                $sheet->setCellValue('E' . $rowNumber, $commission_amount);
                
                $sheet->getStyle('D' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('E' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');
                
                // Apply border to data cells
                $sheet->getStyle('A'.$rowNumber.':E'.$rowNumber)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                $rowNumber++;
            }
            
            // Summary Row
            $sheet->mergeCells('A'.$rowNumber.':C'.$rowNumber);
            $sheet->setCellValue('A' . $rowNumber, 'مجموع کمیسیون این دوره');
            $sheet->getStyle('A'.$rowNumber)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue('D' . $rowNumber, ''); // Empty cell or sum of total_amounts if needed
            $sheet->setCellValue('E' . $rowNumber, $total_commission_for_period);
            
            $summaryStyleArray = [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FCE5CD']], // Light orange
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
            ];
            $sheet->getStyle('A'.$rowNumber.':E'.$rowNumber)->applyFromArray($summaryStyleArray);
            $sheet->getStyle('E'.$rowNumber)->getNumberFormat()->setFormatCode('#,##0 "تومان"');


            foreach (range('A', 'E') as $columnID) { $sheet->getColumnDimension($columnID)->setAutoSize(true); }
            
            $filename = "گزارش_کمیسیون_فروشگاه_" . date('Y-m-d_H-i-s') . ".xlsx";
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . rawurlencode($filename) . '"'); // Use rawurlencode for filename
            header('Cache-Control: max-age=0');
            
            // Clear any previously sent headers or output
            if (ob_get_contents()) ob_end_clean();

            $writer = new Xlsx($spreadsheet);
            try {
                $writer->save('php://output');
            } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
                error_log("Error saving Excel file: " . $e->getMessage());
                flash('report_message', 'خطا در ایجاد فایل اکسل: ' . $e->getMessage(), 'alert alert-danger');
                // Cannot redirect here as headers are already sent for file download
            }
            exit();
        } else {
            // If not POST, redirect to the reports page or show an error
            flash('report_message', 'برای دریافت گزارش، لطفاً بازه زمانی را انتخاب و ارسال کنید.', 'alert alert-info');
            header('Location: ' . BASE_URL . 'admin/reports');
            exit();
        }
    }

    // ... (سایر متدهای AdminController مانند افزودن، ویرایش و حذف محصول که قبلاً بررسی شد) ...

    public function exportVendorPayouts() {
        if (ob_get_level()) { ob_end_clean(); }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $start_date_input = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
            $end_date_input = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
            $payout_status_filter = !empty($_POST['payout_status']) ? trim($_POST['payout_status']) : null;

            $start_date_gregorian = null;
            if ($start_date_input && function_exists('to_gregorian_date')) {
                $start_date_gregorian = to_gregorian_date($start_date_input);
                if ($start_date_gregorian) $start_date_gregorian .= ' 00:00:00';
            } elseif ($start_date_input) { $start_date_gregorian = $start_date_input . ' 00:00:00';}

            $end_date_gregorian = null;
            if ($end_date_input && function_exists('to_gregorian_date')) {
                $end_date_gregorian = to_gregorian_date($end_date_input);
                if ($end_date_gregorian) $end_date_gregorian .= ' 23:59:59';
            } elseif ($end_date_input) { $end_date_gregorian = $end_date_input . ' 23:59:59';}

            $payouts_data = $this->orderModel->getAllPayoutRequests($start_date_gregorian, $end_date_gregorian, $payout_status_filter);

            if (empty($payouts_data)) {
                flash('report_message', 'هیچ درخواست پرداختی برای بازه زمانی و وضعیت انتخاب شده یافت نشد.', 'alert alert-info');
                header('Location: ' . BASE_URL . 'admin/reports');
                exit();
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('درخواست‌های پرداخت فروشندگان');
            $sheet->setRightToLeft(true);

            $headers = [
                'شناسه درخواست', 'شناسه فروشنده', 'نام فروشنده', 'ایمیل فروشنده', 
                'مبلغ درخواستی (تومان)', 'مبلغ پرداخت شده (تومان)', 'وضعیت', 
                'تاریخ درخواست (شمسی)', 'تاریخ پردازش (شمسی)', 'روش پرداخت', 
                'جزئیات پرداخت (فروشنده)', 'یادداشت ادمین', 'پردازش توسط ادمین (ID)'
            ];
            $sheet->fromArray([$headers], NULL, 'A1');
            // ... (استایل‌دهی به هدرها)

            $rowNumber = 2;
            foreach ($payouts_data as $payout) {
                $sheet->setCellValue('A' . $rowNumber, $payout['id']);
                $sheet->setCellValue('B' . $rowNumber, $payout['vendor_id']);
                $sheet->setCellValue('C' . $rowNumber, $payout['vendor_full_name'] ?: $payout['vendor_username']);
                $sheet->setCellValue('D' . $rowNumber, $payout['vendor_email'] ?? '');
                $sheet->setCellValue('E' . $rowNumber, (float)($payout['requested_amount'] ?? 0));
                $sheet->setCellValue('F' . $rowNumber, ($payout['payout_amount'] !== null) ? (float)$payout['payout_amount'] : '');
                $sheet->setCellValue('G' . $rowNumber, $payout['status']);
                $sheet->setCellValue('H' . $rowNumber, isset($payout['requested_at']) ? to_jalali_datetime($payout['requested_at']) : '');
                $sheet->setCellValue('I' . $rowNumber, isset($payout['processed_at']) && $payout['processed_at'] ? to_jalali_datetime($payout['processed_at']) : '-');
                $sheet->setCellValue('J' . $rowNumber, $payout['payout_method'] ?? '');
                $sheet->setCellValue('K' . $rowNumber, $payout['payment_details'] ?? '');
                $sheet->setCellValue('L' . $rowNumber, $payout['notes'] ?? '');
                $sheet->setCellValue('M' . $rowNumber, $payout['processed_by_admin_id'] ?? '');
                
                $sheet->getStyle('E' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('F' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0');
                // ... (استایل‌دهی به ردیف)
                $rowNumber++;
            }
            foreach (range('A', 'M') as $columnID) { $sheet->getColumnDimension($columnID)->setAutoSize(true); }
            
            $writer = new Xlsx($spreadsheet);
            $filename = "vendor_payout_requests_export_" . date('Ymd_His') . ".xlsx";
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            if (ob_get_level()) { ob_end_clean(); }
            try { $writer->save('php://output'); } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) { /* ... */ }
            exit();
        } else { /* ... */ }
    }
     

       /**
        * Generate HTML for printing a shipping label for a specific order.
        * URL: admin/printShippingLabel/ORDER_ID
        */
       public function printShippingLabel($order_id = null) {
           if (is_null($order_id) || !is_numeric($order_id)) {
               flash('error_message', 'شناسه سفارش برای چاپ لیبل پستی نامعتبر است.', 'alert alert-danger');
               header('Location: ' . BASE_URL . 'admin/orders');
               exit();
           }
           $order_id = (int)$order_id;
           $order = $this->orderModel->getOrderDetailsById($order_id);

           if (!$order) {
               flash('error_message', 'سفارش مورد نظر برای چاپ لیبل پستی یافت نشد.', 'alert alert-danger');
               header('Location: ' . BASE_URL . 'admin/orders');
               exit();
           }
            $store_info = [ /* ... (مشابه بالا) ... */ ];
           $data = [
               'pageTitle' => 'لیبل پستی سفارش #' . htmlspecialchars($order['id']),
               'order' => $order,
               'store_info' => $store_info,
               'layout' => 'print'
           ];
           $this->view('admin/orders/prints/shipping_label', $data);
       }

   
    // --- Print Documents ---
    public function printInvoice($order_id = null) {
        if (is_null($order_id) || !is_numeric($order_id)) {
            flash('error_message', 'شناسه سفارش برای چاپ فاکتور نامعتبر است.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/orders');
            exit();
        }
        $order_id = (int)$order_id;
        $order = $this->orderModel->getOrderDetailsById($order_id); 

        if (!$order) {
            flash('error_message', 'سفارش مورد نظر برای چاپ فاکتور یافت نشد.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/orders');
            exit();
        }

        $store_info = [
            'name' => defined('SITE_NAME') ? SITE_NAME : 'فروشگاه شما',
            'address' => 'آدرس فروشگاه شما، شهر، استان',
            'phone' => 'تلفن فروشگاه',
            'email' => 'ایمیل فروشگاه',
            'logo_url' => BASE_URL . 'images/logo.png' 
        ];

        $data = [
            'pageTitle' => 'فاکتور سفارش #' . htmlspecialchars($order['id']),
            'order' => $order,
            'store_info' => $store_info,
            'layout' => 'print' 
        ];
        $this->view('admin/orders/prints/invoice', $data);
    }

    public function printWarehouseReceipt($order_id = null) {
        if (is_null($order_id) || !is_numeric($order_id)) {
            flash('error_message', 'شناسه سفارش برای چاپ رسید انبار نامعتبر است.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/orders');
            exit();
        }
        $order_id = (int)$order_id;
        $order = $this->orderModel->getOrderDetailsById($order_id);

        if (!$order) {
            flash('error_message', 'سفارش مورد نظر برای چاپ رسید انبار یافت نشد.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/orders');
            exit();
        }
         $store_info = [
            'name' => defined('SITE_NAME') ? SITE_NAME : 'فروشگاه شما',
            // سایر اطلاعات فروشگاه اگر لازم است
        ];
        $data = [
            'pageTitle' => 'رسید انبار سفارش #' . htmlspecialchars($order['id']),
            'order' => $order,
            'store_info' => $store_info,
            'layout' => 'print' 
        ];
        $this->view('admin/orders/prints/warehouse_receipt', $data);
    }

   
    public function printBatchWarehouseReceipts() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['order_ids']) && is_array($_POST['order_ids']) && !empty($_POST['order_ids'])) {
                $selected_order_ids = array_map('intval', $_POST['order_ids']);
                
                $orders_to_print = [];
                $count = 0; 
                foreach ($selected_order_ids as $order_id) {
                    if ($count >= 8) break; // محدودیت ۸ رسید در هر صفحه A4
                    $order = $this->orderModel->getOrderDetailsById($order_id);
                    if ($order) {
                        $orders_to_print[] = $order;
                        $count++;
                    }
                }

                if (empty($orders_to_print)) {
                    flash('error_message', 'هیچ سفارش معتبری برای چاپ گروهی انتخاب نشده یا یافت نشد.', 'alert alert-warning');
                    header('Location: ' . BASE_URL . 'admin/orders');
                    exit();
                }

                $store_info = [
                    'name' => defined('SITE_NAME') ? SITE_NAME : 'فروشگاه شما',
                ];

                $data = [
                    'pageTitle' => 'چاپ گروهی رسیدهای انبار',
                    'orders' => $orders_to_print,
                    'store_info' => $store_info,
                    'layout' => 'print' 
                ];
                $this->view('admin/orders/prints/batch_warehouse_receipt', $data);

            } else {
                flash('error_message', 'هیچ سفارشی برای چاپ گروهی انتخاب نشده است.', 'alert alert-warning');
                header('Location: ' . BASE_URL . 'admin/orders');
                exit();
            }
        } else {
            header('Location: ' . BASE_URL . 'admin/orders');
            exit();
        }
    }
    
    public function printBatchShippingLabels() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['order_ids']) && is_array($_POST['order_ids']) && !empty($_POST['order_ids'])) {
                $selected_order_ids = array_map('intval', $_POST['order_ids']);
                
                $orders_to_print = [];
                $count = 0; 
                foreach ($selected_order_ids as $order_id) {
                    // برای چاپ گروهی لیبل، ممکن است بخواهید تعداد بیشتری را در یک فراخوانی مدیریت کنید
                    // و ویو batch_shipping_label مسئول چیدمان آنها در صفحات مختلف باشد.
                    // فعلاً همان محدودیت ۸ تایی را برای سادگی در نظر می‌گیریم.
                    if ($count >= 8) break; 

                    $order = $this->orderModel->getOrderDetailsById($order_id);
                    if ($order) {
                        $orders_to_print[] = $order;
                        $count++;
                    }
                }

                if (empty($orders_to_print)) {
                    flash('error_message', 'هیچ سفارش معتبری برای چاپ گروهی لیبل انتخاب نشده یا یافت نشد.', 'alert alert-warning');
                    header('Location: ' . BASE_URL . 'admin/orders');
                    exit();
                }

                $store_info = [
                    'name' => defined('SITE_NAME') ? SITE_NAME : 'فروشگاه شما',
                    'address' => 'خیابان اصلی، کوچه فرعی، پلاک ۱، شهر شما',
                    'phone' => '۰۲۱-۱۲۳۴۵۶۷۸',
                    'logo_url' => BASE_URL . 'images/logo_for_print.png' 
                ];

                $data = [
                    'pageTitle' => 'چاپ گروهی لیبل‌های پستی',
                    'orders' => $orders_to_print,
                    'store_info' => $store_info,
                    'layout' => 'print' 
                ];
                $this->view('admin/orders/prints/batch_shipping_label', $data);

            } else {
                flash('error_message', 'هیچ سفارشی برای چاپ گروهی لیبل انتخاب نشده است.', 'alert alert-warning');
                header('Location: ' . BASE_URL . 'admin/orders');
                exit();
            }
        } else {
            header('Location: ' . BASE_URL . 'admin/orders');
            exit();
        }
    }
    // --- Affiliate Commission Management ---
    /**
     * Display list of all affiliate commissions for admin.
     * URL: admin/affiliateCommissions
     */
    public function affiliateCommissions() {
        // نیاز به متدی در OrderModel یا AffiliateModel برای دریافت تمام کمیسیون‌ها
        // $all_commissions = $this->orderModel->getAllAffiliateCommissionsFiltered(); 
        // فعلاً یک آرایه خالی می‌گذاریم تا بعداً تکمیل شود
        $all_commissions = $this->orderModel->getAllAffiliateCommissionsWithDetails(); // فرض وجود این متد

        $data = [
            'pageTitle' => 'مدیریت کمیسیون‌های همکاری در فروش',
            'commissions' => $all_commissions
        ];
        $this->view('admin/affiliates/commissions_index', $data);
    }

    /**
     * Update the status of an affiliate commission.
     * URL: admin/updateAffiliateCommissionStatus/COMMISSION_ID (POST)
     */
   public function updateAffiliateCommissionStatus($commission_id = null) {
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && $commission_id && is_numeric($commission_id)) {
                $commission_id = (int)$commission_id;
                $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $new_status = isset($_POST['commission_status']) ? trim($_POST['commission_status']) : null;
                $allowed_statuses = ['pending', 'approved', 'rejected', 'paid', 'cancelled']; // 'paid' توسط processAffiliatePayout تنظیم می‌شود

                if ($new_status && in_array($new_status, $allowed_statuses)) {
                    $commission_details = $this->orderModel->getAffiliateCommissionById($commission_id);

                    if ($commission_details) {
                        $old_status = $commission_details['status'];
                        $affiliate_id = $commission_details['affiliate_id'];
                        $commission_amount = (float)$commission_details['commission_earned'];

                        // فقط اگر وضعیت واقعاً تغییر کرده است
                        if ($old_status !== $new_status) {
                            if ($this->orderModel->updateAffiliateCommissionStatus($commission_id, $new_status)) {
                                $balance_updated = false;
                                // اگر وضعیت جدید 'approved' است و وضعیت قبلی 'approved' نبوده
                                if ($new_status === 'approved' && $old_status !== 'approved') {
                                    if ($this->userModel->updateAffiliateBalance($affiliate_id, $commission_amount)) {
                                        flash('commission_status_success', 'وضعیت کمیسیون به "تایید شده" تغییر یافت و مبلغ به کیف پول همکار اضافه شد.');
                                        $balance_updated = true;
                                    } else {
                                        flash('commission_status_warning', 'وضعیت کمیسیون به‌روز شد اما در افزودن مبلغ به کیف پول همکار خطایی رخ داد.', 'alert alert-warning');
                                    }
                                } 
                                // اگر وضعیت قبلی 'approved' بوده و وضعیت جدید چیزی غیر از 'approved' یا 'paid' است (یعنی رد شده یا به حالت انتظار برگشته)
                                elseif ($old_status === 'approved' && !in_array($new_status, ['approved', 'paid'])) {
                                    if ($this->userModel->updateAffiliateBalance($affiliate_id, -$commission_amount)) {
                                        flash('commission_status_success', 'وضعیت کمیسیون تغییر یافت و مبلغ از کیف پول همکار کسر شد.');
                                        $balance_updated = true;
                                    } else {
                                        flash('commission_status_warning', 'وضعیت کمیسیون به‌روز شد اما در کسر مبلغ از کیف پول همکار خطایی رخ داد.', 'alert alert-warning');
                                    }
                                }
                                
                                if (!$balance_updated && $old_status !== $new_status) { // اگر وضعیت تغییر کرده ولی موجودی نه
                                     flash('commission_status_success', 'وضعیت کمیسیون با موفقیت به‌روز شد.');
                                } elseif ($old_status === $new_status) {
                                     flash('commission_status_info', 'وضعیت کمیسیون تغییری نکرده است.', 'alert alert-info');
                                }

                            } else {
                                flash('commission_status_fail', 'خطا در به‌روزرسانی وضعیت کمیسیون در پایگاه داده.', 'alert alert-danger');
                            }
                        } else {
                             flash('commission_status_info', 'وضعیت کمیسیون تغییری نکرده است.', 'alert alert-info');
                        }
                    } else {
                        flash('commission_status_fail', 'کمیسیون مورد نظر یافت نشد.', 'alert alert-danger');
                    }
                } else {
                    flash('commission_status_fail', 'وضعیت ارسالی نامعتبر است.', 'alert alert-danger');
                }
                header('Location: ' . BASE_URL . 'admin/affiliateCommissions');
                exit();
            } else {
                header('Location: ' . BASE_URL . 'admin/affiliateCommissions');
                exit();
            }
        }
    // --- Affiliate Payout Management by Admin ---

    /**
     * Display list of all affiliate payout requests.
     * URL: admin/affiliatePayoutRequests
     */
    public function affiliatePayoutRequests() {
        // دریافت فیلترها از GET request (اختیاری)
        $start_date_input = !empty($_GET['start_date']) ? $_GET['start_date'] : null;
        $end_date_input = !empty($_GET['end_date']) ? $_GET['end_date'] : null;
        $status_filter = !empty($_GET['status']) ? trim($_GET['status']) : null;

        $start_date_gregorian = null;
        if ($start_date_input && function_exists('to_gregorian_date')) {
            $start_date_gregorian = to_gregorian_date($start_date_input);
            if ($start_date_gregorian) $start_date_gregorian .= ' 00:00:00';
        } elseif ($start_date_input) { $start_date_gregorian = $start_date_input . ' 00:00:00';}

        $end_date_gregorian = null;
        if ($end_date_input && function_exists('to_gregorian_date')) {
            $end_date_gregorian = to_gregorian_date($end_date_input);
            if ($end_date_gregorian) $end_date_gregorian .= ' 23:59:59';
        } elseif ($end_date_input) { $end_date_gregorian = $end_date_input . ' 23:59:59';}
        
        $payouts = $this->orderModel->getAllAffiliatePayoutRequests($start_date_gregorian, $end_date_gregorian, $status_filter);
        
        $data = [
            'pageTitle' => 'درخواست‌های تسویه حساب همکاران',
            'payouts' => $payouts,
            'filter_start_date' => $start_date_input, // برای حفظ مقادیر فیلتر در فرم
            'filter_end_date' => $end_date_input,
            'filter_status' => $status_filter
        ];
        $this->view('admin/affiliates/payouts_index', $data);
    }

    /**
     * Display form to process a specific affiliate payout request and handle form submission.
     * URL: admin/processAffiliatePayout/PAYOUT_ID
     */
    public function processAffiliatePayout($payout_id = null) {
        if (is_null($payout_id) || !is_numeric($payout_id)) {
            flash('error_message', 'شناسه درخواست تسویه همکار نامعتبر است.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/affiliatePayoutRequests');
            exit();
        }
        $payout_id = (int)$payout_id;
        $payoutRequest = $this->orderModel->getAffiliatePayoutRequestById($payout_id);

        if (!$payoutRequest) {
            flash('error_message', 'درخواست تسویه همکار مورد نظر یافت نشد.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'admin/affiliatePayoutRequests');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            $new_status = isset($_POST['payout_status']) ? trim($_POST['payout_status']) : $payoutRequest['status'];
            $payout_amount_paid = isset($_POST['payout_amount_paid']) && $_POST['payout_amount_paid'] !== '' ? (float)trim($_POST['payout_amount_paid']) : null;
            $admin_notes = isset($_POST['admin_notes']) ? trim($_POST['admin_notes']) : null;
            $payment_details_admin_input = isset($_POST['payment_details_admin']) ? trim($_POST['payment_details_admin']) : ($payoutRequest['payment_details'] ?? null); // استفاده از جزئیات قبلی اگر وارد نشده
            $admin_user_id_processing = $_SESSION['user_id']; 

            $allowed_statuses = ['requested', 'processing', 'completed', 'rejected', 'cancelled'];

            if (!in_array($new_status, $allowed_statuses)) {
                flash('error_message', 'وضعیت انتخاب شده نامعتبر است.', 'alert alert-danger');
            } elseif ($new_status === 'completed' && ($payout_amount_paid === null || $payout_amount_paid <= 0)) {
                flash('error_message', 'برای وضعیت "تکمیل شده"، مبلغ پرداخت شده باید وارد شود و بیشتر از صفر باشد.', 'alert alert-danger');
            } elseif ($new_status === 'completed' && $payout_amount_paid > (float)$payoutRequest['requested_amount']) {
                 flash('error_message', 'مبلغ پرداخت شده نمی‌تواند بیشتر از مبلغ درخواستی باشد.', 'alert alert-danger');
            }
            else {
                if ($new_status === 'completed' && $payout_amount_paid === null) {
                     $payout_amount_paid = (float)$payoutRequest['requested_amount'];
                }

                if ($this->orderModel->processAffiliatePayout(
                    $payout_id, 
                    $new_status, 
                    $admin_user_id_processing, 
                    $payout_amount_paid,       
                    $admin_notes,              
                    $payment_details_admin_input 
                )) {
                    flash('payout_processed_success', 'درخواست تسویه همکار با موفقیت پردازش شد.');
                } else {
                    flash('payout_processed_fail', 'خطا در پردازش درخواست تسویه همکار.', 'alert alert-danger');
                }
            }
            header('Location: ' . BASE_URL . 'admin/processAffiliatePayout/' . $payout_id); 
            exit();

        } else { // GET request
            $affiliate_commissions_for_payout = $this->orderModel->getAffiliateCommissionsByPayoutId($payout_id);

            $data = [
                'pageTitle' => 'پردازش درخواست تسویه همکار #' . htmlspecialchars($payoutRequest['id']) . ' برای ' . htmlspecialchars($payoutRequest['affiliate_full_name'] ?? $payoutRequest['affiliate_username']),
                'payoutRequest' => $payoutRequest,
                'affiliateCommissions' => $affiliate_commissions_for_payout ?: []
            ];
            $this->view('admin/affiliates/payout_process', $data);
        }
    }
}
?>
