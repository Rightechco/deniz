<?php
// app/controllers/CustomerController.php

class CustomerController extends Controller {
    private $orderModel;
    private $userModel;

    public function __construct() {
        $this->orderModel = $this->model('Order');
        $this->userModel = $this->model('User'); // ممکن است برای نمایش اطلاعات کاربر لازم شود

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // تمام متدهای این کنترلر نیاز به لاگین کاربر دارند
        if (!isset($_SESSION['user_id'])) {
            flash('auth_required', 'برای دسترسی به این بخش، لطفاً ابتدا وارد شوید.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'auth/login');
            exit();
        }
        // همچنین می‌توانیم بررسی کنیم که نقش کاربر 'customer' باشد اگر پنل‌های جداگانه‌ای برای نقش‌های دیگر داریم
        // if ($_SESSION['user_role'] !== 'customer') {
        //     flash('access_denied', 'شما اجازه دسترسی به این بخش را ندارید.', 'alert alert-danger');
        //     header('Location: ' . BASE_URL); // یا به داشبورد مربوط به نقش خودش
        //     exit();
        // }
    }

    /**
     * نمایش تاریخچه سفارشات کاربر لاگین کرده
     * URL: customer/orders
     */
    public function orders() {
        $user_id = $_SESSION['user_id'];
        $orders = $this->orderModel->getOrdersByUserId($user_id);

        $data = [
            'pageTitle' => 'تاریخچه سفارشات شما',
            'orders' => $orders
        ];
        $this->view('customer/orders', $data);
    }

    /**
     * نمایش جزئیات یک سفارش خاص (در آینده تکمیل می‌شود)
     * URL: customer/orderDetails/ORDER_ID
     */
    public function orderDetails($order_id = null) {
        if (is_null($order_id) || !is_numeric($order_id)) {
            flash('error_message', 'شناسه سفارش نامعتبر است.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'customer/orders');
            exit();
        }

        $user_id = $_SESSION['user_id'];
        $order = $this->orderModel->getOrderDetailsById((int)$order_id, $user_id);

        if ($order) {
            $data = [
                'pageTitle' => 'جزئیات سفارش #' . htmlspecialchars($order['id']),
                'order' => $order
            ];
            $this->view('customer/order_details', $data);
        } else {
            flash('error_message', 'سفارش مورد نظر یافت نشد یا شما اجازه دسترسی به آن را ندارید.', 'alert alert-danger');
            header('Location: ' . BASE_URL . 'customer/orders');
            exit();
        }
    }

    // متدهای دیگر پنل کاربری مانند ویرایش پروفایل و ... در آینده اضافه می‌شوند
    // public function profile() { ... }
}
?>
