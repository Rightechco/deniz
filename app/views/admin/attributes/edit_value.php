    <?php // ویو: app/views/admin/attributes/edit_value.php ?>
    <h1><?php echo htmlspecialchars($data['pageTitle']); ?></h1>
    <p>ویرایش مقدار "<?php echo htmlspecialchars(isset($data['current_value']) ? $data['current_value'] : $data['value']); ?>" برای ویژگی "<?php echo htmlspecialchars($data['attribute_name']); ?>"</p>

    <?php flash('attribute_value_action_fail'); ?>
    <?php flash('attribute_value_form_error'); ?>

    <form action="<?php echo BASE_URL; ?>admin/editAttributeValue/<?php echo $data['id']; ?>" method="post">
        <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
        <div>
            <label for="value">مقدار ویژگی: <sup>*</sup></label>
            <input type="text" name="value" id="value" value="<?php echo htmlspecialchars(isset($data['value']) ? $data['value'] : ''); ?>" required>
            <span class="error-text"><?php echo isset($data['value_err']) ? $data['value_err'] : ''; ?></span>
        </div>
        <div style="margin-top: 20px;">
            <button type="submit">ذخیره تغییرات</button>
            <a href="<?php echo BASE_URL; ?>admin/attributeValues/<?php echo $data['attribute_id']; ?>" class="button-link button-secondary" style="margin-left: 10px;">انصراف</a>
        </div>
    </form>
    