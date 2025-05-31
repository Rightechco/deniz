<?php
// app/models/ProductAttribute.php

class ProductAttribute {
    private $db;
    private $variationUploadDir = 'uploads/variations/';

    public function __construct() {
        if (class_exists('Database')) {
            $this->db = new Database();
        } else {
            error_log("ProductAttributeModel FATAL ERROR: Database class not found.");
            throw new Exception("Fatal Error: Database class not found in ProductAttribute model.");
        }
    }

    // --- Attribute Management ---
    public function getAllAttributes() {
        $this->db->query("SELECT * FROM product_attributes ORDER BY name ASC");
        return $this->db->resultSet() ?: [];
    }

    public function getAllAttributesWithValues() {
        $attributes = $this->getAllAttributes();
        if ($attributes) {
            foreach ($attributes as $key => $attribute) {
                if (isset($attribute['id'])) {
                    $values = $this->getValuesByAttributeId($attribute['id']);
                    $attributes[$key]['values'] = $values ?: [];
                } else {
                    $attributes[$key]['values'] = []; 
                }
            }
        }
        return $attributes ?: [];
    }

    public function getAttributeById($id) {
        $this->db->query("SELECT * FROM product_attributes WHERE id = :id");
        $this->db->bind(':id', (int)$id);
        return $this->db->single() ?: false;
    }
    
    public function getAttributeByName($name) {
        $this->db->query("SELECT * FROM product_attributes WHERE name = :name");
        $this->db->bind(':name', $name);
        return $this->db->single() ?: false;
    }

    public function addAttribute($data) {
        $this->db->query("INSERT INTO product_attributes (name) VALUES (:name)");
        $this->db->bind(':name', $data['name']);
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        $db_error_info = $this->db->getErrorInfo();
        error_log("ProductAttributeModel::addAttribute failed. DB Error: " . (is_array($db_error_info) ? implode(" | ", $db_error_info) : ($db_error_info ?: 'Unknown DB error')));
        return false;
    }

    public function updateAttribute($data) {
        $this->db->query("UPDATE product_attributes SET name = :name WHERE id = :id");
        $this->db->bind(':id', (int)$data['id']);
        $this->db->bind(':name', $data['name']);
        return $this->db->execute();
    }

    public function deleteAttribute($attribute_id) {
        $attribute_id = (int)$attribute_id;
        if (method_exists($this->db, 'beginTransaction')) {
            $this->db->beginTransaction();
        }
        try {
            $this->db->query("DELETE FROM product_attribute_values WHERE attribute_id = :attribute_id");
            $this->db->bind(':attribute_id', $attribute_id);
            $this->db->execute();

            $this->db->query("DELETE FROM product_configurable_attributes WHERE attribute_id = :attribute_id");
            $this->db->bind(':attribute_id', $attribute_id);
            $this->db->execute();

            $this->db->query("DELETE FROM product_variation_attributes WHERE attribute_id = :attribute_id");
            $this->db->bind(':attribute_id', $attribute_id);
            $this->db->execute();

            $this->db->query("DELETE FROM product_attributes WHERE id = :id");
            $this->db->bind(':id', $attribute_id);
            if (!$this->db->execute()) {
                if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
                return false;
            }

            if (method_exists($this->db, 'commit')) $this->db->commit();
            return true;

        } catch (Exception $e) {
            if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
            error_log("Error in ProductAttributeModel::deleteAttribute: " . $e->getMessage());
            return false;
        }
    }

    // --- Attribute Value Management ---
    public function getValuesByAttributeId($attribute_id) {
        $this->db->query("SELECT * FROM product_attribute_values WHERE attribute_id = :attribute_id ORDER BY value ASC");
        $this->db->bind(':attribute_id', (int)$attribute_id);
        return $this->db->resultSet() ?: [];
    }

    public function getAttributeValueById($value_id) {
        $this->db->query("SELECT * FROM product_attribute_values WHERE id = :id");
        $this->db->bind(':id', (int)$value_id);
        return $this->db->single() ?: false;
    }

