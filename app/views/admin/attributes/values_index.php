    <?php // ویو: app/views/admin/attributes/values_index.php ?>
    <h1><?php echo htmlspecialchars($data['pageTitle']); ?></h1>

    <?php flash('attribute_value_action_success'); ?>
    <?php flash('attribute_value_action_fail'); ?>
    <?php flash('attribute_value_form_error'); ?>

    <div style="margin-bottom: 20px; padding: 15px; border: 1px solid #eee; background-color: #f9f9f9;">
        <h3>افزودن مقدار جدید برای ویژگی "<?php echo htmlspecialchars($data['attribute']['name']); ?>"</h3>
        <form action="<?php echo BASE_URL; ?>admin/addAttributeValue/<?php echo $data['attribute']['id']; ?>" method="post">
            <div>
                <label for="value_input">مقدار جدید: <sup>*</sup></label>
                <input type="text" name="value" id="value_input" value="<?php echo htmlspecialchars(isset($data['value_input']) ? $data['value_input'] : ''); ?>" required>
                <span class="error-text"><?php echo isset($data['value_err']) ? $data['value_err'] : ''; ?></span>
            </div>
            <div style="margin-top: 10px;">
                <button type="submit">افزودن مقدار</button>
            </div>
        </form>
    </div>

    <h3>مقادیر موجود برای "<?php echo htmlspecialchars($data['attribute']['name']); ?>"</h3>
    <?php if (!empty($data['values'])): ?>
        <table style="width: 100%;">
            <thead>
                <tr>
                    <th>شناسه مقدار</th>
                    <th>مقدار</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['values'] as $value_item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($value_item['id']); ?></td>
                        <td><?php echo htmlspecialchars($value_item['value']); ?></td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>admin/editAttributeValue/<?php echo $value_item['id']; ?>" class="button-link button-warning" style="margin-left: 5px;">ویرایش</a>
                            <form action="<?php echo BASE_URL; ?>admin/deleteAttributeValue/<?php echo $value_item['id']; ?>" method="post" style="display: inline;" onsubmit="return confirm('آیا از حذف این مقدار ویژگی مطمئن هستید؟');">
                                <button type="submit" class="button-danger">حذف</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>هنوز هیچ مقداری برای این ویژگی تعریف نشده است.</p>
    <?php endif; ?>

    <p style="margin-top: 30px;">
        <a href="<?php echo BASE_URL; ?>admin/attributes" class="button-link button-secondary">بازگشت به لیست ویژگی‌ها</a>
    </p>
    