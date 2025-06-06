    <?php // ویو: app/views/admin/attributes/add.php ?>
    <h1><?php echo htmlspecialchars($data['pageTitle']); ?></h1>

    <?php flash('attribute_action_fail'); ?>
    <?php flash('attribute_form_error'); ?>

    <form action="<?php echo BASE_URL; ?>admin/addAttribute" method="post">
        <div>
            <label for="name">نام ویژگی: <sup>*</sup></label>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars(isset($data['name']) ? $data['name'] : ''); ?>" required>
            <span class="error-text"><?php echo isset($data['name_err']) ? $data['name_err'] : ''; ?></span>
        </div>
        <div style="margin-top: 20px;">
            <button type="submit">افزودن ویژگی</button>
            <a href="<?php echo BASE_URL; ?>admin/attributes" class="button-link button-secondary" style="margin-left: 10px;">انصراف</a>
        </div>
    </form>
    