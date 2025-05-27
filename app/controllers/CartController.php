<?php
// app/controllers/CartController.php

class CartController extends Controller {
    private $productModel;
    private $attributeModel; // برای خواندن اطلاعات تنوع

    public function __construct() {
        $this->productModel = $this->model('Product');
        $this->attributeModel = $this->model('ProductAttribute'); // یا هر نامی که برای مدل ویژگی‌ها گذاشته‌اید

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
            // variation_id از فرم صفحه محصول متغیر ارسال می‌شود
            $variation_id = isset($_POST['variation_id']) && !empty($_POST['variation_id']) ? (int)$_POST['variation_id'] : null;

            if ($product_id > 0 && $quantity > 0) {
                $item_id_in_cart = $product_id; // پیش‌فرض برای محصول ساده
                if ($variation_id) {
                    $item_id_in_cart = $product_id . '_v_' . $variation_id; // شناسه یکتا برای تنوع در سبد
                }

                $item_name = '';
                $item_price = 0;
                $item_image_url = '';
                $item_stock_available = 0;
                $is_valid_item = false;

                $parentProduct = $this->productModel->getProductById($product_id);
                if (!$parentProduct) {
                    flash('cart_action_fail', 'محصول اصلی یافت نشد.', 'alert alert-danger');
                    header('Location: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : BASE_URL . 'products'));
                    exit();
                }

                if ($variation_id) { // محصول متغیر با تنوع انتخاب شده
                    $variation = $this->attributeModel->getVariationById($variation_id);

                    if ($variation && $variation['parent_product_id'] == $product_id && $variation['is_active']) {
                        $item_name = $parentProduct['name'] . " (";
                        $attr_names = [];
                        if(!empty($variation['attributes'])){
                            foreach($variation['attributes'] as $attr) {
                                $attr_names[] = htmlspecialchars($attr['attribute_value']);
                            }
                        }
                        $item_name .= implode(' - ', $attr_names) . ")";
                        $item_price = $variation['price'] !== null ? $variation['price'] : $parentProduct['price']; // اگر قیمت تنوع null بود، از قیمت والد استفاده کن
                        $item_image_url = !empty($variation['image_url']) ? $variation['image_url'] : $parentProduct['image_url'];
                        $item_stock_available = (int)$variation['stock_quantity'];
                        $is_valid_item = true;
                    } else if ($variation && !$variation['is_active']) {
                         flash('cart_action_fail', 'تنوع محصول انتخاب شده فعال نمی‌باشد.', 'alert alert-danger');
                    } else if ($variation && $variation['stock_quantity'] < $quantity && (!isset($_SESSION['cart'][$item_id_in_cart]) || $variation['stock_quantity'] < ($_SESSION['cart'][$item_id_in_cart]['quantity'] + $quantity))) {
                        flash('cart_action_fail', 'موجودی تنوع انتخاب شده کافی نیست.', 'alert alert-danger');
                    }
                     else {
                         flash('cart_action_fail', 'تنوع محصول مورد نظر یافت نشد.', 'alert alert-danger');
                    }
                } else { // محصول ساده
                    if ($parentProduct['product_type'] === 'simple') {
                        $item_name = $parentProduct['name'];
                        $item_price = $parentProduct['price'];
                        $item_image_url = $parentProduct['image_url'];
                        $item_stock_available = (int)$parentProduct['stock_quantity'];
                        $is_valid_item = true;
                    } else {
                        // اگر محصول متغیر است اما variation_id ارسال نشده
                        flash('cart_action_fail', 'لطفاً گزینه‌های محصول متغیر را به درستی انتخاب کنید.', 'alert alert-danger');
                    }
                }

                if ($is_valid_item) {
                    $requested_total_quantity = $quantity;
                    if (isset($_SESSION['cart'][$item_id_in_cart])) {
                        $requested_total_quantity = $_SESSION['cart'][$item_id_in_cart]['quantity'] + $quantity;
                    }

                    if ($item_stock_available >= $requested_total_quantity) {
                        if (isset($_SESSION['cart'][$item_id_in_cart])) {
                            $_SESSION['cart'][$item_id_in_cart]['quantity'] += $quantity;
                        } else {
                            $_SESSION['cart'][$item_id_in_cart] = [
                                'product_id' => $product_id,      // شناسه محصول والد
                                'variation_id' => $variation_id,  // شناسه تنوع (برای محصول ساده null است)
                                'name' => $item_name,
                                'price' => $item_price,
                                'image_url' => $item_image_url,
                                'quantity' => $quantity
                            ];
                        }
                        flash('cart_action_success', htmlspecialchars($item_name) . ' با موفقیت به سبد خرید اضافه شد/به‌روز شد.');
                    } else {
                        flash('cart_action_fail', 'موجودی محصول ' . htmlspecialchars($item_name) . ' برای تعداد درخواستی (' . $requested_total_quantity . ') کافی نیست (موجودی: ' . $item_stock_available . ').', 'alert alert-danger');
                    }
                }
            } else {
                flash('cart_action_fail', 'اطلاعات محصول نامعتبر است.', 'alert alert-danger');
            }
            
            $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : BASE_URL . 'products';
            // جلوگیری از ریدایرکت لوپ به خود اکشن cart/add در صورت رفرش یا خطای مستقیم
            if (strpos($redirect_url, 'cart/add') !== false) { 
                $redirect_url = BASE_URL . 'products/show/' . $product_id; // بازگشت به صفحه محصول
            }
            header('Location: ' . $redirect_url);
            exit();
        } else {
            header('Location: ' . BASE_URL . 'products');
            exit();
        }
    }

