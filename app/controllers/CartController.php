<?php
// app/controllers/CartController.php

class CartController extends Controller {
    private $productModel;
    private $attributeModel; // For variation details if needed

    public function __construct(){
        $this->productModel = $this->model('Product');
        $this->attributeModel = $this->model('ProductAttribute');
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        // Initialize cart if not already set
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public function index(){
        $cart_items_from_session = $_SESSION['cart'];
        $detailed_cart_items = [];
        $total_price = 0;

        error_log("CartController::index - SESSION CART at start: " . print_r($cart_items_from_session, true));

        if (!empty($cart_items_from_session)) {
            foreach ($cart_items_from_session as $item_key => $cart_item_data) {
                // $item_key could be 'product_id' or 'product_id-variation_id'
                // $cart_item_data should contain 'product_id', 'quantity', and optionally 'variation_id'
                
                if (!is_array($cart_item_data) || !isset($cart_item_data['product_id']) || !isset($cart_item_data['quantity'])) {
                    error_log("CartController::index - Invalid cart item structure for key {$item_key}: " . print_r($cart_item_data, true));
                    unset($_SESSION['cart'][$item_key]); // Remove malformed item
                    continue;
                }

                $product_id = (int)$cart_item_data['product_id'];
                $quantity = (int)$cart_item_data['quantity'];
                $variation_id = isset($cart_item_data['variation_id']) ? (int)$cart_item_data['variation_id'] : null;

                $product_details = null;
                $item_price = 0;
                $item_name = 'محصول ناموجود';
                $item_image_url = null;
                $variation_name_display = '';

                if ($variation_id) {
                    $variation_details = $this->attributeModel->getVariationById($variation_id);
                    if ($variation_details && $variation_details['parent_product_id'] == $product_id) {
                        $product_details = $this->productModel->getProductById($product_id); // Get parent product for some details
                        $item_price = (float)$variation_details['price'];
                        $item_name = $product_details ? $product_details['name'] : $variation_details['parent_product_name']; // Fallback
                        $item_image_url = $variation_details['image_url'] ?? $product_details['image_url'] ?? null;
                        
                        $attrs_display = [];
                        if (!empty($variation_details['attributes'])) {
                            foreach ($variation_details['attributes'] as $attr_val) {
                                $attrs_display[] = htmlspecialchars($attr_val['attribute_value']);
                            }
                        }
                        $variation_name_display = implode(' / ', $attrs_display);
                    } else {
                        error_log("CartController::index - Variation ID {$variation_id} not found or mismatch for product ID {$product_id}. Removing from cart.");
                        unset($_SESSION['cart'][$item_key]);
                        continue;
                    }
                } else {
                    $product_details = $this->productModel->getProductById($product_id);
                    if ($product_details) {
                        $item_price = (float)$product_details['price'];
                        $item_name = $product_details['name'];
                        $item_image_url = $product_details['image_url'];
                    } else {
                        error_log("CartController::index - Product ID {$product_id} not found. Removing from cart.");
                        unset($_SESSION['cart'][$item_key]);
                        continue;
                    }
                }
                
                if ($quantity <= 0) { // Should not happen if add/update logic is correct
                    error_log("CartController::index - Item {$item_key} has zero or negative quantity. Removing from cart.");
                    unset($_SESSION['cart'][$item_key]);
                    continue;
                }

                $detailed_cart_items[$item_key] = [
                    'product_id' => $product_id,
                    'variation_id' => $variation_id,
                    'name' => $item_name,
                    'variation_name' => $variation_name_display,
                    'quantity' => $quantity,
                    'price' => $item_price,
                    'subtotal' => $item_price * $quantity,
                    'image_url' => $item_image_url
                ];
                $total_price += $item_price * $quantity;
                error_log("CartController::index - Item {$item_key}: Qty={$quantity}, Price={$item_price}, Subtotal=" . ($item_price * $quantity) . ", Current Total={$total_price}");
            }
        }
        
        // Update session cart if any items were removed due to inconsistency
        $_SESSION['cart'] = array_filter($_SESSION['cart']); // Remove any items that became null/false

        $data = [
            'pageTitle' => 'سبد خرید شما',
            'cartItems' => $detailed_cart_items, // Pass detailed items to view
            'totalPrice' => $total_price
        ];
        $this->view('cart/index', $data);
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize POST data
            $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
            $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $variation_id = filter_input(INPUT_POST, 'variation_id', FILTER_VALIDATE_INT);
            // For selected attributes if sent from product page for variable product
            // $attributes_selected = $_POST['attributes'] ?? []; 

            if (!$product_id || !$quantity) {
                flash('cart_error', 'اطلاعات محصول نامعتبر است.', 'alert alert-danger');
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'products'));
                exit();
            }

            $item_key = $product_id;
            if ($variation_id) {
                $item_key .= '-' . $variation_id;
            }

