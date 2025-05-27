<?php
// app/controllers/PagesController.php

// کلاس Controller پایه باید قبلاً توسط Router یا index.php بارگذاری شده باشد.
class PagesController extends Controller { // ارث‌بری از کلاس Controller پایه

    public function __construct() {
        // در اینجا می‌توانید مدل‌هایی که در تمام متدهای این کنترلر نیاز دارید را بارگذاری کنید
        // مثال: $this->productModel = $this->model('Product');
    }

    // متد پیش‌فرض برای صفحه اصلی سایت
    public function index() {
        $data = [
            'pageTitle' => 'صفحه اصلی فروشگاه',
            'welcomeMessage' => 'به فروشگاه اینترنتی ما خوش آمدید!'
        ];
        // متد view از کلاس Controller والد به ارث رسیده است.
        // این متد فایل 'app/views/pages/index.php' را بارگذاری می‌کند.
        $this->view('pages/index', $data);
    }

    // متد برای نمایش صفحه "درباره ما"
    public function about() {
        $data = [
            'pageTitle' => 'درباره ما',
            'description' => 'این یک فروشگاه اینترنتی آزمایشی است که با PHP در حال ساخت می‌باشد.'
        ];
        $this->view('pages/about', $data); // بارگذاری فایل 'app/views/pages/about.php'
    }

    // شما می‌توانید متدهای دیگری برای صفحات دیگر در اینجا اضافه کنید.
}
?>