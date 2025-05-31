<?php
// app/controllers/PagesController.php

class PagesController extends Controller {
    private $productModel;
    private $categoryModel;
    // private $sliderModel; // Optional: if you have a separate model for sliders

    public function __construct(){
        $this->productModel = $this->model('Product');
        $this->categoryModel = $this->model('Category');
        // $this->sliderModel = $this->model('Slider'); 
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index(){
        // --- Fetching Data for Homepage ---

        // 1. Main Slider Data (Example - implement fetching from DB or settings)
        // You should create a mechanism to manage these slides, e.g., from admin panel
        $main_slides = [
            [
                'image_url' => 'images/slider/default_slide_1.jpg', // Path relative to public folder
                'alt_text' => 'اسلاید اول: تخفیف‌های ویژه',
                'caption_title' => 'فروش فوق‌العاده بهاره!',
                'caption_text' => 'تا ۳۰٪ تخفیف بر روی محصولات منتخب. فرصت را از دست ندهید!',
                'link_url' => BASE_URL . 'products?filter=sale',
                'link_text' => 'اکنون خرید کنید'
            ],
            [
                'image_url' => 'images/slider/default_slide_2.jpg',
                'alt_text' => 'اسلاید دوم: معرفی محصولات جدید',
                'caption_title' => 'جدیدترین محصولات رسید!',
                'caption_text' => 'آخرین روندها و نوآوری‌ها را با ما تجربه کنید.',
                'link_url' => BASE_URL . 'products?sort=latest',
                'link_text' => 'مشاهده جدیدترین‌ها'
            ],
        ];
        // Fallback placeholder if no slides are configured
        if (empty($main_slides)) {
             $main_slides = [
                ['image_url' => 'https://placehold.co/1920x600/06b6d4/ffffff?text=اسلاید+جایگزین+۱&font=vazirmatn', 'alt_text' => 'اسلاید ۱', 'caption_title' => 'به فروشگاه ما خوش آمدید', 'caption_text' => 'بهترین‌ها را از ما بخواهید', 'link_url' => BASE_URL.'products', 'link_text' => 'مشاهده محصولات'],
                ['image_url' => 'https://placehold.co/1920x600/ec4899/ffffff?text=اسلاید+جایگزین+۲&font=vazirmatn', 'alt_text' => 'اسلاید ۲', 'caption_title' => 'تخفیف‌های باورنکردنی', 'caption_text' => 'همین حالا خرید کنید و لذت ببرید', 'link_url' => BASE_URL.'products?filter=discounted', 'link_text' => 'مشاهده تخفیف‌ها'],
            ];
        }

        // 2. Parent Categories (Requires getParentCategories in CategoryModel)
        // This method should return categories that are top-level (e.g., parent_id IS NULL or 0)
        // It should also ideally return an 'image_url' and 'slug' for each category.
        $parent_categories = method_exists($this->categoryModel, 'getParentCategories') 
                             ? $this->categoryModel->getParentCategories(6) // Fetch up to 6 parent categories
                             : []; 
        if (empty($parent_categories)) { // Placeholder data if method or data is missing
            $parent_categories = [
                ['id' => 1, 'name' => 'پوشاک مردانه', 'slug' => 'mens-clothing', 'image_url' => 'images/categories/cat_men.jpg'],
                ['id' => 2, 'name' => 'پوشاک زنانه', 'slug' => 'womens-clothing', 'image_url' => 'images/categories/cat_women.jpg'],
                ['id' => 3, 'name' => 'کالای دیجیتال', 'slug' => 'electronics', 'image_url' => 'images/categories/cat_digital.jpg'],
                ['id' => 4, 'name' => 'خانه و آشپزخانه', 'slug' => 'home-kitchen', 'image_url' => 'images/categories/cat_home.jpg'],
                ['id' => 5, 'name' => 'ورزش و سفر', 'slug' => 'sports-travel', 'image_url' => 'images/categories/cat_sport.jpg'],
                ['id' => 6, 'name' => 'کتاب و هنر', 'slug' => 'books-art', 'image_url' => 'images/categories/cat_book.jpg'],
            ];
        }


        // 3. Discounted Products (Requires getDiscountedProducts in ProductModel)
        // This method should fetch products that have a discount (e.g., sale_price < price or a discount flag)
        $discounted_products = method_exists($this->productModel, 'getDiscountedProducts') 
                               ? $this->productModel->getDiscountedProducts(8) // Fetch up to 8 discounted products
                               : [];
        if (empty($discounted_products)) { 
            for ($i=1; $i <= 8; $i++) { $discounted_products[] = ['id' => 100+$i, 'name' => "محصول تخفیف‌دار نمونه {$i}", 'price' => rand(50000, 200000), 'old_price' => rand(200000, 400000), 'image_url' => null, 'category_name' => 'تخفیف‌ها', 'product_type' => 'simple']; }
        }

        // 4. Latest Products (Requires getLatestProducts in ProductModel)
        // This method should fetch products ordered by creation date descending.
        $latest_products = method_exists($this->productModel, 'getLatestProducts') 
                           ? $this->productModel->getLatestProducts(8) // Fetch up to 8 latest products
                           : [];
         if (empty($latest_products)) { 
            for ($i=1; $i <= 8; $i++) { $latest_products[] = ['id' => 200+$i, 'name' => "محصول جدید نمونه {$i}", 'price' => rand(100000, 500000), 'image_url' => null, 'category_name' => 'جدیدترین‌ها', 'product_type' => 'simple']; }
        }

        // 5. Bestselling Products (Requires getBestsellingProducts in ProductModel)
        // This is more complex and might require tracking sales counts.
        $bestselling_products = method_exists($this->productModel, 'getBestsellingProducts') 
                                ? $this->productModel->getBestsellingProducts(8) // Fetch up to 8 bestselling products
                                : [];
         if (empty($bestselling_products)) { 
            for ($i=1; $i <= 8; $i++) { $bestselling_products[] = ['id' => 300+$i, 'name' => "محصول پرفروش نمونه {$i}", 'price' => rand(80000, 300000), 'image_url' => null, 'category_name' => 'پرفروش‌ها', 'product_type' => 'simple']; }
        }
        
        // For Header Menu Categories (if not handled by a base controller)
        $menu_categories = method_exists($this->categoryModel, 'getParentCategories') 
                           ? $this->categoryModel->getParentCategories(5) // Fetch 5 parent categories for menu
                           : [];


        $data = [
            'pageTitle' => SITE_NAME . ' - صفحه اصلی',
            'main_slides' => $main_slides,
            'parent_categories' => $parent_categories,
            'discounted_products' => $discounted_products,
            'latest_products' => $latest_products,
            'bestselling_products' => $bestselling_products,
            'menu_categories' => $menu_categories // For header dropdown
        ];
        $this->view('pages/index', $data);
    }

    public function about(){
        $data = [
            'pageTitle' => 'درباره ما',
            'menu_categories' => method_exists($this->categoryModel, 'getParentCategories') ? $this->categoryModel->getParentCategories(5) : []
        ];
        $this->view('pages/about', $data);
    }
    
    public function contact(){
        $data = [
            'pageTitle' => 'تماس با ما',
            'menu_categories' => method_exists($this->categoryModel, 'getParentCategories') ? $this->categoryModel->getParentCategories(5) : []
        ];
        $this->view('pages/contact', $data);
    }
     public function faq(){
        $data = [
            'pageTitle' => 'سوالات متداول',
            'menu_categories' => method_exists($this->categoryModel, 'getParentCategories') ? $this->categoryModel->getParentCategories(5) : []
        ];
        $this->view('pages/faq', $data); // Create this view if it doesn't exist
    }
     public function terms(){
        $data = [
            'pageTitle' => 'شرایط و قوانین',
            'menu_categories' => method_exists($this->categoryModel, 'getParentCategories') ? $this->categoryModel->getParentCategories(5) : []
        ];
        $this->view('pages/terms', $data); // Create this view
    }
    public function privacy(){
        $data = [
            'pageTitle' => 'حریم خصوصی',
            'menu_categories' => method_exists($this->categoryModel, 'getParentCategories') ? $this->categoryModel->getParentCategories(5) : []
        ];
        $this->view('pages/privacy', $data); // Create this view
    }
}
?>
