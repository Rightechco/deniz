    <?php // ویو: app/views/admin/categories/add.php ?>

    <h1><?php echo htmlspecialchars($data['pageTitle']); ?></h1>

    <?php flash('category_action_fail'); ?>
    <?php flash('category_form_error'); ?>

    <form action="<?php echo BASE_URL; ?>admin/addCategory" method="post">
        <div>
            <label for="name">نام دسته‌بندی: <sup>*</sup></label>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars(isset($data['name']) ? $data['name'] : ''); ?>" required>
            <span class="error-text"><?php echo isset($data['name_err']) ? $data['name_err'] : ''; ?></span>
        </div>

        <div>
            <label for="description">توضیحات دسته‌بندی:</label>
            <textarea name="description" id="description" rows="3"><?php echo htmlspecialchars(isset($data['description']) ? $data['description'] : ''); ?></textarea>
        </div>

        <div>
            <label for="parent_id">دسته‌بندی والد (اختیاری):</label>
            <select name="parent_id" id="parent_id">
                <option value="">-- بدون والد (دسته‌بندی اصلی) --</option>
                <?php if (!empty($data['all_categories'])): ?>
                    <?php foreach($data['all_categories'] as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo (isset($data['parent_id']) && $data['parent_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" name="add_category_submit">افزودن دسته‌بندی</button>
            <a href="<?php echo BASE_URL; ?>admin/categories" class="button-link button-secondary" style="margin-right: 10px;">انصراف</a>
        </div>
    </form>
    