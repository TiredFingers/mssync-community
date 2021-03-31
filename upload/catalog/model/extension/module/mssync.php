<?php

class ModelExtensionModuleMssync extends Model {

    /**
     * 
     * interface
     * 
     * @param array $order
     * @param string $counterparty_uuid
     * @param int $order_id
     * @return boolean
     * 
     * ver 2.0.3.5
     * rev 26.11.17
     * anatoliy.iwanov@yandex.ru
     */
    public function createCustomerOrder($order, $counterparty_uuid, $order_id) {

        $this->load->model("extension/module/mssync_orders");
        
        $this->model_extension_module_mssync_customers->createCustomerOrder($order, $counterparty_uuid, $order_id);
    }
    
    /**
     * 
     * interface
     * 
     * @param array $order
     * @param int $order_id
     */
    public function createShipment($order, $order_id){
        
        $this->load->model("extension/module/mssync_orders");
        $this->model_extension_module_mssync_orders->createShipment($order, $order_id);
    }
    
    /**
     * 
     * interface
     * 
     * @param array $data
     * @param string $mssync_login
     * @param string $mssync_password
     * @param int $customer_id
     * @param string $customer_type
     * @return void
     * 
     * ver 1.0
     * rev 31.08.18
     */
    public function updateCustomer($data, $mssync_login, $mssync_password, 
            $customer_id, $customer_type = "individual"){
        
        $this->load->model("extension/module/mssync_customers");
        $this->model_extension_module_mssync_customers->updateCustomer($data,
                $mssync_login, $mssync_password, $customer_id, $customer_type);
    }
    
    /**
     * 
     * interface
     * 
     * @param string $cp_uuid
     * @return float
     * 
     * ver 0.2
     * rev 22.03.18
     * anatoliy.iwanov@yandex.ru
     */
    public function getCustomerSalesAmount($cp_uuid){
        
        $this->load->model("extension/module/mssync_customers");
        return $this->model_extension_module_mssync_customers->getCustomerSalesAmount($cp_uuid);
    }
    
    /**
     * interface
     * 
     * @param aray $data
     * @param int $customer_id
     * @param string $customer_description
     * @param string $customer_type
     * 
     * ver 1.1.5
     * rev 31.08.18
     * anatoliy.iwanov@yandex.ru
     */
    public function createCustomer($data, $customer_id, $customer_description = "", $customer_type = "individual"){
        
        $this->load->model("extension/module/mssync_customers");
        $this->model_extension_module_mssync_customers->createCustomer($data, $customer_id,
                $customer_description, $customer_type);
    }

    /**
     * 
     * interface
     * 
     * @param string $order_phone
     * @return string
     * 
     * ver 2.2
     * rev 19.05.18
     * anatoliy.iwanov@yandex.ru
     */
    public function getCustomerUuidFromMsByPhone($order_phone){
        
        $this->load->model("extension/module/mssync_customers");
        return $this->model_extension_module_mssync_customers->getCustomerUuidFromMsByPhone($order_phone);
    }
    
    /**
     * 
     * interface
     * 
     * @param string $email
     * @return string
     */
    public function getCustomerUuidFromMsByEmail($email){
        
        $this->load->model("extension/module/mssync_customers");
        return $this->model_extension_module_mssync_customers->getCustomerUuidFromMsByEmail($email);
    }

    /**
     * 
     * interface
     * 
     * @param string $email
     * @return int
     * 
     * ver 1.0
     * rev 31.08.18
     * anatoliy.iwanov@yandex.ru
     */
    public function getCustomerIdByEmail($email){
        
        $this->load->model("extension/module/mssync_customers");
        return $this->model_extension_module_mssync_customers->getCustomerIdByEmail($email);
    }

    /**
     * 
     * interface
     * 
     * @param string $phone
     * @return int
     * 
     * ver 1.0
     * rev 31.08.18
     * 
     */
    public function getCustomerIdByPhone($phone){
        
        $this->load->model("extension/module/mssync_customers");
        return $this->model_extension_module_mssync_customers->getCustomerIdByPhone($phone);
    }

}