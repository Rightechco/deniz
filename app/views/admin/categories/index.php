    <?php // ویو: app/views/admin/categories/index.php ?>

    <h1><?php echo htmlspecialchars($data['pageTitle']); ?></h1>

    <?php flash('category_action_success'); ?>
    <?php flash('category_action_fail'); ?>
    <?php flash('error_message'); ?>


    <p style="margin-bottom: 20px;">
        <a href="<?php echo BASE_URL; ?>admin/addCategory" class="button-link" style="background-color: #28a745;">افزودن دسته‌بندی جدید</a>
        <a href="<?php echo BASE_URL; ?>admin/products" class="button-link" style="background-color: #17a2b8; margin-right:10px;">بازگشت به مدیریت محصولات</a>
    </p>

    <?php if (!empty($data['categories'])): ?>
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th style="padding: 10px; border: 1px solid #ddd;">شناسه</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">نام دسته‌بندی</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">توضیحات</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: center;">عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['categories'] as $category): ?>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($category['id']); ?></td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($category['name']); ?></td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><?php echo !empty($category['description']) ? nl2br(htmlspecialchars($category['description'])) : '<em>بدون توضیحات</em>'; ?></td>
                        <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">
                            <a href="<?php echo BASE_URL; ?>admin/editCategory/<?php echo $category['id']; ?>" class="button-link button-warning" style="margin-right: 5px;">ویرایش</a>
                            <form action="<?php echo BASE_URL; ?>admin/deleteCategory/<?php echo $category['id']; ?>" method="post" style="display: inline;" onsubmit="return confirm('آیا از حذف این دسته‌بندی مطمئن هستید؟ تمام زیرشاخه‌های آن نیز والد خود را از دست خواهند داد و محصولات مرتبط با این دسته، بدون دسته خواهند شد (اگر این منطق پیاده‌سازی شده باشد).');">
                                <button type="submit" class="button-danger">حذف</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>هیچ دسته‌بندی برای نمایش وجود ندارد. برای شروع، یک <a href="<?php echo BASE_URL; ?>admin/addCategory">دسته‌بندی جدید اضافه کنید</a>.</p>
    <?php endif; ?>
    