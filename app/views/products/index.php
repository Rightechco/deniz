<?php // ویو: app/views/products/index.php ?>

<div style="display: flex; flex-wrap: wrap; gap: 20px;">
    <aside style="width: 220px; min-width: 200px; border-right: 1px solid #eee; padding-right: 20px; background-color: #f9f9f9; border-radius: 5px; padding:15px; align-self: flex-start;">
        <h3 style="margin-top:0; border-bottom: 1px solid #ddd; padding-bottom:10px;">دسته‌بندی‌ها</h3>
        <ul style="list-style: none; padding: 0;">
            <li style="margin-bottom: 8px;">
                <a href="<?php echo BASE_URL; ?>products/index" 
                   style="text-decoration: none; color: <?php echo (!isset($data['current_category_id']) || $data['current_category_id'] === null) ? '#007bff; font-weight:bold;' : '#333;'; ?> display: block; padding: 5px 0;">
                    همه محصولات
                </a>
            </li>
            <?php if (!empty($data['categories'])): ?>
                <?php foreach($data['categories'] as $category): ?>
                    <li style="margin-bottom: 8px;">
                        <a href="<?php echo BASE_URL; ?>products/category/<?php echo $category['id']; ?>" 
                           style="text-decoration: none; color: <?php echo (isset($data['current_category_id']) && $data['current_category_id'] == $category['id']) ? '#007bff; font-weight:bold;' : '#333;'; ?> display: block; padding: 5px 0;">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </a>
                        </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </aside>

    <section style="flex-grow: 1;">
        <h1><?php echo htmlspecialchars(isset($data['current_category_name']) ? $data['current_category_name'] : (isset($data['pageTitle']) ? $data['pageTitle'] : 'محصولات')); ?></h1>

        <?php flash('cart_action_success'); ?>
        <?php flash('cart_action_fail'); ?>
        <?php flash('error_message'); // برای پیام‌های خطا از کنترلر ?>


        <?php if (!empty($data['products'])): ?>
            <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 20px;">
                <?php foreach ($data['products'] as $product): ?>
                    <div class="product-card" style="border: 1px solid #ddd; padding: 15px; text-align: center; background-color: #fff; border-radius: 5px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <a href="<?php echo BASE_URL; ?>products/show/<?php echo $product['id']; ?>" style="text-decoration:none; color:inherit;">
                                <?php
                                $image_path = !empty($product['image_url']) ? BASE_URL . htmlspecialchars($product['image_url']) : BASE_URL . 'images/placeholder.png';
                                $alt_text = !empty($product['image_url']) ? htmlspecialchars($product['name']) : 'تصویر موجود نیست';
                                ?>
                                <img src="<?php echo $image_path; ?>" alt="<?php echo $alt_text; ?>" style="max-width: 100%; height: 180px; object-fit: contain; margin-bottom: 10px; border-radius: 4px;">
                                <h3 style="font-size: 1.1em; min-height: 40px; margin: 10px 0;"><?php echo htmlspecialchars($product['name']); ?></h3>
                            </a>
                            <p style="color: #555; font-size: 0.9em; margin-bottom: 5px;">
                                دسته: <?php echo htmlspecialchars(isset($product['category_name']) ? $product['category_name'] : '<em>بدون دسته</em>'); ?>
                            </p>
                            <p style="font-size: 1.2em; font-weight: bold; color: #d9534f; margin: 10px 0;">
                                <?php 
                                if (isset($product['price']) && $product['price'] !== null) {
                                    echo htmlspecialchars(number_format((float)$product['price'])) . ' تومان';
                                } else {
                                    echo ($product['product_type'] == 'variable') ? '<em>(قیمت در تنوع‌ها)</em>' : '---';
                                }
                                ?>
                            </p>
                            <p style="font-size: 0.9em; color: #777; margin-bottom:10px;">
                                <?php 
                                if ($product['product_type'] == 'variable') {
                                    // برای محصول متغیر، می‌توانیم مجموع موجودی تنوع‌ها را نمایش دهیم یا یک پیام کلی
                                    // $total_variation_stock = 0;
                                    // if(isset($product['variations_details']) && is_array($product['variations_details'])){
                                    //     foreach($product['variations_details'] as $var_item) $total_variation_stock += (int)$var_item['stock_quantity'];
                                    // }
                                    // echo 'موجودی کل تنوع‌ها: ' . $total_variation_stock;
                                    echo '<em>(موجودی در تنوع‌ها)</em>';
                                } else { // محصول ساده
                                    echo 'موجودی: ' . htmlspecialchars(isset($product['stock_quantity']) ? $product['stock_quantity'] : '0');
                                }
                                ?>
                            </p>
                        </div>

                        <div style="margin-top: auto;"> <?php if ($product['product_type'] == 'simple' && isset($product['stock_quantity']) && $product['stock_quantity'] > 0): ?>
                                <form action="<?php echo BASE_URL; ?>cart/add" method="post" style="margin-top: 10px;">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <div style="display:flex; justify-content:center; align-items:center; margin-bottom:10px;">
                                        <label for="quantity_<?php echo $product['id']; ?>" style="margin-left:5px; font-size:0.9em;">تعداد:</label>
                                        <input type="number" name="quantity" id="quantity_<?php echo $product['id']; ?>" value="1" min="1" max="<?php echo htmlspecialchars($product['stock_quantity']); ?>" style="width: 50px; padding: 5px; border: 1px solid #ccc; border-radius:3px;">
                                    </div>
                                    <button type="submit" class="button-link" style="background-color: #28a745; width:100%;">افزودن به سبد</button>
                                </form>
                            <?php elseif ($product['product_type'] == 'simple' && (!isset($product['stock_quantity']) || $product['stock_quantity'] <= 0)): ?>
                                <p style="color: red; margin-top: 10px; font-weight:bold;">اتمام موجودی</p>
                            <?php endif; ?>
                             <a href="<?php echo BASE_URL; ?>products/show/<?php echo $product['id']; ?>" class="button-link" style="background-color: #007bff; width:calc(100% - 22px); margin-top: 5px; display:block; padding:10px 0;">
                                <?php echo ($product['product_type'] == 'variable') ? 'انتخاب گزینه‌ها' : 'مشاهده جزئیات'; ?>
                             </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>در این دسته‌بندی (یا در کل فروشگاه) محصولی برای نمایش وجود ندارد.</p>
        <?php endif; ?>
    </section>
</div>
