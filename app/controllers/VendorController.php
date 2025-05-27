<?php
// app/controllers/VendorController.php

class VendorController extends Controller {
    private $userModel;
    private $productModel;
    private $categoryModel;
    private $attributeModel; // ProductAttribute model
    private $orderModel;     // Order model
    private $uploadDir = 'uploads/products/'; 
    private $variationUploadDir = 'uploads/variations/'; 

    public function __construct() {
        $this->userModel = $this->model('User');
        $this->productModel = $this->model('Product');
        $this->categoryModel = $this->model('Category');
        $this->attributeModel = $this->model('ProductAttribute');
        $this->orderModel = $this->model('Order'); 

        if (session_status() == PHP_SESSION_NONE) { 
            session_start(); 
        }

        // Check if user is logged in and is a vendor
        if (!isset($_SESSION['user_id'])) {
            flash('auth_required', 'برای دسترسی به پنل فروشندگی، لطفاً ابتدا وارد شوید.', 'alert alert-danger');
            $_SESSION['redirect_after_login'] = BASE_URL . 'vendor/dashboard'; // Default redirect for vendor
            header('Location: ' . BASE_URL . 'auth/login');
            exit();
        }
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'vendor') {
            flash('access_denied', 'شما اجازه دسترسی به پنل فروشندگی را ندارید.', 'alert alert-danger');
            $role = $_SESSION['user_role'];
            switch ($role) {
                case 'customer': header('Location: ' . BASE_URL . 'customer/orders'); break;
                case 'admin': header('Location: ' . BASE_URL . 'admin/dashboard'); break;
                case 'affiliate': header('Location: ' . BASE_URL . 'affiliate/dashboard'); break; // Assuming these routes exist
                default: header('Location: ' . BASE_URL); break;
            }
            exit();
        }
    }

    public function dashboard() {
        $vendor_id = $_SESSION['user_id'];
        $vendor = $this->userModel->findUserById($vendor_id);
        $withdrawable_balance = $this->orderModel->getVendorWithdrawableBalance($vendor_id);
        $unpaid_items = $this->orderModel->getUnpaidOrderItemsForVendor($vendor_id);

        $data = [
            'pageTitle' => 'داشبورد فروشنده',
            'vendor_name' => $vendor ? ($vendor['first_name'] . ' ' . $vendor['last_name']) : ($_SESSION['username'] ?? 'فروشنده'),
            'withdrawable_balance' => $withdrawable_balance,
            'unpaid_items' => $unpaid_items
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
                } else { // Simple product
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
            'pageTitle' => 'محصولات من',
            'products' => $products_to_view
        ];
        $this->view('vendor/products/index', $data);
    }

    public function addProduct() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $stock_quantity_input = trim($_POST['stock_quantity']);
            $data = [
                'name' => trim($_POST['name']),
                'description' => trim($_POST['description']),
                'price' => trim($_POST['price']),
                'stock_quantity' => $stock_quantity_input,
                'initial_stock_quantity' => $stock_quantity_input,
                'category_id' => isset($_POST['category_id']) ? trim($_POST['category_id']) : '',
                'product_type' => isset($_POST['product_type']) ? trim($_POST['product_type']) : 'simple',
                'vendor_id' => $_SESSION['user_id'], 
                'affiliate_commission_type' => 'none', 
                'affiliate_commission_value' => null,
                'image_url' => '',
                'name_err' => '', 'price_err' => '', 'stock_quantity_err' => '', 'image_err' => '', 'product_type_err' => ''
            ];

            if (!in_array($data['product_type'], ['simple', 'variable'])) { $data['product_type_err'] = 'نوع محصول نامعتبر است.'; }
            if (empty($data['name'])) { $data['name_err'] = 'لطفاً نام محصول را وارد کنید.'; }
            if ($data['product_type'] == 'simple') {
                if (empty($data['price'])) { $data['price_err'] = 'لطفاً قیمت محصول ساده را وارد کنید.'; }
                elseif (!is_numeric($data['price']) || (float)$data['price'] < 0) { $data['price_err'] = 'قیمت وارد شده معتبر نیست.'; }
                if ($data['stock_quantity'] === '' || !is_numeric($data['stock_quantity']) || (int)$data['stock_quantity'] < 0) {
                     $data['stock_quantity_err'] = 'تعداد موجودی محصول ساده معتبر نیست.';
                } else { $data['initial_stock_quantity'] = (int)$data['stock_quantity']; }
            } else { 
                if (!empty($data['price']) && (!is_numeric($data['price']) || (float)$data['price'] < 0)) { $data['price_err'] = 'قیمت والد متغیر نامعتبر.';}
                if (!empty($data['stock_quantity']) && (!is_numeric($data['stock_quantity']) || (int)$data['stock_quantity'] < 0)) { $data['stock_quantity_err'] = 'موجودی والد متغیر نامعتبر.';}
                if (empty($data['price'])) $data['price'] = null; 
                if ($data['stock_quantity'] === '' || !is_numeric($data['stock_quantity'])) { 
                    $data['stock_quantity'] = 0; $data['initial_stock_quantity'] = 0;
                } else { $data['initial_stock_quantity'] = (int)$data['stock_quantity'];}
                $data['stock_quantity_explicit'] = true; 
            }

            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == UPLOAD_ERR_OK) {
                if (!defined('FCPATH')) { $data['image_err'] = 'خطای پیکربندی: FCPATH.'; }
                if (empty($data['image_err'])) { 
                    $target_dir_absolute = FCPATH . $this->uploadDir;
                    if (!is_dir($target_dir_absolute)) { if (!mkdir($target_dir_absolute, 0775, true)) { $data['image_err'] = 'خطا در ایجاد پوشه آپلود.';}}
                    if (empty($data['image_err'])) { 
                        $file_info = pathinfo($_FILES["product_image"]["name"]);
                        $file_type = strtolower($file_info['extension']);
                        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];  $max_file_size = 2 * 1024 * 1024;
                        if (!in_array($file_type, $allowed_types)) { $data['image_err'] = 'فرمت‌های JPG, JPEG, PNG, GIF مجاز.';}
                        elseif ($_FILES["product_image"]["size"] > $max_file_size) { $data['image_err'] = 'حجم فایل > ۲مگابایت.';}
                        else {
                            $new_file_name = uniqid('product_'. $_SESSION['user_id'] . '_', true) . '.' . $file_type;
                            if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_dir_absolute . $new_file_name)) {
                                $data['image_url'] = $this->uploadDir . $new_file_name;
                            } else { $data['image_err'] = 'خطا در آپلود فایل.'; }
                        }
                    }
                }
            } elseif (isset($_FILES['product_image']) && $_FILES['product_image']['error'] != UPLOAD_ERR_NO_FILE) {
                $data['image_err'] = 'خطا در آپلود. کد: ' . $_FILES['product_image']['error'];
            }

            if (empty($data['name_err']) && empty($data['price_err']) && empty($data['stock_quantity_err']) && empty($data['image_err']) && empty($data['product_type_err'])) {
                $product_id = $this->productModel->addProduct($data);
                if ($product_id) {
                    if ($data['product_type'] === 'variable' && isset($_POST['configurable_attributes']) && is_array($_POST['configurable_attributes'])) {
                        $this->attributeModel->setConfigurableAttributesForProduct($product_id, $_POST['configurable_attributes']);
                    }
                    flash('product_added_success', 'محصول جدید شما با موفقیت اضافه شد.');
                    header('Location: ' . BASE_URL . 'vendor/myProducts');
                    exit();
                } else { 
                    flash('product_action_fail', 'خطا در افزودن محصول.', 'alert alert-danger');
                    if (!empty($data['image_url']) && defined('FCPATH') && file_exists(FCPATH . $data['image_url'])) {
                        unlink(FCPATH . $data['image_url']);
                    }
                }
            }
            $data['pageTitle'] = 'افزودن محصول جدید';
            $data['categories'] = $this->categoryModel->getAllCategories();
            $data['all_attributes'] = $this->attributeModel->getAllAttributes();
            if(!empty($data['image_err'])) flash('file_upload_error', $data['image_err'], 'alert alert-danger');
            if(!empty($data['name_err']) || !empty($data['price_err']) || !empty($data['stock_quantity_err']) || !empty($data['product_type_err'])) {
                 flash('product_form_error', 'لطفاً خطاهای فرم را برطرف کنید.', 'alert alert-danger');
            }
            $this->view('vendor/products/add', $data);
            exit();
        } else { 
            $data = [
                'pageTitle' => 'افزودن محصول جدید', 'name' => '', 'description' => '', 'price' => '',
                'image_url' => '', 'stock_quantity' => '0', 'initial_stock_quantity' => '0', 'category_id' => '', 'product_type' => 'simple',
                'categories' => $this->categoryModel->getAllCategories(),
                'all_attributes' => $this->attributeModel->getAllAttributes(),
                'configurable_attributes_for_product' => [],
                'name_err' => '', 'price_err' => '', 'stock_quantity_err' => '', 'image_err' => '', 'product_type_err' => ''
            ];
            $this->view('vendor/products/add', $data);
        }
    }

    public function editProduct($id = null) {
        if (is_null($id) || !is_numeric($id)) { 
            flash('error_message', 'شناسه محصول نامعتبر.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'vendor/myProducts'); exit();
        }
        $id = (int)$id;
        $product = $this->productModel->getProductById($id);

        if (!$product || $product['vendor_id'] != $_SESSION['user_id']) {
            flash('access_denied', 'شما اجازه ویرایش این محصول را ندارید.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'vendor/myProducts');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $current_image_url = isset($_POST['current_image_url']) ? trim($_POST['current_image_url']) : ($product['image_url'] ?? '');
            $data = [
                'id' => $id, 
                'name' => trim($_POST['name']), 
                'description' => trim($_POST['description']),
                'price' => trim($_POST['price']), 
                'stock_quantity' => trim($_POST['stock_quantity']),
                'initial_stock_quantity' => isset($_POST['initial_stock_quantity_edit']) ? (int)trim($_POST['initial_stock_quantity_edit']) : (isset($product['initial_stock_quantity']) ? $product['initial_stock_quantity'] : 0),
                'category_id' => isset($_POST['category_id']) ? trim($_POST['category_id']) : '',
                'product_type' => isset($_POST['product_type']) ? trim($_POST['product_type']) : 'simple',
                'vendor_id' => $_SESSION['user_id'], 
                'affiliate_commission_type' => $product['affiliate_commission_type'], 
                'affiliate_commission_value' => $product['affiliate_commission_value'],
                'image_url' => $current_image_url,
                'name_err' => '', 'price_err' => '', 'stock_quantity_err' => '', 'image_err' => '', 'product_type_err' => ''
                // 'initial_stock_quantity_err' => '' 
            ];
            
            if (isset($_POST['initial_stock_quantity_edit']) && (!is_numeric($data['initial_stock_quantity']) || (int)$data['initial_stock_quantity'] < 0) ) {
                // $data['initial_stock_quantity_err'] = 'موجودی اولیه نامعتبر است.'; 
            }

            if (!in_array($data['product_type'], ['simple', 'variable'])) { $data['product_type_err'] = 'نوع محصول نامعتبر است.'; }
            if (empty($data['name'])) { $data['name_err'] = 'لطفاً نام محصول را وارد کنید.'; }
            if ($data['product_type'] == 'simple') {
                if (empty($data['price'])) { $data['price_err'] = 'لطفاً قیمت محصول ساده را وارد کنید.'; }
                elseif (!is_numeric($data['price']) || (float)$data['price'] < 0) { $data['price_err'] = 'قیمت وارد شده معتبر نیست.'; }
                if ($data['stock_quantity'] === '' || !is_numeric($data['stock_quantity']) || (int)$data['stock_quantity'] < 0) {
                    $data['stock_quantity_err'] = 'تعداد موجودی محصول ساده معتبر نیست.';
                }
            } else { 
                if (!empty($data['price']) && (!is_numeric($data['price']) || (float)$data['price'] < 0)) { $data['price_err'] = 'قیمت والد متغیر نامعتبر.';}
                if (!empty($data['stock_quantity']) && (!is_numeric($data['stock_quantity']) || (int)$data['stock_quantity'] < 0)) { $data['stock_quantity_err'] = 'موجودی والد متغیر نامعتبر.';}
                if (empty($data['price'])) $data['price'] = null; 
                if (empty($data['stock_quantity'])) $data['stock_quantity'] = 0; 
            }

            // Image upload processing
            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == UPLOAD_ERR_OK) {
                // ... (کد آپلود تصویر با حذف تصویر قدیمی در صورت موفقیت) ...
            } elseif (isset($_FILES['product_image']) && $_FILES['product_image']['error'] != UPLOAD_ERR_NO_FILE) {
                $data['image_err'] = 'خطا در آپلود. کد: ' . $_FILES['product_image']['error'];
            }

            if (empty($data['name_err']) && empty($data['price_err']) && empty($data['stock_quantity_err']) && empty($data['image_err']) && empty($data['product_type_err']) /* && empty($data['initial_stock_quantity_err']) */) {
                if ($this->productModel->updateProduct($data)) {
                    $selected_configurable_attributes = isset($_POST['configurable_attributes']) && is_array($_POST['configurable_attributes']) ? $_POST['configurable_attributes'] : [];
                    if ($data['product_type'] === 'variable') {
                        $this->attributeModel->setConfigurableAttributesForProduct($id, $selected_configurable_attributes);
                    } else { 
                        $this->attributeModel->setConfigurableAttributesForProduct($id, []);
                        if (method_exists($this->attributeModel, 'deleteAllVariationsForProduct')) {
                            $this->attributeModel->deleteAllVariationsForProduct($id);
                        }
                    }
                    flash('product_updated_success', 'محصول شما با موفقیت ویرایش شد.');
                    header('Location: ' . BASE_URL . 'vendor/myProducts'); 
                    exit();
                } else { 
                    flash('product_action_fail', 'خطا در ویرایش محصول در پایگاه داده.', 'alert alert-danger');
                    if ($data['image_url'] !== $current_image_url && defined('FCPATH') && file_exists(FCPATH . $data['image_url'])) {
                        unlink(FCPATH . $data['image_url']); 
                        $data['image_url'] = $current_image_url; 
                    }
                }
            }
            
            $data['pageTitle'] = 'ویرایش محصول: ' . htmlspecialchars($product['name']);
            $data['categories'] = $this->categoryModel->getAllCategories();
            $data['all_attributes'] = $this->attributeModel->getAllAttributes();
            $data['configurable_attributes_for_product'] = $this->attributeModel->getConfigurableAttributesForProduct($id);
            if( (!empty($data['image_err']) && $data['image_url'] === $current_image_url) || (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == UPLOAD_ERR_NO_FILE) ){
                 $data['image_url'] = $product['image_url']; 
            }
            $this->view('vendor/products/edit', $data);
            exit();
        } else { // GET
            $data = [
                'pageTitle' => 'ویرایش محصول: ' . htmlspecialchars($product['name']),
                'id' => $product['id'], 
                'name' => $product['name'], 
                'description' => $product['description'],
                'price' => $product['price'], 
                'image_url' => $product['image_url'],
                'stock_quantity' => $product['stock_quantity'],
                'initial_stock_quantity' => isset($product['initial_stock_quantity']) ? $product['initial_stock_quantity'] : $product['stock_quantity'],
                'category_id' => $product['category_id'], 
                'product_type' => $product['product_type'],
                'categories' => $this->categoryModel->getAllCategories(),
                'all_attributes' => $this->attributeModel->getAllAttributes(),
                'configurable_attributes_for_product' => $this->attributeModel->getConfigurableAttributesForProduct($id),
                'name_err' => '', 'price_err' => '', 'stock_quantity_err' => '', 'image_err' => '', 'product_type_err' => ''
            ];
            $this->view('vendor/products/edit', $data);
        }
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
    public function requestPayout() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $vendor_id = $_SESSION['user_id'];
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $payment_details_input = isset($_POST['payment_details']) ? trim($_POST['payment_details']) : ''; 

            $unpaid_items = $this->orderModel->getUnpaidOrderItemsForVendor($vendor_id);
            $calculated_withdrawable_amount = 0;
            $order_item_ids_for_payout = [];

            if (empty($unpaid_items)) {
                flash('payout_fail', 'در حال حاضر هیچ درآمد قابل تسویه‌ای برای شما وجود ندارد.', 'alert alert-warning');
                header('Location: ' . BASE_URL . 'vendor/dashboard');
                exit();
            }

            foreach($unpaid_items as $item) {
                if (isset($item['vendor_earning']) && isset($item['order_item_id'])) {
                    $calculated_withdrawable_amount += (float)$item['vendor_earning'];
                    $order_item_ids_for_payout[] = $item['order_item_id'];
                }
            }
            
            if ($calculated_withdrawable_amount <= 0) { 
                flash('payout_fail', 'مبلغ قابل برداشت شما صفر است.', 'alert alert-danger');
            } elseif (empty($payment_details_input)) {
                 flash('payout_fail', 'لطفاً اطلاعات حساب برای واریز را مشخص کنید (مثلاً شماره شبا).', 'alert alert-danger');
            }
            else {
                $payout_result = $this->orderModel->requestVendorPayout(
                    $vendor_id, 
                    $calculated_withdrawable_amount, 
                    $order_item_ids_for_payout, 
                    'bank_transfer', 
                    $payment_details_input
                );

                if (is_int($payout_result) && $payout_result > 0) { 
                    flash('payout_success', 'درخواست تسویه شما برای مبلغ ' . number_format($calculated_withdrawable_amount) . ' تومان با شناسه پیگیری #' . $payout_result . ' با موفقیت ثبت شد.', 'alert alert-success');
                } else {
                    $errorMessage = 'خطا در ثبت درخواست تسویه.';
                    if ($payout_result === 'duplicate_combination') { // This error code is for addVariation, not payout.
                        $errorMessage = 'خطا: این ترکیب از ویژگی‌ها قبلاً برای این محصول ثبت شده است.'; // Should not happen here
                    } elseif (strpos((string)$payout_result, 'db_error') === 0 || $payout_result === 'pdo_exception' || $payout_result === 'general_exception') {
                        $errorMessage = 'خطای داخلی سرور هنگام ثبت درخواست تسویه. لطفاً لاگ‌ها را بررسی کنید.';
                    } else if ($payout_result === 'no_items_for_payout'){
                        $errorMessage = 'هیچ آیتم پرداخت نشده‌ای برای تسویه یافت نشد.';
                    }
                    flash('payout_fail', $errorMessage, 'alert alert-danger');
                }
            }
            header('Location: ' . BASE_URL . 'vendor/dashboard'); 
            exit();

        } else { 
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
