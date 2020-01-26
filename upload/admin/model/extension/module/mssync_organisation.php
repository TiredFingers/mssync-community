<?php

class ModelExtensionModuleMssyncOrganisation extends Model {

    /**
     * @return boolean
     * 
     * ver 2.0.5.2
     * rev 02.10.18
     * anatoliy.ivanow@yandex.ru
     */
    public function getOrganizationUUID() {

        $log = $this->language->get("method_start") . " getOrganizationUUID\n";

        $this->load->model("extension/module/mssync_utils");
                
        $curl_res = $this->model_extension_module_mssync_utils->curlGet($this->config->get("mssync_api_url") . "/entity/organization", $this->config->get("mssync_login"), $this->config->get("mssync_password"));
                
        $organisations = $this->model_extension_module_mssync_utils->getJson($curl_res, $log, __LINE__);

        if (isset($organisations["error"])) {
            return array();
        }

        $count_organizations = count($organisations["rows"]);

        $log .= $this->language->get("method_end") . "getOrganizationUUID\n";
        
        $this->model_extension_module_mssync_utils->writeToLog($log);

        if ($count_organizations == 1) {
            return array(
                0 => array(
                    "id" => $organisations["rows"][0]["id"],
                    "name" => $organisations["rows"][0]["name"])
                );
        } else if ($count_organizations == 0) {
            return array();
        } else {
            return $this->model_extension_module_mssync_utils->createList($organisations);
        }
    }

    /**

     * @return array 
     * 
     * ver 2.1.5.3
     * rev 04.10.2018
     * anatoliy.iwanov@yandex.ru
     */
    public function getStoreUUID() {
        
        $log = $this->language->get("method_start") . " getStoreUUID\n";

        $curl_res = $this->model_extension_module_mssync_utils->curlGet($this->config->get("mssync_api_url") . "/entity/store", $this->config->get("mssync_login"), $this->config->get("mssync_password"));
        
        $stores = $this->model_extension_module_mssync_utils->getJson($curl_res, 
                $log, __LINE__);
        
        if(isset($stores["error"])){
            return array();
        }
        
        $count_stores = count($stores["rows"]);

        if ($count_stores <= 0) {
            $log .= $this->language->get("log_no_stores") . $count_stores . "\n";
            $this->model_extension_module_mssync_utils->writeToLog($log);
            return $stores;
        }
        
        if ($count_stores == 1) {
            return array(
                0 => array(
                    "id" => $stores["rows"][0]["id"],
                    "name" => $stores["rows"][0]["name"]
                )
            );
        } else if ($count_stores == 0) {
            return array();
        } else {
            return $this->model_extension_module_mssync_utils->createList($stores);
        }

        $log .= $this->language->get("log_no_stores") . "\n";
        $this->model_extension_module_mssync_utils->writeToLog($log);
        return $stores;
    }

}