            // Fetch product/variation details to get price and stock
            $item_details_for_cart = null;
            if ($variation_id) {
                $variation = $this->attributeModel->getVariationById($variation_id);
                if ($variation && $variation['parent_product_id'] == $product_id && $variation['is_active']) {
                    if ($variation['stock_quantity'] >= $quantity) {
                        $parent_product = $this->productModel->getProductById($product_id);
                        $item_details_for_cart = [
                            'product_id' => $product_id,
                            'variation_id' => $variation_id,
                            'name' => $parent_product['name'] ?? 'محصول', // Use parent name
                            'variation_name' => implode(' / ', array_column($variation['attributes'] ?? [], 'attribute_value')),
                            'price' => $variation['price'],
                            'image_url' => $variation['image_url'] ?? $parent_product['image_url'] ?? null,
                            'max_stock' => $variation['stock_quantity']
                        ];
                    } else {
                        flash('cart_error', 'موجودی تنوع انتخاب شده کافی نیست.', 'alert alert-warning');
                    }
                } else {
                    flash('cart_error', 'تنوع محصول نامعتبر یا غیرفعال است.', 'alert alert-danger');
                }
            } else { // Simple product
                $product = $this->productModel->getProductById($product_id);
                if ($product && $product['product_type'] === 'simple') {
                    if ($product['stock_quantity'] >= $quantity) {
                        $item_details_for_cart = [
                            'product_id' => $product_id,
                            'variation_id' => null,
                            'name' => $product['name'],
                            'variation_name' => '',
                            'price' => $product['price'],
                            'image_url' => $product['image_url'],
                            'max_stock' => $product['stock_quantity']
                        ];
                    } else {
                        flash('cart_error', 'موجودی محصول کافی نیست.', 'alert alert-warning');
                    }
                } else {
                    flash('cart_error', 'محصول نامعتبر است.', 'alert alert-danger');
                }
            }

            if ($item_details_for_cart) {
                if (isset($_SESSION['cart'][$item_key])) {
                    $new_quantity = $_SESSION['cart'][$item_key]['quantity'] + $quantity;
                    if ($new_quantity <= $item_details_for_cart['max_stock']) {
                        $_SESSION['cart'][$item_key]['quantity'] = $new_quantity;
                        flash('cart_message', '"' . htmlspecialchars($item_details_for_cart['name']) . '" به سبد خرید اضافه شد.');
                    } else {
                         flash('cart_error', 'تعداد درخواستی برای "' . htmlspecialchars($item_details_for_cart['name']) . '" بیش از موجودی است.', 'alert alert-warning');
                    }
                } else {
                    $_SESSION['cart'][$item_key] = [
                        'product_id' => $item_details_for_cart['product_id'],
                        'variation_id' => $item_details_for_cart['variation_id'],
                        'name' => $item_details_for_cart['name'],
                        'variation_name' => $item_details_for_cart['variation_name'],
                        'price' => $item_details_for_cart['price'],
                        'quantity' => $quantity,
                        'image_url' => $item_details_for_cart['image_url']
                        // 'max_stock' is not stored in session, re-checked on cart/checkout if needed
                    ];
                    flash('cart_message', '"' . htmlspecialchars($item_details_for_cart['name']) . '" به سبد خرید اضافه شد.');
                }
            }
            // Redirect back to product page or cart page
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'cart'));
            exit();
        } else {
            // Not a POST request, redirect to home or products
            header('Location: ' . BASE_URL);
            exit();
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $item_key = $_POST['item_key'] ?? null; // Use item_key from form
            $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]); // Allow 0 to remove

            if ($item_key === null || $quantity === false) {
                flash('cart_error', 'اطلاعات نامعتبر برای به‌روزرسانی سبد.', 'alert alert-danger');
            } elseif ($quantity == 0) {
                // If quantity is 0, remove the item
                unset($_SESSION['cart'][$item_key]);
                flash('cart_message', 'محصول از سبد خرید حذف شد.');
            } elseif (isset($_SESSION['cart'][$item_key])) {
                // Here you should re-validate stock before updating quantity
                $cart_item_data = $_SESSION['cart'][$item_key];
                $product_id = (int)$cart_item_data['product_id'];
                $variation_id = isset($cart_item_data['variation_id']) ? (int)$cart_item_data['variation_id'] : null;
                $current_stock = 0;

                if ($variation_id) {
                    $variation = $this->attributeModel->getVariationById($variation_id);
                    if ($variation && $variation['parent_product_id'] == $product_id) {
                        $current_stock = (int)$variation['stock_quantity'];
                    }
                } else {
                    $product = $this->productModel->getProductById($product_id);
                    if ($product) {
                        $current_stock = (int)$product['stock_quantity'];
                    }
                }

                if ($quantity <= $current_stock) {
                    $_SESSION['cart'][$item_key]['quantity'] = $quantity;
                    flash('cart_message', 'تعداد محصول در سبد خرید به‌روز شد.');
                } else {
                    flash('cart_error', 'تعداد درخواستی بیش از موجودی انبار است. موجودی فعلی: ' . $current_stock, 'alert alert-warning');
                    // Optionally, set quantity to max available stock
                    // $_SESSION['cart'][$item_key]['quantity'] = $current_stock;
                }
            }
        }
        header('Location: ' . BASE_URL . 'cart');
        exit();
    }

    public function remove() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $item_key_to_remove = $_POST['item_key_to_remove'] ?? null; // Use item_key from form
            if ($item_key_to_remove !== null && isset($_SESSION['cart'][$item_key_to_remove])) {
                unset($_SESSION['cart'][$item_key_to_remove]);
                flash('cart_message', 'محصول از سبد خرید حذف شد.');
            } else {
                flash('cart_error', 'خطا در حذف محصول از سبد.', 'alert alert-danger');
            }
        }
        header('Location: ' . BASE_URL . 'cart');
        exit();
    }

    public function clear() {
        $_SESSION['cart'] = [];
        flash('cart_message', 'سبد خرید شما خالی شد.');
        header('Location: ' . BASE_URL . 'cart');
        exit();
    }
}
?>
