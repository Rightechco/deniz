<?php
// app/models/Product.php

class Product {
    private $db;
    private $mainImageUploadDir = 'uploads/products/'; // For main product image
    private $galleryImageUploadDir = 'uploads/products/gallery/'; // For gallery images

    public function __construct() { 
        if (class_exists('Database')) {
            $this->db = new Database();
        } else {
            // This case should ideally not happen if autoloader is working correctly.
            error_log("ProductModel FATAL ERROR: Database class not found.");
            die("Fatal Error: Database class not found in Product model.");
        }
        // Ensure gallery upload directory exists
        if (defined('FCPATH')) {
            if (!is_dir(FCPATH . $this->galleryImageUploadDir) && !mkdir(FCPATH . $this->galleryImageUploadDir, 0775, true)) {
                 error_log("ProductModel: Failed to create gallery upload directory: " . FCPATH . $this->galleryImageUploadDir);
            }
        } else {
            error_log("ProductModel: FCPATH is not defined. Cannot create gallery upload directory.");
        }
    }

    // ... (متدهای getAllProducts, getProductById, getProductsByCategoryId, getProductsByVendorId, decreaseStock از فایل Product (2).php شما بدون تغییر عمده باقی می‌مانند) ...
    public function getAllProducts() { 
        $this->db->query("SELECT p.*, 
                                 c.name as category_name,
                                 u.username as vendor_username, 
                                 CONCAT(u.first_name, ' ', u.last_name) as vendor_full_name
                          FROM products p
                          LEFT JOIN categories c ON p.category_id = c.id
                          LEFT JOIN users u ON p.vendor_id = u.id
                          ORDER BY p.created_at DESC");
        return $this->db->resultSet() ?: [];
    }

    public function getProductById($id) { 
        $this->db->query("SELECT p.*, 
                                 c.name as category_name,
                                 u.username as vendor_username,
                                 CONCAT(u.first_name, ' ', u.last_name) as vendor_full_name
                          FROM products p
                          LEFT JOIN categories c ON p.category_id = c.id
                          LEFT JOIN users u ON p.vendor_id = u.id
                          WHERE p.id = :id");
        $this->db->bind(':id', (int)$id);
        $row = $this->db->single();
        return ($this->db->rowCount() > 0) ? $row : false;
    }
     public function getProductsByVendorId($vendor_id) {
        $this->db->query("SELECT p.*, c.name as category_name 
                          FROM products p
                          LEFT JOIN categories c ON p.category_id = c.id
                          WHERE p.vendor_id = :vendor_id
                          ORDER BY p.created_at DESC");
        $this->db->bind(':vendor_id', (int)$vendor_id);
        $results = $this->db->resultSet();
        return $results ? $results : [];
    }

    public function decreaseStock($product_id, $quantity_to_decrease) {
        // ... (کد این متد از Product (2).php شما) ...
        $product_id = (int)$product_id;
        $quantity_to_decrease = (int)$quantity_to_decrease;
        $this->db->query("SELECT stock_quantity FROM products WHERE id = :id");
        $this->db->bind(':id', $product_id);
        $product_stock_data = $this->db->single();
        if ($product_stock_data && (int)$product_stock_data['stock_quantity'] >= $quantity_to_decrease) {
            $this->db->query("UPDATE products SET stock_quantity = stock_quantity - :quantity WHERE id = :id AND stock_quantity >= :quantity_check");
            $this->db->bindMultiple([':quantity' => $quantity_to_decrease, ':id' => $product_id, ':quantity_check' => $quantity_to_decrease]);
            return $this->db->execute() && $this->db->rowCount() > 0;
        }
        return false;
    }


    /**
     * Handles image upload.
     * @param array $file $_FILES['input_name']
     * @param string $target_sub_dir Subdirectory within 'uploads/' (e.g., 'products', 'products/gallery')
     * @param string|null $old_image_filename_to_delete Filename of the old image to be deleted from $target_sub_dir.
     * @return string|false The new filename (without base path) on success, false on failure.
     */
    public function handleImageUpload($file, $target_sub_dir, $old_image_filename_to_delete = null) {
        if (!defined('FCPATH')) {
            error_log("ProductModel::handleImageUpload - FCPATH is not defined.");
            return false;
        }

        $upload_base_path = FCPATH . 'uploads/'; // Base for all uploads
        $target_directory_absolute = rtrim($upload_base_path, '/') . '/' . trim($target_sub_dir, '/') . '/';

        if (!is_dir($target_directory_absolute)) {
            if (!mkdir($target_directory_absolute, 0775, true)) {
                error_log("handleImageUpload: Failed to create upload directory: " . $target_directory_absolute);
                return false;
            }
        }

        if (!isset($file['tmp_name']) || empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            error_log("handleImageUpload: No file uploaded or upload error. Error code: " . ($file['error'] ?? 'Unknown'));
            return false;
        }

        $imageFileType = strtolower(pathinfo(basename($file["name"]), PATHINFO_EXTENSION));
        $newFileName = uniqid(basename($target_sub_dir, '/') . '_', true) . '_' . rand(1000,9999) . '.' . $imageFileType;
        $target_file_absolute = $target_directory_absolute . $newFileName;

        $check = getimagesize($file["tmp_name"]);
        if ($check === false) {
            error_log("handleImageUpload: File is not an image: " . $file["name"]);
            return false;
        }

        if ($file["size"] > 5 * 1024 * 1024) { // 5MB
            error_log("handleImageUpload: File is too large: " . $file["name"]);
            return false;
        }

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($imageFileType, $allowed_types)) {
            error_log("handleImageUpload: Invalid file type: " . $imageFileType);
            return false;
        }

        if (move_uploaded_file($file["tmp_name"], $target_file_absolute)) {
            // Delete old image if a new one is uploaded successfully and old path is provided
            if ($old_image_filename_to_delete && file_exists($target_directory_absolute . $old_image_filename_to_delete)) {
                if (!@unlink($target_directory_absolute . $old_image_filename_to_delete)) {
                     error_log("handleImageUpload: Could not delete old image: " . $target_directory_absolute . $old_image_filename_to_delete);
                }
            }
            return $newFileName; // Return only the filename, path is relative to $target_sub_dir
        } else {
            error_log("handleImageUpload: Failed to move uploaded file from {$file['tmp_name']} to {$target_file_absolute}. Check permissions and path.");
            return false;
        }
    }


    public function addProduct($data) {
        // Main image handling
        $image_filename = null; // Filename only, not the full path
        if (isset($data['image_file']) && $data['image_file']['error'] == UPLOAD_ERR_OK) {
            $image_filename = $this->handleImageUpload($data['image_file'], 'products'); // Subdir is 'products'
            if (!$image_filename) {
                // Optionally return an error or use a default image path
                error_log("ProductModel::addProduct - Main image upload failed.");
                // For simplicity, we'll proceed without an image if upload fails, or you can return false here
            }
        }
        $db_image_path = $image_filename ? $this->mainImageUploadDir . $image_filename : null;


        $this->db->query('INSERT INTO products (name, description, price, image_url, stock_quantity, initial_stock_quantity, category_id, product_type, vendor_id, affiliate_commission_type, affiliate_commission_value, created_at, updated_at) 
                          VALUES (:name, :description, :price, :image_url, :stock_quantity, :initial_stock_quantity, :category_id, :product_type, :vendor_id, :affiliate_commission_type, :affiliate_commission_value, NOW(), NOW())');
        
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':price', ($data['product_type'] == 'variable' || !isset($data['price']) || $data['price'] === '') ? null : (float)$data['price']);
        $this->db->bind(':image_url', $db_image_path); // Store relative path from webroot
        
        $stock_qty = ($data['product_type'] == 'variable' && empty($data['stock_quantity_explicit'])) ? 0 : (isset($data['stock_quantity']) ? (int)$data['stock_quantity'] : 0);
        $this->db->bind(':stock_quantity', $stock_qty);
        $this->db->bind(':initial_stock_quantity', isset($data['initial_stock_quantity']) ? (int)$data['initial_stock_quantity'] : $stock_qty); 
        
        $this->db->bind(':category_id', (empty($data['category_id'])) ? null : (int)$data['category_id']);
        $this->db->bind(':product_type', $data['product_type'] ?? 'simple');
        $this->db->bind(':vendor_id', isset($data['vendor_id']) ? (int)$data['vendor_id'] : null);
        $this->db->bind(':affiliate_commission_type', $data['affiliate_commission_type'] ?? 'none');
        $this->db->bind(':affiliate_commission_value', ($data['affiliate_commission_type'] == 'none' || empty($data['affiliate_commission_value'])) ? null : (float)$data['affiliate_commission_value']);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        error_log("ProductModel::addProduct failed. DB Error: " . implode(" | ", $this->db->getErrorInfo()));
        // If product insert fails, delete the uploaded image
        if ($image_filename && defined('FCPATH') && file_exists(FCPATH . $this->mainImageUploadDir . $image_filename)) {
            unlink(FCPATH . $this->mainImageUploadDir . $image_filename);
        }
        return false;
    }

    public function updateProduct($data) {
        $product_id = (int)$data['id'];
        $current_product = $this->getProductById($product_id);
        if (!$current_product) return false;

        $image_filename_to_update = $current_product['image_url'] ? basename($current_product['image_url']) : null;

        if (isset($data['delete_current_image']) && $data['delete_current_image'] == '1' && $image_filename_to_update) {
            if (defined('FCPATH') && file_exists(FCPATH . $this->mainImageUploadDir . $image_filename_to_update)) {
                unlink(FCPATH . $this->mainImageUploadDir . $image_filename_to_update);
            }
            $image_filename_to_update = null;
        }

        if (isset($data['image_file']) && $data['image_file']['error'] == UPLOAD_ERR_OK) {
            $new_image_filename = $this->handleImageUpload($data['image_file'], 'products', $image_filename_to_update); // Pass old filename to delete
            if ($new_image_filename) {
                $image_filename_to_update = $new_image_filename;
            } else {
                // Image upload failed, decide if this is a fatal error for the update
                error_log("ProductModel::updateProduct - New main image upload failed for product ID {$product_id}.");
                // You might want to set an error message and return false or proceed without changing the image.
            }
        }
        $db_image_path_to_update = $image_filename_to_update ? $this->mainImageUploadDir . $image_filename_to_update : null;

        $sql = 'UPDATE products SET
                    name = :name, description = :description, price = :price, image_url = :image_url,
                    stock_quantity = :stock_quantity, category_id = :category_id, product_type = :product_type,
                    vendor_id = :vendor_id,
                    affiliate_commission_type = :affiliate_commission_type, affiliate_commission_value = :affiliate_commission_value';
        
        if (isset($data['initial_stock_quantity'])) { 
             $sql .= ", initial_stock_quantity = :initial_stock_quantity";
        }
        $sql .= ', updated_at = NOW() WHERE id = :id'; // Ensure updated_at is set
        $this->db->query($sql);
        
        $this->db->bind(':id', $product_id);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':description', $data['description'] ?? $current_product['description']);
        $this->db->bind(':price', ($data['product_type'] == 'variable' || !isset($data['price']) || $data['price'] === '') ? null : (float)$data['price']);
        $this->db->bind(':image_url', $db_image_path_to_update);
        
        if ($data['product_type'] == 'variable') {
            $this->db->bind(':stock_quantity', (isset($data['stock_quantity']) && $data['stock_quantity'] !== '') ? (int)$data['stock_quantity'] : 0);
        } else { 
            $this->db->bind(':stock_quantity', (int)($data['stock_quantity'] ?? $current_product['stock_quantity']));
        }
        
        if (isset($data['initial_stock_quantity'])) {
           $this->db->bind(':initial_stock_quantity', (int)$data['initial_stock_quantity']);
        }

        $this->db->bind(':category_id', (empty($data['category_id'])) ? null : (int)$data['category_id']);
        $this->db->bind(':product_type', $data['product_type']);
        $this->db->bind(':vendor_id', isset($data['vendor_id']) ? (int)$data['vendor_id'] : $current_product['vendor_id']);
        $this->db->bind(':affiliate_commission_type', $data['affiliate_commission_type'] ?? $current_product['affiliate_commission_type']);
        $this->db->bind(':affiliate_commission_value', ($data['affiliate_commission_type'] == 'none' || empty($data['affiliate_commission_value'])) ? null : (float)$data['affiliate_commission_value']);

        if($this->db->execute()){
            return true;
        }
        error_log("ProductModel::updateProduct failed for ID {$data['id']}. DB Error: " . implode(" | ", $this->db->getErrorInfo()));
        // If DB update failed but a new image was uploaded, delete the new image
        if (isset($new_image_filename) && $new_image_filename && defined('FCPATH') && file_exists(FCPATH . $this->mainImageUploadDir . $new_image_filename)) {
            unlink(FCPATH . $this->mainImageUploadDir . $new_image_filename);
        }
        return false;
    }

    public function deleteProduct($id) {
        $product = $this->getProductById($id);
        if (!$product) return false;

        // Delete main image
        if (!empty($product['image_url']) && defined('FCPATH') && file_exists(FCPATH . $product['image_url'])) {
            @unlink(FCPATH . $product['image_url']);
        }
        // Delete gallery images
        $galleryImages = $this->getGalleryImages($id);
        foreach ($galleryImages as $gImage) {
            $this->deleteGalleryImage($gImage['id']); // This will also delete the file
        }
        // Note: Deleting product variations and configurable attributes should be handled by ON DELETE CASCADE
        // or explicitly called from the controller before calling deleteProduct if not.

        $this->db->query('DELETE FROM products WHERE id = :id');
        $this->db->bind(':id', (int)$id);
        if ($this->db->execute()) { return $this->db->rowCount() > 0; }
        error_log("ProductModel::deleteProduct failed for ID {$id}. DB Error: " . implode(" | ", $this->db->getErrorInfo()));
        return false;
    }

    public function getSalesCount($product_id) { /* ... as before ... */ }
    public function getProductsForExport($startDate = null, $endDate = null) { /* ... as before ... */ }
    public function getProductCount() { /* ... as before ... */ }
    public function getVariationSalesAndStock($variation_id) { /* ... as before ... */ }


    // --- Gallery Image Methods ---
    public function addGalleryImage($product_id, $file_data, $alt_text = null, $sort_order = 0) {
        $product_id = (int)$product_id;
        // Use the gallery-specific upload directory
        $image_filename = $this->handleImageUpload($file_data, 'gallery_prod_' . $product_id, $this->galleryImageUploadDir);

        if ($image_filename) {
            $db_gallery_image_path = $this->galleryImageUploadDir . $image_filename;
            $this->db->query('INSERT INTO product_gallery_images (product_id, image_path, alt_text, sort_order) 
                              VALUES (:product_id, :image_path, :alt_text, :sort_order)');
            $this->db->bind(':product_id', $product_id);
            $this->db->bind(':image_path', $db_gallery_image_path); // Store path relative to web root
            $this->db->bind(':alt_text', $alt_text);
            $this->db->bind(':sort_order', (int)$sort_order);
            
            if ($this->db->execute()) {
                return $this->db->lastInsertId();
            } else {
                error_log("ProductModel::addGalleryImage - Failed to insert gallery image record for product ID {$product_id}. DB Error: " . print_r($this->db->getErrorInfo(), true));
                if (defined('FCPATH') && file_exists(FCPATH . $db_gallery_image_path)) {
                    unlink(FCPATH . $db_gallery_image_path);
                }
                return false;
            }
        }
        error_log("ProductModel::addGalleryImage - File upload failed for gallery image, product ID {$product_id}.");
        return false;
    }

    public function getGalleryImages($product_id) {
        $product_id = (int)$product_id;
        $this->db->query('SELECT id, product_id, image_path, alt_text, sort_order 
                          FROM product_gallery_images 
                          WHERE product_id = :product_id ORDER BY sort_order ASC, id ASC');
        $this->db->bind(':product_id', $product_id);
        $results = $this->db->resultSet();
        
        // Prepend BASE_URL for display if image_path is relative to web root
        foreach ($results as $key => $image) {
            if (isset($image['image_path']) && !empty($image['image_path'])) {
                 // Assuming image_path is stored relative to FCPATH (e.g., "uploads/products/gallery/image.jpg")
                 // and BASE_URL is the web root URL.
                $results[$key]['full_url'] = BASE_URL . ltrim($image['image_path'], '/');
            } else {
                $results[$key]['full_url'] = null;
            }
        }
        return $results ?: [];
    }

    public function deleteGalleryImage($gallery_image_id) {
        $gallery_image_id = (int)$gallery_image_id;
        
        $this->db->query('SELECT image_path FROM product_gallery_images WHERE id = :id');
        $this->db->bind(':id', $gallery_image_id);
        $row = $this->db->single();

        if ($row && isset($row['image_path'])) {
            // image_path is stored relative to FCPATH/uploads/ (e.g., products/gallery/filename.jpg)
            // So the actual file path is FCPATH . image_path
            $image_file_absolute_path = defined('FCPATH') ? FCPATH . $row['image_path'] : null;
            
            $this->db->query('DELETE FROM product_gallery_images WHERE id = :id');
            $this->db->bind(':id', $gallery_image_id);
            
            if ($this->db->execute()) {
                if ($image_file_absolute_path && file_exists($image_file_absolute_path)) {
                    if(!@unlink($image_file_absolute_path)){
                        error_log("ProductModel::deleteGalleryImage - Could not delete image file: " . $image_file_absolute_path);
                    }
                }
                return true;
            } else {
                error_log("ProductModel::deleteGalleryImage - Failed to delete DB record for ID {$gallery_image_id}.");
                return false;
            }
        }
        error_log("ProductModel::deleteGalleryImage - Gallery image with ID {$gallery_image_id} not found in DB.");
        return false;
    }

    public function updateGalleryImageAltText($gallery_image_id, $alt_text) {
        $this->db->query('UPDATE product_gallery_images SET alt_text = :alt_text, updated_at = NOW() WHERE id = :id');
        $this->db->bind(':alt_text', $alt_text);
        $this->db->bind(':id', (int)$gallery_image_id);
        return $this->db->execute();
    }
}
?>
