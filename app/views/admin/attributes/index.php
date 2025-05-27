    <?php // ویو: app/views/admin/attributes/index.php ?>
    <h1><?php echo htmlspecialchars($data['pageTitle']); ?></h1>

    <?php flash('attribute_action_success'); ?>
    <?php flash('attribute_action_fail'); ?>

    <p style="margin-bottom: 20px;">
        <a href="<?php echo BASE_URL; ?>admin/addAttribute" class="button-link" style="background-color: #28a745;">افزودن ویژگی جدید</a>
        <a href="<?php echo BASE_URL; ?>admin/products" class="button-link button-secondary" style="margin-left:10px;">بازگشت به مدیریت محصولات</a>
    </p>

    <?php if (!empty($data['attributes'])): ?>
        <table style="width: 100%;">
            <thead>
                <tr>
                    <th>شناسه</th>
                    <th>نام ویژگی</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['attributes'] as $attribute): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($attribute['id']); ?></td>
                        <td><?php echo htmlspecialchars($attribute['name']); ?></td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>admin/attributeValues/<?php echo $attribute['id']; ?>" class="button-link" style="background-color: #17a2b8;">مدیریت مقادیر</a>
                            <a href="<?php echo BASE_URL; ?>admin/editAttribute/<?php echo $attribute['id']; ?>" class="button-link button-warning" style="margin-left: 5px;">ویرایش</a>
                            <form action="<?php echo BASE_URL; ?>admin/deleteAttribute/<?php echo $attribute['id']; ?>" method="post" style="display: inline;" onsubmit="return confirm('آیا از حذف این ویژگی و تمام مقادیر مرتبط با آن مطمئن هستید؟');">
                                <button type="submit" class="button-danger">حذف</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>هیچ ویژگی‌ای تعریف نشده است.</p>
    <?php endif; ?>
    