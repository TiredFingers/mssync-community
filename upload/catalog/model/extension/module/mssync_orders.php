<?php

class ModelExtensionModuleMssyncOrders extends Model{
    
    /**
     * 
     * Opencart 3.0.2.0 core function from catalog/model/order
     * 
     * @param int $order_id
     * @return array
     */
    public function getOrderProducts($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
		
		return $query->rows;
    }
    
    /**
     * 
     * @param array $order
     * @param string $counterparty_uuid
     * @return boolean
     * 
     * ver 2.0.5.6
     * rev 01.10.18
     * anatoliy.iwanov@yandex.ru
     */
    public function createCustomerOrder($order, $counterparty_uuid) {
        
        $this->load->model("extension/module/mssync_utils");
        $this->load->model("extension/module/mssync_products");
        $this->load->language("extension/module/mssync");
        
        $log = "--- " . date("d-F-o G:i:s") . " --- " . $this->language->get("method_start") . " createCustomerOrder ---\n";
        
        if(strlen($counterparty_uuid) <= 0){
            $log .= $this->language->get("log_empty_cp_uuid") . "\n";
            $this->model_extension_module_mssync_utils->writeToLog($log);
            return false;
        }
        
        if ($this->getOrderSyncStatus($order["order_id"]) == "archived") {
            $log .= "--- " . $this->language->get("log_archived_order") . " ---\n";
            $this->model_extension_module_mssync_utils->writeToLog($log);
            return false;
        }
        
        $order_data_to_ms = array();
        $order_data_to_ms["code"] = (string) $order["order_id"];

        $order_data_to_ms["organization"]["meta"]["href"] = $this->config->get("mssync_api_url") . "/entity/organization/" . $this->config->get("mssync_organization_uuid");
        $order_data_to_ms["organization"]["meta"]["type"] = "organization";
        $order_data_to_ms["organization"]["meta"]["mediaType"] = "application/json";

        $order_data_to_ms["store"]["meta"]["href"] = $this->config->get("mssync_api_url") . "/entity/store/" . $this->config->get("mssync_store_uuid");
        $order_data_to_ms["store"]["meta"]["type"] = "store";
        $order_data_to_ms["store"]["meta"]["mediaType"] = "application/json";

        $order_data_to_ms["agent"]["meta"]["href"] = $this->config->get("mssync_api_url") . "/entity/counterparty/" . $counterparty_uuid;
        $order_data_to_ms["agent"]["meta"]["type"] = "counterparty";
        $order_data_to_ms["agent"]["meta"]["mediaType"] = "application/json";

        $order_data_to_ms["moment"] = date("Y-m-d G:i:s");
        $order_data_to_ms["applicable"] = false;
        $order_data_to_ms["vatEnabled"] = ($this->config->get("mssync_vat_included") == 1) ? true : false;

        $order_data_to_ms["state"]["meta"]["href"] = $this->config->get("mssync_api_url") . "/entity/customerorder/metadata/states/" . $this->config->get("mssync_new_order_state_uuid");
        $order_data_to_ms["state"]["meta"]["type"] = "state";
        $order_data_to_ms["state"]["meta"]["mediaType"] = "application/json";

        $order_data_to_ms["description"] = (string) $order["comment"];
        $order_data_to_ms["deliveryPlannedMoment"] = date("Y-m-d G:i:s");

        foreach ($order["products"] as $product) {

            $product_uuid = $this->model_extension_module_mssync_products->getProductUUID($product["product_id"]);

            if ($product_uuid == "") {
                $log .= "--- " . $this->language->get("error_products_add") . " " . $product["product_id"] . " ---\n";
                $this->model_extension_module_mssync_utils->writeToLog($log);
                return false;
            }

            $order_data_to_ms["positions"][] = array(
                "quantity" => (float) $product["quantity"],
                "price" => $this->model_extension_module_mssync_utils->sitePricePrepareForMs($product["price"]),
                "discount" => 0,
                "vat" => (int) $product["tax"],
                "assortment" => array(
                    "meta" => array(
                        "href" => $this->config->get("mssync_api_url") . "/entity/product/" . $product_uuid,
                        "type" => "product",
                        "mediType" => "application/json"
                    )
                )
            );
        }

        $json_data = json_encode($order_data_to_ms);

        if (json_last_error() != JSON_ERROR_NONE) {
            $this->model_extension_module_mssync_utils->jsonErrorHandler($log, 
                    __LINE__, json_last_error(), json_last_error_msg(), "e");
            return false;
        }

        $json_ms_order_result = $this->model_extension_module_mssync_utils->curlCustomRequest($this->config->get("mssync_api_url") . 
                "/entity/customerorder", $this->config->get("mssync_login"), $this->config->get("mssync_password"), $json_data, "POST");
        
        $order_result = $this->model_extension_module_mssync_utils->getJson($json_ms_order_result,
                $log, __LINE__, "d");
        
        if(isset($order_result["error"])){
            return false;
        }

        $this->setOrderSyncStatus($order["order_id"], $order_result["id"], "order");
        $this->setOrderUuid($order["order_id"], $order_result["id"]);

        $log .= "---" . date("d-F-o G:i:s") . "--- " . $this->language->get("method_end") . " createCustomerOrder ---\n";
        $this->model_extension_module_mssync_utils->writeToLog($log);
        return true;
    }
    
    /**
     * 
     * @param int $order_id
     * @return string
     * 
     * ver 1.0.1
     * rev 31.08.18
     * anatoliy.iwanov@yandex.ru
     */
    private function getOrderSyncStatus($order_id) {

        $res = $this->db->query("SELECT status "
                . "FROM " . DB_PREFIX . "mssync_sync_msorders "
                . "WHERE id_order = " . (int) $order_id);

        if ($res->num_rows > 0) {
            return (string) $res->row["status"];
        }

        return "";
    }
    
    /**
     * 
     * @param int $order_id
     * @param string $order_uuid
     * @param string $status
     * 
     * ver 1.1.1
     * rev 31.08.18
     * anatoliy.iwanov@yandex.ru
     */
    private function setOrderSyncStatus($order_id, $order_uuid, $status) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "mssync_sync_msorders "
                . "(id_order, uuid_order, status) "
                . "VALUES "
                . "(" . (int) $order_id . ", '" . $this->db->escape($order_uuid) . "', '" . $this->db->escape($status) . "')");
    }
    
    /**
     * 
     * @param int $order_id
     * @param string $uuid
     * 
     * ver 1.0
     * rev 31.08.18
     * anatoliy.iwanov@yandex.ru
     */
    private function setOrderUuid($order_id, $uuid) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "mssync_order_uuid "
                . "(id_order, uuid) "
                . "VALUES (" . (int) $order_id . ", '" . $this->db->escape($uuid) . "')");
    }
}