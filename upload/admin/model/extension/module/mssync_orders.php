<?php

class ModelExtensionModuleMssyncOrders extends Model{
    
    /**
     * 
     * @return array
     * 
     * ver 1.1.1.0
     * rev 02.10.18
     */
    public function getOrderStatusesListFromMs(){
        
        $this->load->model("extension/module/mssync_utils");
        
        $log = $this->language->get("method_start") . " getOrderStatusesListFromMs\n";
        
        $curl_res = $this->model_extension_module_mssync_utils->curlGet($this->config->get("mssync_api_url") . 
                "/entity/customerorder/metadata", 
                $this->config->get("mssync_login"),
                $this->config->get("mssync_password"));
        
        $json = $this->model_extension_module_mssync_utils->getJson($curl_res,
                $log, __LINE__, "d");
        
        if(isset($json["error"])){
            return $this->model_extension_module_mssync_utils->createList(array("rows" => array()));
        }
        
        $log .= $this->language->get("method_end") . " getOrderStatusesListFromMs\n";
        
        return $this->model_extension_module_mssync_utils->createList(array("rows" => $json["states"]));
        
    }

}