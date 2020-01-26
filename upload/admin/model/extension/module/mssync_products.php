<?php

class ModelExtensionModuleMssyncProducts extends Model {
	
    /**
     * 
     * @param int $product_id
     * @return void
     * 
     * ver 1.0
     * rev 30.08.18
     * anatoliy.iwanov@yandex.ru
     */
    public function deleteProductUUID($product_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "mssync_product_uuid "
                . "WHERE id_product = " . (int) $product_id);
    }

    /**
     * 
     * @param $uuid string 
     * @return int
     * 
     * ver 1.1
     * rev 14.04.17
     * anatoliy.iwanov@yandex.ru
     */
    public function getProductIdByUuid($uuid) {

        $product_id = 0;

        $res = $this->db->query("SELECT id_product "
                . "FROM " . DB_PREFIX . "mssync_product_uuid "
                . "WHERE uuid_product = '" . $this->db->escape($uuid) . "'");

        if ($res->num_rows > 0) {
            $product_id = $res->row["id_product"];
        }

        return (int) $product_id;
    }

    /**
     * 
     * For success good creation in MS, each of it must have a unique code
     * 
     * @param array $data
     * @param int $product_id
     * @return boolean
     * 
     * ver 2.0.1.1
     * rev 12.12.18
     * anatoliy.iwanov@yandex.ru
     */
    public function addProduct($data) {

        $this->load->language("extension/module/mssync");

        $log = $this->language->get("method_start") . " addProduct\n";
        
        $product_id = (int) $data["product_id"];
        
        if ($product_id <= 0) {
            $log .= "bad product id\n";
            $log .= $this->language->get("method_end") . " addProduct\n";
            $this->model_extension_module_mssync_utils->writeToLog($log);
            return false;
        }
        
        $new_prod_data = array();
        $new_prod_data["shared"] = false;
        $new_prod_data["name"] = $data["name"];
        $new_prod_data["description"] = $data["description"];
        $new_prod_data["code"] = (strlen($data["sku"]) > 0) ? $data["sku"] : (string) $product_id;
        $new_prod_data["archived"] = false;
        $new_prod_data["salePrices"] = array(
            array(
                "value" => $this->model_extension_module_mssync_utils->sitePricePrepareForMs($data["price"]),
                "currency" => array(
                    "meta" => array(
                        "href" => "https://online.moysklad.ru/api/remap/1.1/entity/currency/33718866-fd7b-11e8-9107-5048000a41fb",
                        "metadataHref" => "https://online.moysklad.ru/api/remap/1.1/entity/currency/metadata",
                        "type" => "currency",
                        "mediaType" => "application/json"
                    )
                ),
                "priceType" => "Цена продажи"
            )    
        );
        
        $new_prod_data["quantity"] = (float) $data["quantity"];
        $new_prod_data["weighed"] = false;
        $new_prod_data["weight"] = (float) $data["weight"];

        $json = json_encode($new_prod_data);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->model_extension_module_mssync_utils->jsonErrorHandler($log, __LINE__, json_last_error(), json_last_error_msg(), "e");
            return false;
        }

        $curl_res = $this->model_extension_module_mssync_utils->curlCustomRequest(
                $this->config->get("mssync_api_url") . "/entity/product", $this->config->get("mssync_login"), $this->config->get("mssync_password"), $json, "POST");

        $ms_res = $this->model_extension_module_mssync_utils->getJson($curl_res, $log, __LINE__, "d");

        if (isset($ms_res["error"])) {
            return false;
        }

        $this->setProductUUID($product_id, $ms_res["id"]);

        $log .= $this->language->get("method_end") . " addProduct\n";
        $this->model_extension_module_mssync_utils->writeToLog($log);
        return true;
    }
    
    public function getAllProductUUIDS(){
        $res = $this->db->query("SELECT * FROM " . DB_PREFIX . "mssync_product_uuid");
        
        if($res->num_rows > 0){
            return $res->rows;
        }
        
        return array();
    }
    
    /**
     * @param $product_uuid string 
     * @return int
     * 
     * ver 2.1.2.4
     * rev 26.09.18
     * anatoliy.iwanov@yandex.ru
     */
    public function syncProduct($product_uuid) {

        $this->load->model("extension/module/mssync_utils");

        $log = $this->language->get("method_start") . " syncProduct\n";

        $product_id = $this->getProductIdByUuid($product_uuid);
        if ($product_id > 0) {
            $log .= (int) $product_id . ";" . $this->language->get("log_product_already_synced") . "\n";
            $log .= $this->language->get("method_end") . " syncProduct\n";
            $this->model_extension_module_mssync_utils->writeToLog($log);
            return $product_id;
        }

        $this->load->model("catalog/product");
        $this->load->model("tool/image");

        $ms_login = $this->config->get("mssync_login");
        $ms_password = $this->config->get("mssync_password");
        $api_url = $this->config->get("mssync_api_url");

        $product_curl_res = $this->model_extension_module_mssync_utils->curlGet($api_url . "/entity/product/" . $product_uuid, $ms_login, $ms_password);

        $ms_product = $this->model_extension_module_mssync_utils->getJson($product_curl_res, $log, __LINE__, "d");

        if (isset($ms_product["error"])) {
            return 0;
        }

        $current_product_quantity = (isset($ms_product["packs"][0]["quantity"])) ? (float) $ms_product["packs"][0]["quantity"] : 0.0;
        $stock_text = ($current_product_quantity > 0) ? $this->language->get("text_in_stock") : $this->language->get("text_out_of_stock");
        $stock_status_id = $this->model_extension_module_mssync_utils->selectStockStatusIdByName($stock_text);

        $product_price = $this->model_extension_module_mssync_utils->msPricePrepareForDb($this->getProductCurrentPrice($ms_product["salePrices"]));

        $product_measure = (isset($ms_product["uom"]["meta"]["href"])) ? $this->getProductMeasure($ms_product["uom"]["meta"]["href"], $ms_login, $ms_password) : $this->language->get("text_common_measure");
        $tax_class_id = 0;
        $product_status = 1;

        if ($current_product_quantity <= 0 && $this->config->get("mssync_sync_show_zero_quantity_products") == 0) {

            $product_status = 0;
        }

        $product['model'] = $ms_product["name"];
        $product['sku'] = $ms_product["code"];
        $product['upc'] = "";
        $product['ean'] = "";
        $product['jan'] = "";
        $product['isbn'] = "";
        $product['mpn'] = "";
        $product['location'] = "";
        $product['quantity'] = (float) $current_product_quantity;
        $product['minimum'] = 0;
        $product['subtract'] = 1;
        $product['stock_status_id'] = (int) $stock_status_id;
        $product['date_available'] = "";
        $product['manufacturer_id'] = 0;
        $product['shipping'] = 0;
        $product['price'] = (float) $product_price;
        $product['points'] = 0;
        $product['weight'] = (float) $ms_product["weight"];
        $product ['weight_class_id'] = $this->selectWeightClassIdByValue(($product_measure === "гр") ? 1000.00 : 1.00);
        $product['length'] = 0;
        $product['width'] = 0;
        $product['height'] = 0;
        $product['length_class_id'] = 0;
        $product['status'] = $product_status;
        $product['tax_class_id'] = (int) $tax_class_id;
        $product['sort_order'] = 1;
        $product['product_description'] = array(
            (int) $this->config->get("config_language_id") => array(
                "name" => $ms_product['name'],
                "description" => (isset($ms_product["description"])) ? $ms_product["description"] : $this->language->get("text_without_description"),
                "tag" => $ms_product['name'],
                "meta_title" => "",
                "meta_description" => "",
                "meta_keyword" => $ms_product['name']
        ));
        $product["type_id"] = 0;

        $image_path_to_db = $this->config->get("mssync_product_image_dir_to_db");

        if (isset($ms_product["image"])) {
            $upload_res = $this->model_extension_module_mssync_utils->uploadImageFromMs($ms_product["image"]["filename"], $ms_product["image"]["meta"]["href"], DIR_IMAGE);

            if (is_array($upload_res)) {
                $log .= "--- " . $this->language->get("error_upload_img_from_ms") . " ---\n";
                $this->model_extension_module_mssync_utils->curlErrorHandler($upload_res["error"], $log, __LINE__);
            } else {
                $product["image"] = $image_path_to_db . $ms_product["image"]["filename"];
                $this->model_tool_image->resize($upload_res, 100, 100);
                $product["product_image"] [] = array(
                    "image" => $image_path_to_db . $ms_product["image"]["filename"],
                    "sort_order" => 0
                );
            }
        }

        $product_id = $this->model_catalog_product->addProduct($product);
        $this->setProductUUID($product_id, $product_uuid);

        $log .= "--- " . $this->language->get("log_product_added") . " ---\n";
        $log .= "--- ID: " . (int) $product_id . " ---\n";
        $log .= "--- UUID: " . (string) $product_uuid . " ---\n";


        $log .= $this->language->get("method_end") . " syncProduct\n";
        $this->model_extension_module_mssync_utils->writeToLog($log);
        return (int) $product_id;
    }

    /**
     * Пытается синхронизировать продукты в направлении МС -> сайт
     * 
     * @param boolean $all флаг указывающий синхронизировать все товары
     * 
     * @return boolean
     *
     * ver 2.0.11.3
     * rev 26.09.18
     * anatoliy.iwanov@yandex.ru
     */
    public function syncProducts($all = true) {

        $this->load->model("extension/module/mssync_utils");
        $this->load->model("tool/image");

        $log = "--- " . date("d-F-o G:i:s") . $this->language->get("method_start") . " syncProducts \n";

        $ms_login = $this->config->get("mssync_login");
        $ms_password = $this->config->get("mssync_password");

        $edited_products = 0;
        $added_products = 0;

        $sync_state_info = $this->model_extension_module_mssync_utils->getSyncStateInfo("sync-products", $all);

        $limit = 100; //!
        $offset = 0; //!

        $url = $this->config->get("mssync_api_url") . "/entity/product?limit=" . $limit .
                "&offset=" . $offset . $sync_state_info["sync_date"];

        $curl_get_result = $this->model_extension_module_mssync_utils->curlGet($url, $ms_login, $ms_password);

        $ms_goods = $this->model_extension_module_mssync_utils->getJson($curl_get_result, $log, __LINE__, "d");

        if (isset($ms_goods["error"])) {
            return false;
        }

        $count_ms_goods = count($ms_goods["rows"]);
        
        if ($count_ms_goods > 0) {

            foreach ($ms_goods["rows"] as $ms_good) {

                if ($ms_good["archived"]) {
                    continue;
                }
                
                if ($this->getProductIdByUuid($ms_good["id"]) > 0) {
                    continue;
                }

                $product = array();
                $product_status = $this->config->get("mssync_sync_show_zero_quantity_products");
                $stock_text = $this->language->get("text_out_of_stock");

                $stock_status_id = $this->model_extension_module_mssync_utils->selectStockStatusIdByName($stock_text);

                $product_price = $this->model_extension_module_mssync_utils->msPricePrepareForDb($this->getProductCurrentPrice($ms_good["salePrices"]));

                $product_measure = (isset($ms_good["uom"]["meta"]["href"])) ? $this->getProductMeasure($ms_good["uom"]["meta"]["href"], $ms_login, $ms_password) : "шт";
                $tax_class_id = 0;

                $product['model'] = $ms_good["name"];
                $product['sku'] = $ms_good["code"];
                $product['upc'] = "";
                $product['ean'] = "";
                $product['jan'] = "";
                $product['isbn'] = "";
                $product['mpn'] = "";
                $product['location'] = "";
                $product['quantity'] = 0.0;
                $product['minimum'] = 0;
                $product['subtract'] = 1;
                $product['stock_status_id'] = $stock_status_id;
                $product['date_available'] = "";
                $product['manufacturer_id'] = 0;
                $product['shipping'] = 0;
                $product['price'] = (float) $product_price;
                $product['points'] = 0;
                $product['weight'] = (float) $ms_good["weight"];
                $product['weight_class_id'] = $this->selectWeightClassIdByValue(($product_measure === "гр") ? 1000.00 : 1.00);
                $product['length'] = 0;
                $product['width'] = 0;
                $product['height'] = 0;
                $product['length_class_id'] = 0;
                $product['status'] = $product_status;
                $product['tax_class_id'] = (int) $tax_class_id;
                $product['sort_order'] = 1;
                $product['product_store'][] = 0;
                $product['product_description'] = array(
                    (int) $this->config->get("config_language_id") => array(
                        "name" => $ms_good['name'],
                        "description" => (isset($ms_good["description"])) ? $ms_good["description"] : $this->language->get("text_without_description"),
                        "tag" => $ms_good['name'],
                        "meta_title" => $ms_good['name'],
                        "meta_description" => "",
                        "meta_keyword" => $ms_good['name']
                ));
                $product["type_id"] = 0;

                if (isset($ms_good["image"])) {

                    $upload_res = $this->model_extension_module_mssync_utils->uploadImageFromMs($ms_good["image"]["filename"], $ms_good["image"]["meta"]["href"]);

                    if (is_array($upload_res)) {
                        $log .= "--- " . $this->language->get("error_upload_img_from_ms") . " ---\n";
                        $this->model_extension_module_mssync_utils->curlErrorHandler($upload_res["error"], $log, __LINE__);
                    } else {
                        $product["image"] = $this->config->get("product_image_dir_to_db") . $ms_good["image"]["filename"];
                        $this->model_tool_image->resize($upload_res, 100, 100);
                    }

                    $product["product_image"][] = array(
                        "image" => $this->config->get("product_image_dir_to_db") . $ms_good["image"]["filename"],
                        "sort_order" => 0
                    );
                }

                $product_id = $this->model_catalog_product->addProduct($product);

                $this->setProductUUID($product_id, $ms_good["id"]);

                $added_products++;
                $log .= "--- " . $this->language->get("log_product_added") . " ---\n";
                $log .= "--- ID: " . $product_id . " ---\n";
                $log .= "--- UUID: " . $ms_good["id"] . " ---\n";
            }

            if ($count_ms_goods > $limit) {
                $offset += $limit;
                $this->model_extension_module_mssync_utils->setSyncStateInfo("sync-products", "process", $offset, $limit);
            } else {

                $offset = 0;
                $this->model_extension_module_mssync_utils->setSyncStateInfo("sync-products", "done", 0, $limit);
            }
        } else {
            $offset = 0;
            $this->model_extension_module_mssync_utils->setSyncStateInfo("sync-products", "done", 0, $limit);
        }

        $log .= "total products added: " . $added_products . "\n";
        $log .= "total products edited: " . $edited_products . "\n";
        $log .= "--- " . $this->language->get("method_end") . " syncProducts\n";
        $this->model_extension_module_mssync_utils->writeToLog($log);
        return true;
    }

    /**
     * 
     * @param boolean $all
     * @return boolean
     * 
     * ver 2.0.2.5
     * rev 02.10.18
     * anatoliy.iwanov@yandex.ru
     */
    public function syncAssortment($all = true) {

        $this->load->model("catalog/product");
        $this->load->model("extension/module/mssync_utils");

        $log = "--- " . date("d-F-o G:i:s") . " ---" . $this->language->get("method_start") . " syncAssortment\n";

        $ms_login = $this->config->get("mssync_login");
        $ms_password = $this->config->get("mssync_password");

        $sync_state_info = $this->model_extension_module_mssync_utils->getSyncStateInfo("sync-assortment", $all);

        $limit = $sync_state_info["limit"];
        $offset = $sync_state_info["offset"];

        $store_url = $this->config->get("mssync_api_url") . "/entity/store/";

        $url = $this->config->get("mssync_api_url") .
                "/entity/assortment?limit=" . $limit . "&offset=" . $offset .
                "&stockstore=" . $store_url . $this->config->get("mssync_store_uuid") . "&scope=product";

        $assortment_res = $this->model_extension_module_mssync_utils->curlGet($url, $ms_login, $ms_password);

        $assortment = $this->model_extension_module_mssync_utils->getJson($assortment_res, $log, __LINE__, "d");

        if (isset($assortment["error"])) {
            return false;
        }

        $count_assortment = count($assortment["rows"]);

        if ($count_assortment > 0) {

            foreach ($assortment["rows"] as $product) {

                if ($product["archived"]) {
                    continue;
                }

                $product_uuid = $product["id"];

                $product_id = $this->getProductIdByUuid($product_uuid);

                if ($product_id <= 0) {
                    $product_id = $this->syncProduct($product_uuid);
                }

                if ($product_id <= 0) {
                    $log .= "--- " . date("d-F-o G:i:s") . " ---" . $this->language->get("log_bad_product_sync") . $product_uuid . "\n";
                    continue;
                }

                $product_quantity_from_db = $this->getProductQuantity($product_id);

                if ($product["quantity"] == $product_quantity_from_db) {
                    continue;
                }

                $product_status = 1;

                if ($product["quantity"] > 0) {
                    $stock_text = $this->language->get("text_in_stock");
                } else {
                    $stock_text = $this->language->get("text_out_of_stock");

                    if ($this->config->get("mssync_sync_show_zero_quantity_products") == 0) {

                        $product_status = 0;
                    }
                }

                $stock_status_id = $this->model_extension_module_mssync_utils->selectStockStatusIdByName($stock_text);

                $product_price = $this->model_extension_module_mssync_utils->msPricePrepareForDb($this->getProductCurrentPrice($product["salePrices"]));

                $product_from_db = $this->model_catalog_product->getProduct($product_id);

                $product_from_db["name"] = $product["name"];
                $product_from_db["sku"] = (isset($product["code"])) ? $product["code"] : $this->getProductSku($product_id);
                $product_from_db["quantity"] = $product["quantity"];
                $product_from_db["stock_status_id"] = $stock_status_id;
                $product_from_db["price"] = $product_price;
                $product_from_db["weight"] = $product["weight"];
                $product_from_db["status"] = $product_status;
                $product_from_db["product_store"][] = 0;
                $product_from_db["manufacturer_id"] = $this->getProductManufacturerId($product_id);
                $product_from_db["product_special"] = $this->model_catalog_product->getProductSpecials($product_id);
                $product_from_db["product_option"] = $this->model_catalog_product->getProductOptions($product_id);
                $product_from_db["product_discount"] = $this->model_catalog_product->getProductDiscounts($product_id);
                $product_from_db["product_image"] = $this->model_catalog_product->getProductImages($product_id);
                $product_from_db["product_download"] = $this->model_catalog_product->getProductDownloads($product_id);
                $product_from_db["product_category"] = $this->model_catalog_product->getProductCategories($product_id);
                $product_from_db["product_filter"] = $this->model_catalog_product->getProductFilters($product_id);
                $product_from_db["product_related"] = $this->model_catalog_product->getProductRelated($product_id);
                $product_from_db["product_reward"] = $this->model_catalog_product->getProductRewards($product_id);
                $product_from_db["product_layout"] = $this->model_catalog_product->getProductLayouts($product_id);
                $product_from_db["product_recurring"] = $this->model_catalog_product->getRecurrings($product_id);
                $product_from_db["product_description"] = $this->model_catalog_product->getProductDescriptions($product_id);
                $product_attributes = $this->model_catalog_product->getProductAttributes($product_id);

                if (count($product_attributes) > 0) {
                    $product_from_db["product_attribute"] = $product_attributes;
                }

                $this->model_catalog_product->editProduct($product_id, $product_from_db);

                $log .= $product_id . "; ";
                $log .= "--- " . $this->language->get("log_product_edited") . " ---\n";
            }

            if ($count_assortment > $limit) {
                $offset += $limit;
                $this->model_extension_module_mssync_utils->setSyncStateInfo("sync-assortment", "process", $offset, $limit);
                $log .= "--- " . date("d-F-o G:i:s") . " ---" . $this->language->get("method_end") . " syncAssortment\n";
                $this->model_extension_module_mssync_utils->writeToLog($log);
                return true;
            }
        }

        $this->model_extension_module_mssync_utils->setSyncStateInfo("sync-assortment", "done", 0, $limit);

        $log .= "--- " . date("d-F-o G:i:s") . " ---" . $this->language->get("method_end") . " syncAssortment\n";
        $this->model_extension_module_mssync_utils->writeToLog($log);
        return true;
    }

    /**
     * @param array $sale_prices 
     * @return float
     * 
     * ver 2.0.1
     * rev 30.08.18
     * anatoliy.iwanov@yandex.ru
     */
    private function getProductCurrentPrice($sale_prices) {

        foreach ($sale_prices as $price) {
            if ($price["priceType"] === $this->config->get("mssync_current_sale_price_name")) {
                return (float) $price["value"];
            }
        }

        return (float) $sale_prices[0]["value"];
    }

    /**
     * 
     * @param $href string 
     * @return string
     * 
     * ver 2.1.1.0
     * rev 20.09.18
     * anatoliy.iwanov@yandex.ru
     */
    private function getProductMeasure($href, $ms_login, $ms_password) {

        $log = $this->language->get("method_start") . " getProductMeasure\n";

        $curl_res = $this->model_extension_module_mssync_utils->curlGet($href, $ms_login, $ms_password);

        $ms_res = $this->model_extension_module_mssync_utils->getJson($curl_res, $log, __LINE__, "d");

        if (isset($ms_res["error"])) {
            return "";
        }

        $log .= $this->language->get("method_end") . " getProductMeasure\n";
        $this->model_extension_module_mssync_utils->writeToLog($log);

        return $ms_res["name"];
    }

    /**
     * @param float $value
     * @return int
     * 
     * ver 1.1
     * rev 30.03.17
     * anatoliy.iwanov@yandex.ru
     */
    private function selectWeightClassIdByValue($value) {

        $weight_class_id = 0;

        $query = $this->db->query("SELECT `weight_class_id` "
                . "FROM " . DB_PREFIX . "weight_class " . "WHERE `value` = " . (float) $value);
        if ($query->num_rows > 0) {
            $weight_class_id = $query->row["weight_class_id"];
        }

        return $weight_class_id;
    }

    /**
     * 
     * @param $product_id int 
     * @param $product_uuid string 
     * @return int
     * 
     * ver 1.2
     * rev 16.04.17
     * anatoliy.iwanov@yandex.ru
     */
    private function setProductUUID($product_id, $product_uuid) {
        $this->db->query("INSERT "
                . "INTO " . DB_PREFIX . "mssync_product_uuid 
                    (id_product, uuid_product, sync_date) 
                    VALUES (" . (int) $product_id . ", "
                . "'" . $this->db->escape($product_uuid) . "', NOW())");

        return (int) $this->db->getLastId();
    }

    /**
     * 
     * @param int $product_id
     * @return float
     * 
     * ver 1.2.1.0
     * rev 02.10.18
     * anatoliy.iwanov@yandex.ru
     */
    private function getProductQuantity($product_id) {

        $res = $this->db->query("SELECT quantity "
                . "FROM " . DB_PREFIX . "product " . "WHERE product_id = " . (int) $product_id);

        if ($res->num_rows > 0) {

            return $res->row["quantity"];
        }

        return 0;
    }

    /**
     * 
     * @param int $product_id
     * @return string
     * 
     * ver 1.2
     * rev 30.08.18
     * anatoliy.iwanov@yandex.ru
     */
    private function getProductSku($product_id) {

        $res = $this->db->query("SELECT sku "
                . "FROM " . DB_PREFIX . "product " . "WHERE product_id = " . (int) $product_id);

        if ($res->num_rows > 0) {

            return $res->row["sku"];
        }

        return "";
    }

    /**
     * 
     * @param int $product_id
     * @return int
     * 
     * ver 2.0
     * rev 30.08.18
     * anatoliy.iwanov@yandex.ru
     */
    private function getProductManufacturerId($product_id) {

        $res = $this->db->query("SELECT manufacturer_id "
                . "FROM " . DB_PREFIX . "product "
                . "WHERE product_id = " . (int) $product_id);

        if ($res->num_rows > 0) {
            return (int) $res->row["manufacturer_id"];
        }

        return 0;
    }
}