    public function addAttributeValue($data) {
        $this->db->query("SELECT id FROM product_attribute_values WHERE attribute_id = :attribute_id AND value = :value");
        $this->db->bind(':attribute_id', (int)$data['attribute_id']);
        $this->db->bind(':value', $data['value']);
        if ($this->db->single()) {
            error_log("ProductAttributeModel::addAttributeValue - Duplicate value '{$data['value']}' for attribute_id '{$data['attribute_id']}'.");
            return false; 
        }

        $this->db->query("INSERT INTO product_attribute_values (attribute_id, value) VALUES (:attribute_id, :value)");
        $this->db->bind(':attribute_id', (int)$data['attribute_id']);
        $this->db->bind(':value', $data['value']);
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        $db_error_info = $this->db->getErrorInfo();
        error_log("ProductAttributeModel::addAttributeValue failed. DB Error: " . (is_array($db_error_info) ? implode(" | ", $db_error_info) : ($db_error_info ?: 'Unknown DB error')));
        return false;
    }

    public function updateAttributeValue($data) {
        $this->db->query("SELECT id FROM product_attribute_values WHERE attribute_id = :attribute_id AND value = :value AND id != :id");
        $this->db->bind(':attribute_id', (int)$data['attribute_id']);
        $this->db->bind(':value', $data['value']);
        $this->db->bind(':id', (int)$data['id']);
        if ($this->db->single()) {
            error_log("ProductAttributeModel::updateAttributeValue - Duplicate value '{$data['value']}' for attribute_id '{$data['attribute_id']}' when updating id '{$data['id']}'.");
            return false; 
        }

        $this->db->query("UPDATE product_attribute_values SET value = :value WHERE id = :id");
        $this->db->bind(':id', (int)$data['id']);
        $this->db->bind(':value', $data['value']);
        return $this->db->execute();
    }

    public function deleteAttributeValue($value_id) {
        $value_id = (int)$value_id;
        $this->db->query("DELETE FROM product_attribute_values WHERE id = :id");
        $this->db->bind(':id', $value_id);
        return $this->db->execute();
    }

    // --- Product Configurable Attributes ---
    public function setConfigurableAttributesForProduct($product_id, $attribute_ids) {
        $product_id = (int)$product_id;
        if (method_exists($this->db, 'beginTransaction')) {
            $this->db->beginTransaction();
        }
        try {
            $this->db->query("DELETE FROM product_configurable_attributes WHERE product_id = :product_id");
            $this->db->bind(':product_id', $product_id);
            $this->db->execute();

            if (!empty($attribute_ids) && is_array($attribute_ids)) {
                $this->db->query("INSERT INTO product_configurable_attributes (product_id, attribute_id) VALUES (:product_id, :attribute_id)");
                foreach ($attribute_ids as $attribute_id) {
                    if (is_numeric($attribute_id)) {
                        $this->db->bind(':product_id', $product_id);
                        $this->db->bind(':attribute_id', (int)$attribute_id);
                        if (!$this->db->execute()) {
                            // Log the specific error before throwing exception
                            $db_error_info = $this->db->getErrorInfo();
                            error_log("ProductAttributeModel::setConfigurableAttributesForProduct - Failed to insert link for product_id {$product_id}, attribute_id {$attribute_id}. DB Error: " . (is_array($db_error_info) ? implode(" | ", $db_error_info) : ($db_error_info ?: 'Unknown')));
                            throw new Exception("Failed to insert configurable attribute link.");
                        }
                    }
                }
            }
            if (method_exists($this->db, 'commit')) $this->db->commit();
            return true;
        } catch (Exception $e) {
            if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
            error_log("Error in ProductAttributeModel::setConfigurableAttributesForProduct: " . $e->getMessage());
            return false;
        }
    }

    public function getConfigurableAttributesForProduct($product_id) {
        $this->db->query("SELECT attribute_id FROM product_configurable_attributes WHERE product_id = :product_id");
        $this->db->bind(':product_id', (int)$product_id);
        $results = $this->db->resultSet();
        return $results ? array_column($results, 'attribute_id') : [];
    }
    
