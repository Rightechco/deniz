<?php
// app/core/Router.php

class Router {
    protected $currentController = 'PagesController'; // کنترلر پیش‌فرض
    protected $currentMethod = 'index';             // متد پیش‌فرض
    protected $params = [];                         // پارامترها

    public function __construct(){
        $url = $this->getUrl();

        // بخش ۱: تنظیم کنترلر
        if(isset($url[0]) && !empty($url[0])){
            $controllerName = ucwords($url[0]) . 'Controller';
            if(file_exists('../app/controllers/' . $controllerName . '.php')){
                $this->currentController = $controllerName;
                unset($url[0]);
            } else {
                // اگر فایل کنترلر درخواستی وجود نداشت، به کنترلر خطا بروید
                $this->loadErrorController('notFound', "کنترلر درخواستی یافت نشد: " . $controllerName);
                return; // اجرای روتر را متوقف کن چون به کنترلر خطا رفته‌ایم
            }
        } else {
            // اگر هیچ کنترلری در URL نیست، از پیش‌فرض (PagesController) استفاده کن
            // اطمینان از وجود فایل کنترلر پیش‌فرض
             if (!file_exists('../app/controllers/' . $this->currentController . '.php')) {
                 $this->loadErrorController('serverError', "کنترلر پیش‌فرض یافت نشد: " . $this->currentController);
                 return;
             }
        }
        
        // بخش ۲: بارگذاری و نمونه‌سازی کنترلر
        require_once '../app/controllers/'. $this->currentController . '.php';
        if(class_exists($this->currentController)){
            $this->currentController = new $this->currentController;
        } else {
            // این حالت نباید اتفاق بیفتد اگر فایل وجود دارد و نام کلاس صحیح است
            error_log("Router Error: Class " . $this->currentController . " not found AFTER requiring the file.");
            $this->loadErrorController('serverError', "کلاس کنترلر یافت نشد: " . $this->currentController);
            return;
        }

        // بخش ۳: تنظیم متد
        if(isset($url[1]) && !empty($url[1])){
            if(method_exists($this->currentController, $url[1])){
                $this->currentMethod = $url[1];
                unset($url[1]);
            } else {
                // اگر متد درخواستی در کنترلر فعلی وجود نداشت
                error_log("Router Error: Method " . $url[1] . " not found in controller " . get_class($this->currentController));
                if(method_exists($this->currentController, 'notFound')){ // آیا خود کنترلر متد notFound دارد؟
                    $this->currentMethod = 'notFound';
                } else { // اگر نه، از ErrorController استفاده کن
                    $this->loadErrorController('notFound', "متد درخواستی یافت نشد: " . $url[1]);
                    return;
                }
                $url = []; // پارامترهای بعدی برای متد notFound نامربوط هستند
            }
        } else {
            // اگر متدی در URL نیست، از متد پیش‌فرض (معمولاً index) استفاده کن، به شرطی که وجود داشته باشد
            if (!method_exists($this->currentController, $this->currentMethod)) {
                 error_log("Router Error: Default method '" . $this->currentMethod . "' not found in controller " . get_class($this->currentController));
                 $this->loadErrorController('notFound', "متد پیش‌فرض کنترلر یافت نشد.");
                 return;
            }
        }
        
        // بخش ۴: دریافت پارامترها
        $this->params = $url ? array_values($url) : [];

        // بخش ۵: فراخوانی متد کنترلر با پارامترها
        if (is_callable([$this->currentController, $this->currentMethod])) {
            call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
        } else {
            $controllerClass = is_object($this->currentController) ? get_class($this->currentController) : $this->currentController;
            error_log("Router Critical Error: Method {$this->currentMethod} not callable on controller {$controllerClass}.");
            $this->loadErrorController('serverError', "متد قابل فراخوانی نیست: {$controllerClass}::{$this->currentMethod}");
            return;
        }
    }

    public function getUrl(){
        if(isset($_GET['url'])){
            $url = rtrim($_GET['url'], '/');
            // پاکسازی URL از کاراکترهای غیرمجاز و تگ‌های HTML احتمالی
            $url = filter_var($url, FILTER_SANITIZE_URL); 
            // فیلتر اضافی برای امنیت بیشتر (حذف کاراکترهایی که معمولاً در URL نیستند)
            $url = preg_replace('/[^a-zA-Z0-9_=\/\-\?\&\%]/', '', $url);

            if ($url !== false && $url !== '') {
                 $url = explode('/', $url);
                 return $url;
            }
        }
        return []; 
    }

    /**
     * Helper function to load and call the ErrorController.
     */
    private function loadErrorController($method = 'notFound', $message = '') {
        if (file_exists('../app/controllers/ErrorController.php')) {
            require_once '../app/controllers/ErrorController.php';
            if (class_exists('ErrorController')) {
                $errorController = new ErrorController();
                if (method_exists($errorController, $method)) {
                    call_user_func_array([$errorController, $method], [$message]);
                } else {
                    die("Critical Error: Method {$method} not found in ErrorController.");
                }
            } else {
                die('Critical Error: ErrorController class not found.');
            }
        } else {
            die('Critical Error: ErrorController.php file not found.');
        }
    }
}
?>
