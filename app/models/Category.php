<?php
// app/models/Category.php

class Category {
    private $db;

    public function __construct() {
        if (class_exists('Database')) {
            $this->db = new Database();
        } else {
            die("Fatal Error: Database class not found in Category model.");
        }
    }

    /**
     * دریافت تمام دسته‌بندی‌ها از پایگاه داده
     * @return array آرایه‌ای از دسته‌بندی‌ها یا آرایه خالی
     */
    public function getAllCategories() {
        $this->db->query("SELECT * FROM categories ORDER BY name ASC");
        $results = $this->db->resultSet();
        return $results ? $results : [];
    }

    /**
     * دریافت یک دسته‌بندی خاص بر اساس شناسه (ID) آن
     * @param int $id شناسه دسته‌بندی
     * @return mixed آرایه اطلاعات دسته‌بندی در صورت یافتن، در غیر این صورت false
     */
    public function getCategoryById($id) {
        $this->db->query("SELECT * FROM categories WHERE id = :id");
        $this->db->bind(':id', $id);
        $row = $this->db->single();
        return ($this->db->rowCount() > 0) ? $row : false;
    }

    /**
     * افزودن یک دسته‌بندی جدید به پایگاه داده
     * @param array $data داده‌های دسته‌بندی شامل name, description, parent_id
     * @return bool true در صورت موفقیت، false در صورت شکست
     */
    public function addCategory($data) {
        $this->db->query('INSERT INTO categories (name, description, parent_id)
                          VALUES (:name, :description, :parent_id)');
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':parent_id', $data['parent_id'] == '' ? null : $data['parent_id']); // اگر parent_id خالی بود، null ذخیره کن

        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * به‌روزرسانی اطلاعات یک دسته‌بندی موجود
     * @param array $data داده‌های دسته‌بندی شامل id, name, description, parent_id
     * @return bool true در صورت موفقیت، false در صورت شکست
     */
    public function updateCategory($data) {
        $this->db->query('UPDATE categories SET
                            name = :name,
                            description = :description,
                            parent_id = :parent_id
                          WHERE id = :id');
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':parent_id', $data['parent_id'] == '' ? null : $data['parent_id']);

        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * حذف یک دسته‌بندی از پایگاه داده بر اساس شناسه آن
     * @param int $id شناسه دسته‌بندی برای حذف
     * @return bool true در صورت موفقیت، false در صورت شکست
     */
    public function deleteCategory($id) {
        // قبل از حذف، باید بررسی کنیم که آیا محصولی به این دسته‌بندی اختصاص داده شده یا خیر
        // یا اینکه parent_id دسته‌بندی‌های دیگر به این دسته اشاره می‌کند یا خیر.
        // فعلاً یک حذف ساده انجام می‌دهیم. در آینده این بخش را کامل‌تر می‌کنیم.
        // همچنین، رفتار ON DELETE SET NULL برای parent_id در تعریف جدول،
        // باعث می‌شود اگر دسته‌های فرزندی وجود داشته باشند، parent_id آنها NULL شود.

        $this->db->query('DELETE FROM categories WHERE id = :id');
        $this->db->bind(':id', $id);

        if ($this->db->execute()) {
            return $this->db->rowCount() > 0;
        } else {
            return false;
        }
    }
}
?>