    public function getConfigurableAttributeDetailsForProduct($product_id) {
        $this->db->query("SELECT pa.id, pa.name 
                          FROM product_attributes pa
                          JOIN product_configurable_attributes pca ON pa.id = pca.attribute_id
                          WHERE pca.product_id = :product_id
                          ORDER BY pa.name ASC");
        $this->db->bind(':product_id', (int)$product_id);
        $attributes = $this->db->resultSet();
        if ($attributes) {
            foreach ($attributes as $key => $attribute) {
                $attributes[$key]['values'] = $this->getValuesByAttributeId($attribute['id']);
            }
        }
        return $attributes ?: [];
    }

    private function generateVariationSku($parent_product_id, $attributesData = []) {
        $sku = 'VAR-' . $parent_product_id;
        if (!empty($attributesData)) {
            // Sort by attribute ID to ensure consistent SKU generation
            ksort($attributesData); 
            foreach ($attributesData as $attr_id => $val_id) {
                $sku .= '-' . $attr_id . '-' . $val_id;
            }
        }
        // Check if this SKU already exists
        $this->db->query("SELECT id FROM product_variations WHERE sku = :sku");
        $this->db->bind(':sku', $sku);
        if($this->db->single()){
            // If exists, append a unique suffix
            $sku .= '-' . substr(uniqid(), -4);
        }
        return $sku;
    }

    // --- Product Variation Management ---
    public function addVariation($data, $attributesData) {
        if (empty($data['sku'])) {
            $data['sku'] = $this->generateVariationSku($data['parent_product_id'], $attributesData);
            error_log("ProductAttributeModel::addVariation - Generated SKU: {$data['sku']} for product_id: {$data['parent_product_id']}");
        } else {
             // Check if provided SKU is unique
            $this->db->query("SELECT id FROM product_variations WHERE sku = :sku AND parent_product_id != :parent_product_id_for_check");
            $this->db->bind(':sku', $data['sku']);
            $this->db->bind(':parent_product_id_for_check', (int)$data['parent_product_id']); // Check SKU uniqueness across other products too if SKU is global
            if($this->db->single()){
                 error_log("ProductAttributeModel::addVariation - Provided SKU '{$data['sku']}' already exists.");
                 return 'duplicate_sku';
            }
        }
        
        if (!empty($attributesData)) {
            $existingVariation = $this->findVariationByAttributeCombination($data['parent_product_id'], $attributesData);
            if ($existingVariation) {
                error_log("ProductAttributeModel::addVariation - Duplicate attribute combination for product_id: {$data['parent_product_id']}");
                return 'duplicate_combination'; 
            }
        }

        if (method_exists($this->db, 'beginTransaction')) {
            $this->db->beginTransaction();
        }
        try {
            $this->db->query("INSERT INTO product_variations (parent_product_id, sku, price, stock_quantity, initial_stock_quantity, image_url, is_active) 
                              VALUES (:parent_product_id, :sku, :price, :stock_quantity, :initial_stock_quantity, :image_url, :is_active)");
            $this->db->bind(':parent_product_id', (int)$data['parent_product_id']);
            $this->db->bind(':sku', $data['sku']);
            $this->db->bind(':price', ($data['price'] !== null && $data['price'] !== '') ? (float)$data['price'] : null);
            $this->db->bind(':stock_quantity', (int)$data['stock_quantity']);
            $this->db->bind(':initial_stock_quantity', (int)($data['initial_stock_quantity'] ?? $data['stock_quantity']));
            $this->db->bind(':image_url', $data['image_url']);
            $this->db->bind(':is_active', (int)$data['is_active']);

            if (!$this->db->execute()) {
                $db_error_info = $this->db->getErrorInfo();
                // Check for duplicate SKU error specifically
                if (isset($db_error_info[1]) && $db_error_info[1] == 1062 && strpos($db_error_info[2], 'sku') !== false) {
                     error_log("ProductAttributeModel::addVariation - Duplicate SKU '{$data['sku']}' on INSERT. DB Error: " . implode(" | ", $db_error_info));
                     throw new Exception("Duplicate SKU", 23001); // Custom code for duplicate SKU
                }
                throw new Exception("Failed to insert product_variation. DB Error: " . (is_array($db_error_info) ? implode(" | ", $db_error_info) : ($db_error_info ?: 'Unknown')));
            }
            $variation_id = $this->db->lastInsertId();

            if (!empty($attributesData) && is_array($attributesData)) {
                $this->db->query("INSERT INTO product_variation_attributes (variation_id, attribute_id, attribute_value_id) 
                                  VALUES (:variation_id, :attribute_id, :attribute_value_id)");
                foreach ($attributesData as $attribute_id => $attribute_value_id) {
                    if (is_numeric($attribute_id) && is_numeric($attribute_value_id)) {
                        $this->db->bind(':variation_id', $variation_id);
                        $this->db->bind(':attribute_id', (int)$attribute_id);
                        $this->db->bind(':attribute_value_id', (int)$attribute_value_id);
                        if (!$this->db->execute()) {
                            throw new Exception("Failed to insert product_variation_attribute link.");
                        }
                    }
                }
            }
            if (method_exists($this->db, 'commit')) $this->db->commit();
            return $variation_id;
        } catch (Exception $e) {
            if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
            error_log("Error in ProductAttributeModel::addVariation: " . $e->getMessage());
            if ($e->getCode() === 23001 || ($e->getCode() === '23000' && strpos(strtolower($e->getMessage()), 'duplicate entry') !== false && strpos(strtolower($e->getMessage()), 'sku') !== false)) {
                 return 'duplicate_sku';
            }
            return $e->getCode() === '23000' ? 'db_error_duplicate' : 'general_exception';
        }
    }
    
    public function findVariationByAttributeCombination($parent_product_id, $attributesData) {
        if (empty($attributesData)) return false;

        $parent_product_id = (int)$parent_product_id;
        $params = [':parent_product_id' => $parent_product_id];
        $attribute_count = count($attributesData);
        
        $joins_and_conditions = "";
        $i = 0;
        foreach ($attributesData as $attr_id => $val_id) {
            $i++;
            $alias = "pva" . $i;
            $joins_and_conditions .= " JOIN product_variation_attributes {$alias} ON pv.id = {$alias}.variation_id 
                                       AND {$alias}.attribute_id = :attr_id_{$i} 
                                       AND {$alias}.attribute_value_id = :val_id_{$i} ";
            $params[":attr_id_{$i}"] = (int)$attr_id;
            $params[":val_id_{$i}"] = (int)$val_id;
        }

        $sql = "SELECT pv.id
                FROM product_variations pv
                {$joins_and_conditions}
                WHERE pv.parent_product_id = :parent_product_id
                AND (SELECT COUNT(*) FROM product_variation_attributes WHERE variation_id = pv.id) = :expected_attr_count";
        
        $params[':expected_attr_count'] = $attribute_count;
        
        $this->db->query($sql);
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        return $this->db->single() ?: false;
    }

    public function getVariationsForProduct($parent_product_id) {
        $parent_product_id = (int)$parent_product_id;
        $this->db->query("SELECT * FROM product_variations WHERE parent_product_id = :parent_product_id ORDER BY id ASC");
        $this->db->bind(':parent_product_id', $parent_product_id);
        $variations = $this->db->resultSet();

        if ($variations) {
            foreach ($variations as $key => $variation) {
                $this->db->query("SELECT pva.attribute_id, pva.attribute_value_id, 
                                         pa.name as attribute_name, pav.value as attribute_value 
                                  FROM product_variation_attributes pva
                                  JOIN product_attributes pa ON pva.attribute_id = pa.id
                                  JOIN product_attribute_values pav ON pva.attribute_value_id = pav.id
                                  WHERE pva.variation_id = :variation_id");
                $this->db->bind(':variation_id', $variation['id']);
                $variations[$key]['attributes'] = $this->db->resultSet() ?: [];
            }
        }
        return $variations ?: [];
    }
    
    public function getVariationById($variation_id) {
        $variation_id = (int)$variation_id;
        $this->db->query("SELECT pv.*, 
                                 p.name as parent_product_name, 
                                 p.affiliate_commission_type as parent_affiliate_commission_type, 
                                 p.affiliate_commission_value as parent_affiliate_commission_value 
                          FROM product_variations pv
                          JOIN products p ON pv.parent_product_id = p.id
                          WHERE pv.id = :variation_id");
        $this->db->bind(':variation_id', $variation_id);
        $variation = $this->db->single();

        if ($variation) {
            $this->db->query("SELECT pva.attribute_id, pva.attribute_value_id, 
                                     pa.name as attribute_name, pav.value as attribute_value 
                              FROM product_variation_attributes pva
                              JOIN product_attributes pa ON pva.attribute_id = pa.id
                              JOIN product_attribute_values pav ON pva.attribute_value_id = pav.id
                              WHERE pva.variation_id = :variation_id");
            $this->db->bind(':variation_id', $variation['id']);
            $variation['attributes'] = $this->db->resultSet() ?: [];
        }
        return $variation ?: false;
    }

    public function updateVariation($variation_id, $data, $attributesData = null) {
        $variation_id = (int)$variation_id;
        
        if (empty($data['sku'])) {
            // If SKU is being emptied, generate one. Or decide if empty SKU is allowed on update.
            // For now, let's assume if it's empty, it means it should be NULL or auto-generated if that's the policy.
            // If SKU must be unique and is emptied, it might be set to NULL if DB allows.
            // If you want to auto-generate on empty during update, add that logic here.
            // $data['sku'] = $this->generateVariationSku($data['parent_product_id'], $attributesData);
        } else {
            // Check if provided SKU is unique (excluding current variation)
            $this->db->query("SELECT id FROM product_variations WHERE sku = :sku AND id != :variation_id");
            $this->db->bind(':sku', $data['sku']);
            $this->db->bind(':variation_id', $variation_id);
            if($this->db->single()){
                 error_log("ProductAttributeModel::updateVariation - Provided SKU '{$data['sku']}' already exists for another variation.");
                 return 'duplicate_sku';
            }
        }

        if (!empty($attributesData)) {
            $parent_product_id_result = $this->db->query("SELECT parent_product_id FROM product_variations WHERE id = :vid");
            $this->db->bind(':vid', $variation_id);
            $parent_info = $this->db->single();
            if ($parent_info && isset($parent_info['parent_product_id'])) {
                $existingVariation = $this->findVariationByAttributeCombination($parent_info['parent_product_id'], $attributesData);
                if ($existingVariation && (int)$existingVariation['id'] !== $variation_id) {
                    error_log("ProductAttributeModel::updateVariation - Duplicate attribute combination for variation_id: {$variation_id}");
                    return 'duplicate_combination';
                }
            }
        }

        if (method_exists($this->db, 'beginTransaction')) {
            $this->db->beginTransaction();
        }
        try {
            $sql = "UPDATE product_variations SET sku = :sku, price = :price, stock_quantity = :stock_quantity, 
                    image_url = :image_url, is_active = :is_active";
            if (isset($data['initial_stock_quantity'])) { // Only update if provided
                 $sql .= ", initial_stock_quantity = :initial_stock_quantity";
            }
            $sql .= " WHERE id = :variation_id";

            $this->db->query($sql);
            $this->db->bind(':variation_id', $variation_id);
            $this->db->bind(':sku', $data['sku'] ?: null, PDO::PARAM_STR_OR_NULL); // Allow NULL for SKU
            $this->db->bind(':price', ($data['price'] !== null && $data['price'] !== '') ? (float)$data['price'] : null);
            $this->db->bind(':stock_quantity', (int)$data['stock_quantity']);
            $this->db->bind(':image_url', $data['image_url'] ?: null, PDO::PARAM_STR_OR_NULL);
            $this->db->bind(':is_active', (int)$data['is_active']);
            if (isset($data['initial_stock_quantity'])) {
                $this->db->bind(':initial_stock_quantity', (int)$data['initial_stock_quantity']);
            }

            if (!$this->db->execute()) {
                $db_error_info = $this->db->getErrorInfo();
                 if (isset($db_error_info[1]) && $db_error_info[1] == 1062 && strpos($db_error_info[2], 'sku') !== false) {
                     error_log("ProductAttributeModel::updateVariation - Duplicate SKU '{$data['sku']}' on UPDATE. DB Error: " . implode(" | ", $db_error_info));
                     throw new Exception("Duplicate SKU", 23001); 
                }
                throw new Exception("Failed to update product_variation. DB Error: " . (is_array($db_error_info) ? implode(" | ", $db_error_info) : ($db_error_info ?: 'Unknown')));
            }

            if ($attributesData !== null && is_array($attributesData)) { 
                $this->db->query("DELETE FROM product_variation_attributes WHERE variation_id = :variation_id");
                $this->db->bind(':variation_id', $variation_id);
                $this->db->execute();

                if (!empty($attributesData)) {
                    $this->db->query("INSERT INTO product_variation_attributes (variation_id, attribute_id, attribute_value_id) 
                                      VALUES (:variation_id, :attribute_id, :attribute_value_id)");
                    foreach ($attributesData as $attribute_id => $attribute_value_id) {
                         if (is_numeric($attribute_id) && is_numeric($attribute_value_id)) {
                            $this->db->bind(':variation_id', $variation_id);
                            $this->db->bind(':attribute_id', (int)$attribute_id);
                            $this->db->bind(':attribute_value_id', (int)$attribute_value_id);
                            if (!$this->db->execute()) {
                                throw new Exception("Failed to update product_variation_attribute links.");
                            }
                        }
                    }
                }
            }

            if (method_exists($this->db, 'commit')) $this->db->commit();
            return true;
        } catch (Exception $e) {
            if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
            error_log("Error in ProductAttributeModel::updateVariation: " . $e->getMessage());
            if ($e->getCode() === 23001 || ($e->getCode() === '23000' && strpos(strtolower($e->getMessage()), 'duplicate entry') !== false && strpos(strtolower($e->getMessage()), 'sku') !== false)) {
                 return 'duplicate_sku';
            }
            return $e->getCode() === '23000' ? 'db_error_duplicate' : 'general_exception';
        }
    }

    public function deleteVariation($variation_id) {
        $variation_id = (int)$variation_id;
        if (method_exists($this->db, 'beginTransaction')) {
            $this->db->beginTransaction();
        }
        try {
            $this->db->query("DELETE FROM product_variation_attributes WHERE variation_id = :variation_id");
            $this->db->bind(':variation_id', $variation_id);
            $this->db->execute();

            $this->db->query("DELETE FROM product_variations WHERE id = :variation_id");
            $this->db->bind(':variation_id', $variation_id);
            if (!$this->db->execute()) {
                throw new Exception("Failed to delete product_variation.");
            }

            if (method_exists($this->db, 'commit')) $this->db->commit();
            return true;
        } catch (Exception $e) {
            if (method_exists($this->db, 'rollBack')) $this->db->rollBack();
            error_log("Error in ProductAttributeModel::deleteVariation: " . $e->getMessage());
            return false;
        }
    }
    
    public function decreaseVariationStock($variation_id, $quantity_to_decrease) {
        $variation_id = (int)$variation_id;
        $quantity_to_decrease = (int)$quantity_to_decrease;

        error_log("ProductAttributeModel::decreaseVariationStock for variation_id: {$variation_id}, quantity: {$quantity_to_decrease}");

        $this->db->query("SELECT stock_quantity FROM product_variations WHERE id = :id");
        $this->db->bind(':id', $variation_id);
        $variation_stock_data = $this->db->single();

        if ($variation_stock_data && (int)$variation_stock_data['stock_quantity'] >= $quantity_to_decrease) {
            $this->db->query("UPDATE product_variations SET stock_quantity = stock_quantity - :quantity 
                              WHERE id = :id AND stock_quantity >= :quantity_check");
            $this->db->bind(':quantity', $quantity_to_decrease);
            $this->db->bind(':id', $variation_id);
            $this->db->bind(':quantity_check', $quantity_to_decrease); 
            
            if ($this->db->execute()) {
                error_log("ProductAttributeModel::decreaseVariationStock - UPDATE executed. Rows affected: " . $this->db->rowCount() . " for variation_id: {$variation_id}");
                return $this->db->rowCount() > 0;
            } else {
                 $db_error_info = $this->db->getErrorInfo();
                 error_log("ProductAttributeModel::decreaseVariationStock - UPDATE failed for variation_id: {$variation_id}. DB Error: " . (is_array($db_error_info) ? implode(" | ", $db_error_info) : ($db_error_info ?: 'Unknown DB error')));
            }
        } else {
            if (!$variation_stock_data) {
                error_log("ProductAttributeModel::decreaseVariationStock - Variation not found for id: {$variation_id}");
            } else {
                error_log("ProductAttributeModel::decreaseVariationStock - Not enough stock for variation_id: {$variation_id}. Current: {$variation_stock_data['stock_quantity']}, Requested: {$quantity_to_decrease}");
            }
        }
        return false;
    }
}
?>