    public function index() {
        $cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
        $total_price = 0;
        foreach ($cart_items as $item) {
            $price = isset($item['price']) ? (float)$item['price'] : 0;
            $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;
            $total_price += $price * $quantity;
        }
        $data = [
            'pageTitle' => 'سبد خرید شما',
            'cart_items' => $cart_items,
            'total_price' => $total_price
        ];
        $this->view('cart/index', $data);
    }

    public function updateQuantity() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart_action'])) {
            $quantities = isset($_POST['quantity']) && is_array($_POST['quantity']) ? $_POST['quantity'] : [];
            $updated_count = 0;
            $error_messages = [];

            foreach ($quantities as $item_cart_id => $new_quantity) { 
                $new_quantity = (int)$new_quantity;

                if (isset($_SESSION['cart'][$item_cart_id])) {
                    $cart_item = $_SESSION['cart'][$item_cart_id];
                    $product_id_for_stock_check = $cart_item['product_id']; // شناسه محصول والد
                    $variation_id_for_stock_check = $cart_item['variation_id'];
                    $current_stock = 0;
                    $product_name_for_msg = $cart_item['name'];

                    if ($variation_id_for_stock_check) {
                        $variation = $this->attributeModel->getVariationById($variation_id_for_stock_check);
                        if($variation && $variation['is_active']) {
                            $current_stock = (int)$variation['stock_quantity'];
                        } else { 
                            $error_messages[] = 'تنوع محصول ' . htmlspecialchars($product_name_for_msg) . ' دیگر معتبر نیست.'; 
                            continue; // برو به آیتم بعدی
                        }
                    } else { // محصول ساده
                        $product = $this->productModel->getProductById($product_id_for_stock_check);
                        if($product) {
                            $current_stock = (int)$product['stock_quantity'];
                        } else { 
                            $error_messages[] = 'محصول ' . htmlspecialchars($product_name_for_msg) . ' دیگر معتبر نیست.'; 
                            continue; // برو به آیتم بعدی
                        }
                    }

                    if ($new_quantity > 0) {
                        if ($current_stock >= $new_quantity) {
                            if ($cart_item['quantity'] != $new_quantity) {
                                $_SESSION['cart'][$item_cart_id]['quantity'] = $new_quantity;
                                $updated_count++;
                            }
                        } else {
                            $error_messages[] = 'موجودی محصول ' . htmlspecialchars($product_name_for_msg) . ' برای تعداد ' . $new_quantity . ' کافی نیست (موجودی: ' . $current_stock . ').';
                        }
                    } else { // اگر تعداد 0 یا کمتر وارد شده، محصول را حذف کن
                        unset($_SESSION['cart'][$item_cart_id]);
                        $updated_count++; 
                    }
                }
            }

            if ($updated_count > 0 && empty($error_messages)) { flash('cart_action_success', 'سبد خرید با موفقیت به‌روز شد.'); }
            elseif ($updated_count > 0 && !empty($error_messages)) { flash('cart_action_success', 'بخشی از سبد خرید به‌روز شد.');}
            if (!empty($error_messages)) { flash('cart_action_fail', implode("<br>", $error_messages), 'alert alert-danger');}
            elseif ($updated_count == 0 && empty($error_messages)) { flash('cart_action_info', 'هیچ تغییری در سبد خرید اعمال نشد.', 'alert alert-info');}
            
            header('Location: ' . BASE_URL . 'cart/index');
            exit();
        } else {
            header('Location: ' . BASE_URL . 'cart/index');
            exit();
        }
    }

    public function remove($item_cart_id = null) { 
        if ($item_cart_id !== null && isset($_SESSION['cart'][$item_cart_id])) {
            $product_name = $_SESSION['cart'][$item_cart_id]['name'];
            unset($_SESSION['cart'][$item_cart_id]);
            flash('cart_action_success', 'محصول ' . htmlspecialchars($product_name) . ' با موفقیت از سبد خرید حذف شد.');
        } else {
            flash('cart_action_fail', 'محصول مورد نظر برای حذف در سبد خرید یافت نشد.', 'alert alert-danger');
        }
        header('Location: ' . BASE_URL . 'cart/index');
        exit();
    }
}
?>