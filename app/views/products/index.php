<?php 
// app/views/products/index.php
// این ویو از $data['pageTitle'], $data['products'], $data['categories'], 
// $data['current_category_id'], $data['current_category_name'] استفاده می‌کند.

require_once(__DIR__ . '/../layouts/header.php');
?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-8 text-center">
        <h1 class="text-3xl md:text-4xl font-bold text-neutral-darkest">
            <?php echo htmlspecialchars(isset($data['current_category_name']) && $data['current_category_id'] ? 'محصولات دسته: ' . $data['current_category_name'] : (isset($data['pageTitle']) ? $data['pageTitle'] : 'فروشگاه محصولات')); ?>
        </h1>
        <?php if (isset($data['current_category_id']) && $data['current_category_id']): ?>
            <p class="text-neutral-medium mt-2">مشاهده محصولات منتخب در دسته‌بندی <?php echo htmlspecialchars($data['current_category_name']); ?></p>
        <?php else: ?>
            <p class="text-neutral-medium mt-2">جدیدترین و بهترین محصولات را اینجا پیدا کنید.</p>
        <?php endif; ?>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
        <aside class="lg:w-1/4 xl:w-1/5 space-y-6">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h3 class="text-xl font-semibold text-neutral-dark mb-4 border-b pb-3">دسته‌بندی‌ها</h3>
                <ul class="space-y-2 text-sm">
                    <li>
                        <a href="<?php echo BASE_URL; ?>products" 
                           class="block px-3 py-2 rounded-md hover:bg-primary-light hover:text-primary-dark transition-colors <?php echo !isset($data['current_category_id']) ? 'bg-primary-light text-primary-dark font-semibold' : 'text-gray-600'; ?>">
                           همه محصولات
                        </a>
                    </li>
                    <?php if (isset($data['categories']) && !empty($data['categories'])): ?>
                        <?php foreach ($data['categories'] as $category): ?>
                            <li>
                                <a href="<?php echo BASE_URL . 'products/category/' . ($category['slug'] ?? $category['id']); ?>" 
                                   class="block px-3 py-2 rounded-md hover:bg-primary-light hover:text-primary-dark transition-colors <?php echo (isset($data['current_category_id']) && $data['current_category_id'] == $category['id']) ? 'bg-primary-light text-primary-dark font-semibold' : 'text-gray-600'; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                    <?php /* <span class="text-xs text-gray-400">(<?php echo $category['product_count'] ?? 0; ?>)</span> */ ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h3 class="text-xl font-semibold text-neutral-dark mb-4 border-b pb-3">فیلترهای بیشتر</h3>
                <p class="text-sm text-gray-500">امکان افزودن فیلتر بر اساس قیمت، برند و سایر ویژگی‌ها در اینجا وجود دارد.</p>
                </div>
        </aside>

        <section class="lg:w-3/4 xl:w-4/5">
            <?php if (isset($data['products']) && !empty($data['products'])): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <?php foreach ($data['products'] as $product): ?>
                        <div class="product-card-wrapper">
                            <?php include __DIR__ . '/../products/_product_card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-12 flex justify-center">
                    <nav aria-label="Pagination">
                        <ul class="inline-flex items-center -space-x-px">
                            <li>
                                <a href="#" class="py-2 px-3 ml-0 leading-tight text-gray-500 bg-white rounded-r-lg border border-gray-300 hover:bg-gray-100 hover:text-gray-700">قبلی</a>
                            </li>
                            <li>
                                <a href="#" aria-current="page" class="z-10 py-2 px-3 leading-tight text-primary bg-primary-light border border-primary hover:bg-blue-100 hover:text-blue-700">1</a>
                            </li>
                            <li>
                                <a href="#" class="py-2 px-3 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700">2</a>
                            </li>
                            <li>
                                <a href="#" class="py-2 px-3 leading-tight text-gray-500 bg-white rounded-l-lg border border-gray-300 hover:bg-gray-100 hover:text-gray-700">بعدی</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php else: ?>
                <div class="bg-white p-8 rounded-lg shadow-md text-center">
                    <i class="fas fa-box-open fa-3x text-gray-400 mb-4"></i>
                    <p class="text-xl text-gray-600">متاسفانه محصولی مطابق با جستجوی شما یافت نشد.</p>
                    <a href="<?php echo BASE_URL; ?>products" class="mt-4 inline-block bg-primary text-white font-semibold py-2 px-4 rounded-md hover:bg-primary-dark transition-colors">
                        بازگشت به همه محصولات
                    </a>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php require_once(__DIR__ . '/../layouts/footer.php'); ?>