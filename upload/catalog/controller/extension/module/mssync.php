<?php

class ControllerExtensionModuleMssync extends Controller {

    //catalog/model/account/customer/addCustomer/after
    public function addCustomer(&$route, &$args, &$output) {

        if ($this->config->get("mssync_status")) {
            $this->load->model("extension/module/mssync_customers");
            $this->load->language("extension/module/mssync");

            $this->model_extension_module_mssync_customers->createCustomer($args[0]);
        }
    }

    //catalog/model/checkout/order/addOrderHistory/after
    public function addOrder(&$route, &$args, &$output) {
                
        if ($this->config->get("mssync_status")) {

            $this->load->model("extension/module/mssync_orders");
            $this->load->model("checkout/order");
            $this->load->model("extension/module/mssync_customers");
            $this->load->model("account/customer");
            
            $order = $this->model_checkout_order->getOrder($args[0]);
            
            $order["products"] = $this->model_extension_module_mssync_orders->getOrderProducts($args[0]);
            
            $customer = $this->model_account_customer->getCustomerByEmail($order["email"]);

            if (empty($customer)) {

                $new_cp_data = array();
                $new_cp_data["firstname"] = $order["firstname"];
                $new_cp_data["lastname"] = $order["firstname"];
                $new_cp_data["email"] = $order["email"];
                $new_cp_data["telephone"] = $order["telephone"];
                $new_cp_data["password"] = $this->model_extension_module_mssync_customers->newPassword();

                $this->model_account_customer->addCustomer($new_cp_data);
            }

            $customer_uuid = $this->model_extension_module_mssync_customers->getCustomerUUIDbyEmail($order["email"]);

            if (strlen($customer_uuid) <= 0) {

                $new_cp_data = array();
                $new_cp_data["firstname"] = $order["firstname"];
                $new_cp_data["lastname"] = $order["firstname"];
                $new_cp_data["email"] = $order["email"];
                $new_cp_data["telephone"] = $order["telephone"];

                $customer_uuid = $this->model_extension_module_mssync_customers->createCustomer($new_cp_data); // for MS
            }
        }
        
        $this->model_extension_module_mssync_orders->createCustomerOrder($order, $customer_uuid);
    }
}
