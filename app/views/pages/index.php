<?php 
// app/views/pages/index.php
// This view expects $data['pageTitle'], $data['main_slides'], $data['parent_categories'], 
// $data['discounted_products'], $data['latest_products'], $data['bestselling_products'],
// and $data['menu_categories'] (for the header) to be passed from PagesController.

// Ensure APPROOT is defined (should be in config.php loaded by public/index.php)
if (!defined('APPROOT')) {
    // Fallback or error, though this should not happen if config is loaded
    // For a view, it's better to let it fail visibly if core constants are missing.
    die("APPROOT constant is not defined. Please check your configuration.");
}
require_once(__DIR__ . '/../layouts/header.php');
?>

<div class="font-vazir space-y-12 md:space-y-16 lg:space-y-20">

    <?php if (isset($data['main_slides']) && !empty($data['main_slides'])): ?>
    <section class="main-slider-section relative h-[50vh] md:h-[65vh] lg:h-[calc(100vh-120px)] max-h-[650px] -mt-px overflow-hidden">
        <div class="swiper main-slider h-full">
            <div class="swiper-wrapper">
                <?php foreach ($data['main_slides'] as $slide): ?>
                    <div class="swiper-slide relative">
                        <img src="<?php echo !empty($slide['image_url']) ? (filter_var($slide['image_url'], FILTER_VALIDATE_URL) ? $slide['image_url'] : BASE_URL . htmlspecialchars($slide['image_url'])) : 'https://placehold.co/1920x700/cccccc/999999?text=Slide+Image&font=vazirmatn'; ?>" 
                             alt="<?php echo htmlspecialchars($slide['alt_text'] ?? 'اسلاید تبلیغاتی'); ?>" 
                             class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/30 to-transparent flex flex-col items-center justify-end text-center p-4 pb-12 md:pb-20">
                            <?php if (!empty($slide['caption_title'])): ?>
                                <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-3 md:mb-4 drop-shadow-lg animate-slide-up" style="opacity:0;">
                                    <?php echo htmlspecialchars($slide['caption_title']); ?>
                                </h2>
                            <?php endif; ?>
                            <?php if (!empty($slide['caption_text'])): ?>
                                <p class="text-sm sm:text-lg text-gray-200 mb-4 md:mb-6 max-w-xl md:max-w-2xl drop-shadow-md animate-slide-up animation-delay-200" style="opacity:0;">
                                    <?php echo htmlspecialchars($slide['caption_text']); ?>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($slide['link_url'])): ?>
                                <a href="<?php echo htmlspecialchars($slide['link_url']); ?>" 
                                   class="bg-primary hover:bg-primary-dark text-white font-semibold py-2.5 px-8 rounded-lg text-sm sm:text-base transition duration-300 ease-in-out transform hover:scale-105 shadow-lg animate-slide-up animation-delay-400" style="opacity:0;">
                                   <?php echo htmlspecialchars($slide['link_text'] ?? 'مشاهده بیشتر'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination !bottom-4 md:!bottom-6"></div>
            <div class="swiper-button-next hidden sm:flex after:!text-2xl !w-8 !h-8 md:!w-10 md:!h-10 bg-white/30 hover:bg-white/50 rounded-full transition-colors"></div>
            <div class="swiper-button-prev hidden sm:flex after:!text-2xl !w-8 !h-8 md:!w-10 md:!h-10 bg-white/30 hover:bg-white/50 rounded-full transition-colors"></div>
        </div>
    </section>
    <?php else: ?>
         <section class="h-[50vh] md:h-[65vh] bg-gray-200 flex items-center justify-center text-gray-500">
            <p>اسلایدری برای نمایش وجود ندارد.</p>
        </section>
    <?php endif; ?>

    <section class="py-10 md:py-16">
        <div class="container mx-auto px-4">
            <h2 class="text-2xl md:text-3xl font-bold text-center text-neutral-darkest mb-8 md:mb-12">دسته‌بندی‌های محبوب</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 md:gap-6">
                <?php if (isset($data['parent_categories']) && !empty($data['parent_categories'])): ?>
                    <?php foreach ($data['parent_categories'] as $category): ?>
                        <a href="<?php echo BASE_URL . 'products/category/' . (isset($category['slug']) && !empty($category['slug']) ? $category['slug'] : $category['id']); ?>" 
                           class="group block text-center p-3 sm:p-4 bg-white rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-1.5 transition-all duration-300 ease-in-out">
                            <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto mb-3 rounded-full overflow-hidden border-2 border-gray-200 group-hover:border-primary transition-all duration-300 p-1 bg-gray-50">
                                <img src="<?php echo !empty($category['image_url']) ? (filter_var($category['image_url'], FILTER_VALIDATE_URL) ? $category['image_url'] : BASE_URL . htmlspecialchars($category['image_url'])) : 'https://placehold.co/150x150/06b6d4/white?text=' . urlencode(mb_substr($category['name'],0,10,'UTF-8')); ?>" 
                                     alt="<?php echo htmlspecialchars($category['name']); ?>" 
                                     class="w-full h-full object-contain transition-transform duration-300 group-hover:scale-105">
                            </div>
                            <h3 class="text-sm sm:text-md font-semibold text-gray-700 group-hover:text-primary transition-colors duration-300 truncate"><?php echo htmlspecialchars($category['name']); ?></h3>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php for ($i = 0; $i < 6; $i++): // Placeholder categories ?>
                        <div class="group block text-center p-4 bg-white rounded-lg shadow-lg animate-pulse">
                            <div class="w-24 h-24 mx-auto mb-3 rounded-full bg-gray-300"></div>
                            <div class="h-4 bg-gray-300 rounded w-3/4 mx-auto mt-2"></div>
                        </div>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php if (isset($data['discounted_products']) && !empty($data['discounted_products'])): ?>
    <section class="py-10 md:py-16 bg-gradient-to-r from-red-50 via-orange-50 to-yellow-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-8 md:mb-12">
                <h2 class="text-2xl md:text-3xl font-bold text-red-600">تخفیف‌های شگفت‌انگیز <i class="fas fa-tags ml-2"></i></h2>
                <a href="<?php echo BASE_URL; ?>products?filter=discounted" class="text-sm text-primary hover:text-primary-dark font-semibold transition-colors inline-flex items-center">مشاهده همه <i class="fas fa-arrow-left mr-1"></i></a>
            </div>
            <div class="swiper product-slider" data-slides-per-view="4.5" data-slides-per-view-md="3.5" data-slides-per-view-sm="1.5">
                <div class="swiper-wrapper pb-4"> <?php // Added pb-4 for shadow visibility ?>
                    <?php foreach ($data['discounted_products'] as $product): ?>
                        <div class="swiper-slide p-1 h-full"> 
                            <?php include __DIR__ . '/../products/_product_card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination-products !bottom-[-8px] mt-8 text-center relative"></div>
                <div class="swiper-button-next-products !text-primary hidden sm:flex"></div>
                <div class="swiper-button-prev-products !text-primary hidden sm:flex"></div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="py-8 md:py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                <a href="<?php echo $data['banner1_link'] ?? '#'; ?>" class="block rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transform hover:scale-[1.02] transition-all duration-300 ease-in-out">
                    <img src="<?php echo isset($data['banner1_image_url']) ? (filter_var($data['banner1_image_url'], FILTER_VALIDATE_URL) ? $data['banner1_image_url'] : BASE_URL . htmlspecialchars($data['banner1_image_url'])) : 'https://placehold.co/600x300/f59e0b/333333?text=بنر+تبلیغاتی+۱&font=vazirmatn'; ?>" alt="<?php echo $data['banner1_alt'] ?? 'بنر ۱'; ?>" class="w-full h-auto object-cover">
                </a>
                <a href="<?php echo $data['banner2_link'] ?? '#'; ?>" class="block rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transform hover:scale-[1.02] transition-all duration-300 ease-in-out">
                    <img src="<?php echo isset($data['banner2_image_url']) ? (filter_var($data['banner2_image_url'], FILTER_VALIDATE_URL) ? $data['banner2_image_url'] : BASE_URL . htmlspecialchars($data['banner2_image_url'])) : 'https://placehold.co/600x300/14b8a6/ffffff?text=بنر+تبلیغاتی+۲&font=vazirmatn'; ?>" alt="<?php echo $data['banner2_alt'] ?? 'بنر ۲'; ?>" class="w-full h-auto object-cover">
                </a>
            </div>
        </div>
    </section>

    <?php if (isset($data['latest_products']) && !empty($data['latest_products'])): ?>
    <section class="py-10 md:py-16 bg-neutral-light">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-8 md:mb-12">
                <h2 class="text-2xl md:text-3xl font-bold text-neutral-darkest">جدیدترین محصولات <i class="fas fa-star ml-2 text-yellow-400"></i></h2>
                 <a href="<?php echo BASE_URL; ?>products?sort=latest" class="text-sm text-primary hover:text-primary-dark font-semibold transition-colors inline-flex items-center">مشاهده همه <i class="fas fa-arrow-left mr-1"></i></a>
            </div>
            <div class="swiper product-slider" data-slides-per-view="4.5" data-slides-per-view-md="3.5" data-slides-per-view-sm="1.5">
                <div class="swiper-wrapper pb-4">
                    <?php foreach ($data['latest_products'] as $product): ?>
                         <div class="swiper-slide p-1 h-full">
                             <?php include __DIR__ . '/../products/_product_card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination-products !bottom-[-8px] mt-8 text-center relative"></div>
                <div class="swiper-button-next-products !text-primary hidden sm:flex"></div>
                <div class="swiper-button-prev-products !text-primary hidden sm:flex"></div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="py-8 md:py-12">
        <div class="container mx-auto px-4">
            <a href="<?php echo $data['full_banner_link'] ?? '#'; ?>" class="block rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-shadow duration-300">
                <img src="<?php echo isset($data['full_banner_image_url']) ? (filter_var($data['full_banner_image_url'], FILTER_VALIDATE_URL) ? $data['full_banner_image_url'] : BASE_URL . htmlspecialchars($data['full_banner_image_url'])) : 'https://placehold.co/1200x250/3b82f6/ffffff?text=پیشنهاد+ویژه+و+محدود&font=vazirmatn'; ?>" alt="<?php echo $data['full_banner_alt'] ?? 'بنر تمام عرض'; ?>" class="w-full h-auto object-cover">
            </a>
        </div>
    </section>

    <?php if (isset($data['bestselling_products']) && !empty($data['bestselling_products'])): ?>
    <section class="py-10 md:py-16">
        <div class="container mx-auto px-4">
             <div class="flex justify-between items-center mb-8 md:mb-12">
                <h2 class="text-2xl md:text-3xl font-bold text-neutral-darkest">پرفروش‌ترین‌ها <i class="fas fa-fire ml-2 text-red-500"></i></h2>
                <a href="<?php echo BASE_URL; ?>products?sort=bestselling" class="text-sm text-primary hover:text-primary-dark font-semibold transition-colors inline-flex items-center">مشاهده همه <i class="fas fa-arrow-left mr-1"></i></a>
            </div>
            <div class="swiper product-slider" data-slides-per-view="4.5" data-slides-per-view-md="3.5" data-slides-per-view-sm="1.5">
                <div class="swiper-wrapper pb-4">
                    <?php foreach ($data['bestselling_products'] as $product): ?>
                         <div class="swiper-slide p-1 h-full">
                                           <?php include __DIR__ . '/../products/_product_card.php'; ?>                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination-products !bottom-[-8px] mt-8 text-center relative"></div>
                <div class="swiper-button-next-products !text-primary hidden sm:flex"></div>
                <div class="swiper-button-prev-products !text-primary hidden sm:flex"></div>
            </div>
        </div>
    </section>
    <?php endif; ?>
</div>

<?php require_once(__DIR__ . '/../layouts/footer.php'); ?>
