<?php
// app/controllers/ErrorController.php

class ErrorController extends Controller {

    public function __construct(){
        // سازنده
    }

    public function notFound($message = "صفحه مورد نظر یافت نشد.") {
        header("HTTP/1.0 404 Not Found"); // ارسال هدر صحیح 404
        $data = [
            'pageTitle' => 'خطای 404 - صفحه یافت نشد',
            'errorMessage' => $message
        ];
        // توجه: در متد view از Controller.php، اگر خود ویو 404 پیدا نشود، ممکن است به حلقه بیفتد.
        // باید مطمئن شویم ویو 'errors/404' وجود دارد.
        if (file_exists('../app/views/errors/404.php')) {
             $this->view('errors/404', $data);
        } else {
            // Fallback very basic error if the 404 view itself is missing
            echo "<h1>404 Not Found</h1><p>" . htmlspecialchars($message) . "</p><p>Additionally, the error view file is missing.</p>";
        }
    }

    // می‌توانید متدهای دیگری برای انواع دیگر خطاها اضافه کنید
    // public function forbidden() {
    //     header("HTTP/1.0 403 Forbidden");
    //     $data = [
    //         'pageTitle' => 'خطای 403 - دسترسی غیرمجاز',
    //         'errorMessage' => 'شما اجازه دسترسی به این صفحه را ندارید.'
    //     ];
    //     $this->view('errors/403', $data); // نیاز به ایجاد ویو 403.php
    // }
}
?>