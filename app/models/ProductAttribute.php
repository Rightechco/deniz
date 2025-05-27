<?php
// app/models/ProductAttribute.php

class ProductAttribute {
    private $db;

    public function __construct() { 
        if (class_exists('Database')) {
            $this->db = new Database();
        } else {
            // This should ideally not happen if core files are loaded correctly.
            die("Fatal Error: Database class not found in ProductAttribute model.");
        }
    }

    // --- Attribute Methods ---

    public function getAllAttributes() {
        $this->db->query("SELECT * FROM attributes ORDER BY name ASC");
        $results = $this->db->resultSet();
        return $results ? $results : [];
    }

    public function getAttributeById($id) {
        $this->db->query("SELECT * FROM attributes WHERE id = :id");
        $this->db->bind(':id', (int)$id);
        $row = $this->db->single();
        return ($this->db->rowCount() > 0) ? $row : false;
    }

    public function getAttributeByName($name) {
        $this->db->query("SELECT * FROM attributes WHERE name = :name");
        $this->db->bind(':name', $name);
        $row = $this->db->single();
        return ($this->db->rowCount() > 0) ? $row : false;
    }

    public function addAttribute($data) {
        $this->db->query('INSERT INTO attributes (name) VALUES (:name)');
        $this->db->bind(':name', $data['name']);
        return $this->db->execute();
    }

    public function updateAttribute($data) {
        $this->db->query('UPDATE attributes SET name = :name WHERE id = :id');
        $this->db->bind(':id', (int)$data['id']);
        $this->db->bind(':name', $data['name']);
        return $this->db->execute();
    }

    public function deleteAttribute($id) {
        // Cascading deletes should handle related attribute_values and product_variation_attributes
        $this->db->query('DELETE FROM attributes WHERE id = :id');
        $this->db->bind(':id', (int)$id);
        return $this->db->execute();
    }

    // --- Attribute Value Methods ---

    public function getValuesByAttributeId($attribute_id) {
        $this->db->query("SELECT * FROM attribute_values WHERE attribute_id = :attribute_id ORDER BY value ASC");
        $this->db->bind(':attribute_id', (int)$attribute_id);
        $results = $this->db->resultSet();
        return $results ? $results : [];
    }

    public function getAttributeValueById($value_id) {
        $this->db->query("SELECT * FROM attribute_values WHERE id = :id");
        $this->db->bind(':id', (int)$value_id);
        $row = $this->db->single();
        return ($this->db->rowCount() > 0) ? $row : false;
    }

    public function addAttributeValue($data) {
        try {
            $this->db->query('INSERT INTO attribute_values (attribute_id, value) VALUES (:attribute_id, :value)');
            $this->db->bind(':attribute_id', (int)$data['attribute_id']);
            $this->db->bind(':value', $data['value']);
            return $this->db->execute();
        } catch (PDOException $e) {
            if ($e->getCode() == '23000' || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) { // Unique constraint violation
                return false; 
            }
            error_log("Error in addAttributeValue: " . $e->getMessage());
            return false;
        }
    }

    public function updateAttributeValue($data) {
         try {
            $this->db->query('UPDATE attribute_values SET value = :value WHERE id = :id');
            $this->db->bind(':id', (int)$data['id']);
            $this->db->bind(':value', $data['value']);
            return $this->db->execute();
        } catch (PDOException $e) {
            if ($e->getCode() == '23000' || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) { // Unique constraint violation
                return false;
            }
            error_log("Error in updateAttributeValue: " . $e->getMessage());
            return false;
        }
    }

    public function deleteAttributeValue($value_id) {
        // Cascading delete should handle product_variation_attributes if an attribute_value is deleted
        $this->db->query('DELETE FROM attribute_values WHERE id = :id');
        $this->db->bind(':id', (int)$value_id);
        return $this->db->execute();
    }

    // --- Product Configurable Attribute Methods ---

    public function getConfigurableAttributesForProduct($product_id) {
        $this->db->query("SELECT attribute_id FROM product_configurable_attributes WHERE product_id = :product_id");
        $this->db->bind(':product_id', (int)$product_id);
        $results = $this->db->resultSet();
        $attribute_ids = [];
        if ($results) {
            foreach ($results as $row) {
                $attribute_ids[] = $row['attribute_id'];
            }
        }
        return $attribute_ids;
    }
    
