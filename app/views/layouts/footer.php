<?php // app/views/layouts/footer.php ?>
    </main> <?php // بستن تگ main از header.php ?>

    <footer class="bg-neutral-darkest text-gray-300 pt-12 sm:pt-16 pb-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 mb-10">
                <div>
                    <h3 class="text-xl font-bold text-white mb-4"><?php echo defined('SITE_NAME') ? SITE_NAME : 'فروشگاه شما'; ?></h3>
                    <p class="text-sm text-gray-400 leading-relaxed mb-4">
                        لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ و با استفاده از طراحان گرافیک است. چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است و برای شرایط فعلی تکنولوژی مورد نیاز.
                    </p>
                    <div class="flex space-x-4 space-x-reverse">
                        <a href="#" aria-label="اینستاگرام" class="text-gray-400 hover:text-primary-light transition-colors duration-200"><i class="fab fa-instagram text-xl"></i></a>
                        <a href="#" aria-label="تلگرام" class="text-gray-400 hover:text-primary-light transition-colors duration-200"><i class="fab fa-telegram-plane text-xl"></i></a>
                        <a href="#" aria-label="واتساپ" class="text-gray-400 hover:text-primary-light transition-colors duration-200"><i class="fab fa-whatsapp text-xl"></i></a>
                        <a href="#" aria-label="توییتر" class="text-gray-400 hover:text-primary-light transition-colors duration-200"><i class="fab fa-twitter text-xl"></i></a>
                    </div>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-white mb-4">دسترسی سریع</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="<?php echo BASE_URL; ?>pages/about" class="text-gray-400 hover:text-primary-light transition-colors">درباره ما</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/contact" class="text-gray-400 hover:text-primary-light transition-colors">تماس با ما</a></li>
                        <li><a href="<?php echo BASE_URL; ?>products" class="text-gray-400 hover:text-primary-light transition-colors">فروشگاه</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/faq" class="text-gray-400 hover:text-primary-light transition-colors">سوالات متداول</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-white mb-4">خدمات مشتریان</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="<?php echo BASE_URL; ?>customer/dashboard" class="text-gray-400 hover:text-primary-light transition-colors">پیگیری سفارش</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/terms" class="text-gray-400 hover:text-primary-light transition-colors">شرایط و قوانین</a></li>
                        <li><a href="<?php echo BASE_URL; ?>pages/privacy" class="text-gray-400 hover:text-primary-light transition-colors">حریم خصوصی</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-white mb-4">نمادهای اعتماد</h4>
                    <div class="flex items-center space-x-3 space-x-reverse">
                         <a href="#" target="_blank" rel="noopener noreferrer" class="block">
                            <img src="https://placehold.co/90x90/4b5563/cbd5e1?text=Enamad" alt="نماد اعتماد الکترونیکی" class="h-20 w-auto rounded-md hover:opacity-80 transition-opacity">
                        </a>
                        <a href="#" target="_blank" rel="noopener noreferrer" class="block">
                            <img src="https://placehold.co/90x90/4b5563/cbd5e1?text=Samandehi" alt="نماد ساماندهی" class="h-20 w-auto rounded-md hover:opacity-80 transition-opacity">
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-sm text-gray-500">
                <p>&copy; <?php echo date('Y'); ?> ، تمامی حقوق برای <?php echo defined('SITE_NAME') ? SITE_NAME : 'فروشگاه شما'; ?> محفوظ است.</p>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>js/jquery.min.js"></script> 
    <script src="<?php echo BASE_URL; ?>js/kamadatepicker.min.js"></script>
    <script src="<?php echo BASE_URL; ?>js/main.js?v=<?php echo time(); ?>"></script>
    <?php 
        if (isset($data['page_js']) && is_array($data['page_js'])) {
            foreach ($data['page_js'] as $js_file) {
                echo '<script src="' . BASE_URL . 'js/' . htmlspecialchars($js_file) . '?v=' . time() . '"></script>' . "\n";
            }
        }
    ?>
</body>
</html>
