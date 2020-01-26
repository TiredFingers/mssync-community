<?php

class ModelExtensionModuleMssyncCustomers extends Model {

    /**
     * 
     * @param arr $data
     * @param string $mssync_login
     * @param string $mssync_password
     * @param int $user_id
     * @param string $text
     * @param string $counterPartyType
     * @return boolean
     * 
     * anatoliy.iwanov@yandex.ru
     * ver 2.1.2.0
     * rev 19.09.18
     * 
     */
    public function createCustomer($data, $cp_type = "individual") {

        $this->load->model("extension/module/mssync_utils");

        $log = $this->language->get("method_start") . " createCustomer\n";
        $fullname = $data["firstname"] . " " . $data["lastname"];

        // new cutomer data
        $cp_data = array();
        $cp_data["name"] = $fullname;
        $cp_data["code"] = "customer";
        $cp_data["email"] = $data["email"];
        $cp_data["phone"] = $data["telephone"];
        $cp_data["legalTitle"] = $fullname;
        $cp_data["archived"] = false;
        $cp_data["companyType"] = $cp_type;

        $json = json_encode($cp_data);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->model_extension_module_mssync_utils->jsonErrorHandler($log, __LINE__, json_last_error(), json_last_error_msg(), "e");
            return false;
        }
                
        $curl_res = $this->model_extension_module_mssync_utils->curlCustomRequest($this->config->get("mssync_api_url") . "/entity/counterparty", $this->config->get("mssync_login"), $this->config->get("mssync_password"), $json, "POST");       
        
        $ms_res = $this->model_extension_module_mssync_utils->getJson($curl_res, $log, __LINE__, "d");

        if (isset($ms_res["error"])) {
            return false;
        }
        
        $user_id = $this->getCustomerIdByEmail($data["email"]);
        
        if ($user_id > 0) {
            $this->setCustomerUUID($user_id, $ms_res["id"]);
        } else {
            $log .= "error while set up the customer uuid for " . $ms_res["id"] . "\n";
            $log .= $this->language->get("method_end") . " createCustomer\n";
            $this->model_extension_module_mssync_utils->writeToLog($log);
            return false;
        }

        $log .= $this->language->get("method_end") . " createCustomer\n";
        $this->model_extension_module_mssync_utils->writeToLog($log);
        return true;
    }
    
    /**
     * 
     * @param int $customer_id
     * 
     * ver 2.0
     * rev 30.08.18
     * anatoliy.iwanov@yande.ru
     */
    public function deleteCustomerUUID($customer_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "mssync_customer_uuid "
                . "WHERE customer_id = " . (int) $customer_id);
    }
    
    /**
     * 
     * @param int $customer_id
     * @param string $user_uuid
     * 
     * anatoliy.iwanov@yandex.ru
     * var 1.1.1
     * rev 30.08.18
     * 
     */
    private function setCustomerUUID($customer_id, $user_uuid) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "mssync_customer_uuid (customer_id, customer_uuid, sync_date) "
                . "VALUES(" . (int) $customer_id . ", '" . $this->db->escape($user_uuid) . "', NOW())");
    }
    
    /**
     * 
     * @param string $email
     * @return int
     * 
     * ver 1.2.3.1
     * rev 07.10.18
     * anatoliy.iwanov@yandex.ru
     */
    private function getCustomerIdByEmail($email) {

        $res = $this->db->query("SELECT customer_id "
                . " FROM " . DB_PREFIX . "customer "
                . "WHERE email = '" . $this->db->escape($email) . "'");

        if ($res->num_rows > 0) {
            return $res->row["customer_id"];
        }

        return 0;
    }
}