    public function getConfigurableAttributeDetailsForProduct($product_id) {
        $this->db->query("SELECT a.id, a.name 
                          FROM product_configurable_attributes pca
                          JOIN attributes a ON pca.attribute_id = a.id
                          WHERE pca.product_id = :product_id ORDER BY a.name ASC");
        $this->db->bind(':product_id', (int)$product_id);
        $attributes = $this->db->resultSet();
        if ($attributes) {
            foreach ($attributes as $key => $attribute) {
                $attributes[$key]['values'] = $this->getValuesByAttributeId($attribute['id']);
            }
            return $attributes;
        }
        return [];
    }

    public function setConfigurableAttributesForProduct($product_id, $attribute_ids = []) {
        try {
            $this->db->query("DELETE FROM product_configurable_attributes WHERE product_id = :product_id");
            $this->db->bind(':product_id', (int)$product_id);
            $this->db->execute(); // It's okay if no rows are deleted

            if (!empty($attribute_ids)) {
                foreach ($attribute_ids as $attribute_id) {
                    if (empty($attribute_id) || !is_numeric($attribute_id)) continue;
                    $this->db->query("INSERT INTO product_configurable_attributes (product_id, attribute_id) VALUES (:product_id, :attribute_id)");
                    $this->db->bind(':product_id', (int)$product_id);
                    $this->db->bind(':attribute_id', (int)$attribute_id);
                    if (!$this->db->execute()) { 
                        error_log("Error inserting into product_configurable_attributes for product_id: {$product_id}, attribute_id: {$attribute_id}");
                        return false; 
                    }
                }
            }
            return true;
        } catch (Exception $e) {
            error_log("Error in setConfigurableAttributesForProduct: " . $e->getMessage());
            return false;
        }
    }
    
    // --- Product Variation Methods ---

    public function getSalesCountForVariation($variation_id) {
        // Ensure order_items table has variation_id and orders table has order_status
        $this->db->query("SELECT SUM(oi.quantity) as total_sold 
                          FROM order_items oi
                          JOIN orders o ON oi.order_id = o.id
                          WHERE oi.variation_id = :variation_id
                          AND o.order_status NOT IN ('cancelled', 'refunded', 'pending_confirmation')"); // Filter by relevant order statuses
        $this->db->bind(':variation_id', (int)$variation_id);
        $result = $this->db->single();
        return $result && isset($result['total_sold']) ? (int)$result['total_sold'] : 0;
    }

    public function getVariationsForProduct($parent_product_id) {
        $this->db->query("SELECT id, sku, price, stock_quantity, initial_stock_quantity, image_url, is_active 
                          FROM product_variations 
                          WHERE parent_product_id = :parent_product_id ORDER BY id ASC");
        $this->db->bind(':parent_product_id', (int)$parent_product_id);
        $variations = $this->db->resultSet();

        if ($variations) {
            foreach ($variations as $key => $variation) {
                // Get attributes for this variation
                $this->db->query("SELECT pva.attribute_id, pva.attribute_value_id, 
                                         a.name as attribute_name, av.value as attribute_value
                                  FROM product_variation_attributes pva
                                  JOIN attributes a ON pva.attribute_id = a.id
                                  JOIN attribute_values av ON pva.attribute_value_id = av.id
                                  WHERE pva.product_variation_id = :variation_id ORDER BY a.name ASC");
                $this->db->bind(':variation_id', $variation['id']);
                $variations[$key]['attributes'] = $this->db->resultSet() ?: [];
                
                // Add sales count and remaining stock for each variation
                $variations[$key]['sales_count'] = $this->getSalesCountForVariation($variation['id']);
                
                $current_variation_stock = isset($variation['stock_quantity']) ? (int)$variation['stock_quantity'] : 0;
                $variations[$key]['current_stock_quantity'] = $current_variation_stock; 
                
                $initial_variation_stock = isset($variation['initial_stock_quantity']) ? (int)$variation['initial_stock_quantity'] : 0;
                // remaining_stock_from_initial is based on initial stock
                $variations[$key]['remaining_stock_from_initial'] = $initial_variation_stock - $variations[$key]['sales_count'];
                if ($variations[$key]['remaining_stock_from_initial'] < 0) {
                    $variations[$key]['remaining_stock_from_initial'] = 0;
                }
            }
        }
        return $variations ? $variations : [];
    }

    public function getVariationById($variation_id) {
        $this->db->query("SELECT * FROM product_variations WHERE id = :id");
        $this->db->bind(':id', (int)$variation_id);
        $variation = $this->db->single();
        if ($variation) {
            $this->db->query("SELECT pva.attribute_id, pva.attribute_value_id, 
                                     a.name as attribute_name, av.value as attribute_value
                              FROM product_variation_attributes pva
                              JOIN attributes a ON pva.attribute_id = a.id
                              JOIN attribute_values av ON pva.attribute_value_id = av.id
                              WHERE pva.product_variation_id = :variation_id ORDER BY a.name ASC");
            $this->db->bind(':variation_id', $variation['id']);
            $variation['attributes'] = $this->db->resultSet() ?: [];
            return $variation;
        }
        return false;
    }

    public function addVariation($data, $attributesData) {
        // 1. Check for duplicate attribute combination for this parent product
        $existingVariations = $this->getVariationsForProduct((int)$data['parent_product_id']);
        if ($existingVariations && !empty($attributesData)) {
            foreach ($existingVariations as $existingVariation) {
                if (isset($existingVariation['attributes']) && count($existingVariation['attributes']) == count($attributesData)) {
                    $existing_attrs_map = []; 
                    foreach($existingVariation['attributes'] as $ex_attr) { 
                        $existing_attrs_map[(int)$ex_attr['attribute_id']] = (int)$ex_attr['attribute_value_id']; 
                    }
                    $new_attrs_map = []; 
                    foreach ($attributesData as $new_attr_id => $new_val_id) { 
                        $new_attrs_map[(int)$new_attr_id] = (int)$new_val_id; 
                    }
                    ksort($existing_attrs_map); 
                    ksort($new_attrs_map);
                    if ($existing_attrs_map === $new_attrs_map) {
                        error_log("Duplicate variation attempt for parent_product_id: " . $data['parent_product_id'] . " with attributes: " . json_encode($attributesData));
                        return 'duplicate_combination'; 
                    }
                }
            }
        }

        // 2. Insert new variation
        try {
            $this->db->query("INSERT INTO product_variations (parent_product_id, sku, price, stock_quantity, initial_stock_quantity, image_url, is_active)
                              VALUES (:parent_product_id, :sku, :price, :stock_quantity, :initial_stock_quantity, :image_url, :is_active)");
            $this->db->bind(':parent_product_id', (int)$data['parent_product_id']);
            $this->db->bind(':sku', !empty($data['sku']) ? $data['sku'] : null);
            $this->db->bind(':price', !empty($data['price']) ? (float)$data['price'] : null);
            $stock_qty_var = isset($data['stock_quantity']) ? (int)$data['stock_quantity'] : 0;
            $this->db->bind(':stock_quantity', $stock_qty_var);
            // Initial stock is same as current stock at creation for variation
            $this->db->bind(':initial_stock_quantity', isset($data['initial_stock_quantity']) ? (int)$data['initial_stock_quantity'] : $stock_qty_var); 
            $this->db->bind(':image_url', !empty($data['image_url']) ? $data['image_url'] : null);
            $this->db->bind(':is_active', isset($data['is_active']) ? (int)$data['is_active'] : 1);

            if (!$this->db->execute()) {
                error_log("Failed to insert into product_variations. DB Error: " . (isset($this->db->dbh) ? implode(' | ', $this->db->dbh->errorInfo()) : "DB handler not available"));
                return 'db_error_variation_insert';
            }
            $variation_id = $this->db->lastInsertId();
            if (!$variation_id) {
                error_log("Failed to get lastInsertId after inserting into product_variations.");
                return 'db_error_variation_id';
            }

            if (!empty($attributesData)) {
                foreach ($attributesData as $attribute_id => $attribute_value_id) {
                    if (empty($attribute_id) || !is_numeric($attribute_id) || empty($attribute_value_id) || !is_numeric($attribute_value_id)) {
                        error_log("Invalid attribute_id or attribute_value_id for variation_id: {$variation_id}. AttrID: {$attribute_id}, ValID: {$attribute_value_id}");
                        continue; 
                    }
                    $this->db->query("INSERT INTO product_variation_attributes (product_variation_id, attribute_id, attribute_value_id)
                                      VALUES (:variation_id, :attribute_id, :attribute_value_id)");
                    $this->db->bind(':variation_id', $variation_id);
                    $this->db->bind(':attribute_id', (int)$attribute_id);
                    $this->db->bind(':attribute_value_id', (int)$attribute_value_id);
                    if (!$this->db->execute()) {
                        error_log("Failed to insert into product_variation_attributes for var_id: {$variation_id}. DB Error: " . (isset($this->db->dbh) ? implode(' | ', $this->db->dbh->errorInfo()) : "DB handler not available"));
                        $this->deleteVariation($variation_id); // Attempt to delete the incomplete variation
                        return 'db_error_attribute_insert';
                    }
                }
            }
            return (int)$variation_id;
        } catch (PDOException $e) {
            error_log("PDOException in addVariation: " . $e->getMessage());
            return 'pdo_exception';
        } catch (Exception $e) {
            error_log("General Exception in addVariation: " . $e->getMessage());
            return 'general_exception';
        }
    }

    public function updateVariation($variation_id, $data) { // attributesData is not used to change defining attributes
        try {
            $sql = "UPDATE product_variations SET
                        sku = :sku, 
                        price = :price, 
                        stock_quantity = :stock_quantity,
                        image_url = :image_url, 
                        is_active = :is_active";
            
            // Only update initial_stock_quantity if it's explicitly provided for edit
            if (isset($data['initial_stock_quantity'])) { 
                 $sql .= ", initial_stock_quantity = :initial_stock_quantity";
            }
            $sql .= " WHERE id = :variation_id";
            $this->db->query($sql);
            
            $this->db->bind(':variation_id', (int)$variation_id);
            $this->db->bind(':sku', !empty($data['sku']) ? $data['sku'] : null);
            $this->db->bind(':price', !empty($data['price']) ? (float)$data['price'] : null);
            $this->db->bind(':stock_quantity', (int)$data['stock_quantity']);
            if (isset($data['initial_stock_quantity'])) {
               $this->db->bind(':initial_stock_quantity', (int)$data['initial_stock_quantity']);
            }
            $this->db->bind(':image_url', !empty($data['image_url']) ? $data['image_url'] : null);
            $this->db->bind(':is_active', isset($data['is_active']) ? (int)$data['is_active'] : 1);

            if (!$this->db->execute()) { 
                error_log("Failed to update product_variations for var_id: {$variation_id}. DB Error: " . (isset($this->db->dbh) ? implode(' | ', $this->db->dbh->errorInfo()) : "DB handler not available"));
                return false; 
            }
            // Defining attributes of a variation (product_variation_attributes) are not updated here.
            // If they need to change, the variation should be deleted and a new one created.
            return true;
        } catch (Exception $e) {
            error_log("Error in updateVariation (model): " . $e->getMessage());
            return false;
        }
    }

    public function deleteVariation($variation_id) {
        $this->db->query("DELETE FROM product_variations WHERE id = :variation_id");
        $this->db->bind(':variation_id', (int)$variation_id);
        // Cascading delete should handle product_variation_attributes
        if ($this->db->execute()){
            return $this->db->rowCount() > 0;
        }
        error_log("Error in deleteVariation for var_id: {$variation_id}. DB Error: " . (isset($this->db->dbh) ? implode(' | ', $this->db->dbh->errorInfo()) : "DB handler not available"));
        return false;
    }
    
    public function decreaseVariationStock($variation_id, $quantity_to_decrease) {
        $variation_id = (int)$variation_id;
        $quantity_to_decrease = (int)$quantity_to_decrease;
        error_log("ProductAttributeModel::decreaseVariationStock called for variation_id: {$variation_id}, quantity: {$quantity_to_decrease}");
        
        $this->db->query("SELECT stock_quantity FROM product_variations WHERE id = :id");
        $this->db->bind(':id', $variation_id);
        $variation_stock_data = $this->db->single();

        if ($variation_stock_data && (int)$variation_stock_data['stock_quantity'] >= $quantity_to_decrease) {
            $this->db->query("UPDATE product_variations 
                              SET stock_quantity = stock_quantity - :quantity 
                              WHERE id = :id AND stock_quantity >= :quantity_check");
            $this->db->bind(':quantity', $quantity_to_decrease);
            $this->db->bind(':id', $variation_id);
            $this->db->bind(':quantity_check', $quantity_to_decrease);

            if ($this->db->execute()) {
                $rowCount = $this->db->rowCount();
                error_log("ProductAttributeModel::decreaseVariationStock - UPDATE executed. Rows affected: {$rowCount} for var_id: {$variation_id}");
                return $rowCount > 0;
            } else {
                error_log("ProductAttributeModel::decreaseVariationStock - UPDATE failed for var_id: {$variation_id}. DB Error: " . (isset($this->db->dbh) ? implode(' | ', $this->db->dbh->errorInfo()) : "DB handler not available"));
                return false;
            }
        } else { 
            if (!$variation_stock_data) {
                error_log("ProductAttributeModel::decreaseVariationStock - Variation not found for id: {$variation_id}");
            } else {
                error_log("ProductAttributeModel::decreaseVariationStock - Not enough stock for var_id: {$variation_id}. Current: {$variation_stock_data['stock_quantity']}, Requested: {$quantity_to_decrease}");
            }
            return false; 
        }
    }

    public function deleteAllVariationsForProduct($parent_product_id) {
        $this->db->query("DELETE FROM product_variations WHERE parent_product_id = :parent_product_id");
        $this->db->bind(':parent_product_id', (int)$parent_product_id);
        // Cascading delete will handle product_variation_attributes
        return $this->db->execute();
    }
}
// تگ پایانی PHP را حذف کنید
