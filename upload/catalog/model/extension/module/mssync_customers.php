<?php

class ModelExtensionModuleMssyncCustomers extends Model {

    /**
     * 
     * @param array $data
     * @param string $counterPartyType
     * @return string
     * 
     * ver 2.2.5
     * rev 19.09.18
     * anatoliy.iwanov@yandex.ru
     */
    public function createCustomer($data, $cp_type = "individual") {

        $this->load->model("extension/module/mssync_utils");

        $log = $this->language->get("method_start") . " createCustomer\n";

        $fullname = $data["firstname"] . " " . $data["lastname"];

        // new cutomer data
        $cp_data = array();
        $cp_data["name"] = $fullname;
        $cp_data["email"] = $data["email"];
        $cp_data["phone"] = (string) $data["telephone"];
        $cp_data["legalTitle"] = $fullname;
        $cp_data["archived"] = false;
        $cp_data["companyType"] = $cp_type;

        $json = json_encode($cp_data);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->model_extension_module_mssync_utils->jsonErrorHandler($log, __LINE__, json_last_error(), json_last_error_msg(), "e");
            return "";
        }

        $curl_res = $this->model_extension_module_mssync_utils->curlCustomRequest(
                $this->config->get("mssync_api_url") . "/entity/counterparty", $this->config->get("mssync_login"), $this->config->get("mssync_password"), $json, "POST");

        $ms_res = $this->model_extension_module_mssync_utils->getJson($curl_res, $log, __LINE__, "d");

        if (isset($ms_res["error"])) {
            return "";
        }

        $user_id = $this->getCustomerIdByEmail($data["email"]);

        if ($user_id > 0) {
            $this->setCustomerUUID($user_id, $ms_res["id"]);
        } else {
            $log .= "error while set up the customer uuid for " . $ms_res["id"] . "\n";
            $log .= $this->language->get("method_end") . " createCustomer\n";
            $this->model_extension_module_mssync_utils->writeToLog($log);
            return "";
        }

        $log .= $this->language->get("method_end") . " createCustomer\n";
        $this->model_extension_module_mssync_utils->writeToLog($log);
        return $ms_res["id"];
    }

    /**
     * 
     * @return string
     * 
     * ver 1.2.0.0
     * rev 01.10.2018
     * anatoliy.iwanov@yandex.ru
     */
    public function newPassword() {

        $password_length = 8;
        $new_password = "";

        $letters = array("A", "a", "B", "b", "C", "c", "D", "d", "E", "e", "F",
            "f", "G", "g", "H", "h", "I", "i", "J", "j", "K", "k", "L", "l", "M",
            "m", "N", "n", "O", "o", "P", "p", "Q", "q", "R", "r", "S", "s",
            "T", "t", "U", "u", "V", "v", "W", "w", "X", "x", "Y", "y", "Z", "z");

        $all_symbols_in_letters = count($letters) - 1;
        $numbers_count = rand(1, 5);

        $letters_count_max = $password_length - $numbers_count;
        $letters_count = rand(5, $letters_count_max);

        for ($i = 0; $i < $numbers_count; $i ++) {
            $pass_numbers[] = rand(0, 9);
        }
        for ($i = 0; $i < $letters_count; $i++) {
            $rand_key = rand(0, $all_symbols_in_letters);
            $pass_letters[] = $letters[$rand_key];
        }

        $new_password_array = array_merge($pass_letters, $pass_numbers);

        shuffle($new_password_array);
        
        $count = count($new_password_array);

        for ($i = 0; $i < $count; $i++) {
            $new_password .= $new_password_array[$i];
        }

        return $new_password;
    }

    /**
     * 
     * @param string $email
     * @return int
     * 
     * ver 2.1
     * rev 31.08.18
     * anatoliy.iwanov@yandex.ru
     */
    public function getCustomerIdByEmail($email) {

        if (strlen($email) == 0) {
            return 0;
        }

        $res = $this->db->query("SELECT customer_id "
                . " FROM " . DB_PREFIX . "customer "
                . "WHERE email = '" . $this->db->escape($email) . "'");

        if ($res->num_rows > 0) {
            return (int) $res->row["customer_id"];
        }

        return 0;
    }
    
    /**
     * 
     * @param string $email
     * @return string
     * 
     * ver 1.0.0.1
     * rev 01.10.2018
     */
    public function getCustomerUUIDbyEmail($email) {

        $res = $this->db->query("SELECT " . DB_PREFIX . "mssync_customer_uuid.customer_uuid AS uuid "
                . "FROM " . DB_PREFIX . "mssync_customer_uuid "
                . "JOIN " . DB_PREFIX . "customer "
                . "ON " . DB_PREFIX . "mssync_customer_uuid.customer_id = " . DB_PREFIX . "customer.customer_id "
                . "WHERE " . DB_PREFIX . "customer.email = '" . $this->db->escape($email) . "'");

        if ($res->num_rows > 0) {
            return $res->row["uuid"];
        }

        return "";
    }

    /**
     * 
     * @param int $user_id
     * @param string $user_uuid
     * 
     * anatoliy.iwanov@yandex.ru
     * ver 2.0
     * rev 31.08.18
     */
    private function setCustomerUUID($user_id, $user_uuid) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "mssync_customer_uuid (customer_id, customer_uuid, sync_date) "
                . "VALUES(" . (int) $user_id . ", '" . $this->db->escape($user_uuid) . "', NOW())");
    }
}
