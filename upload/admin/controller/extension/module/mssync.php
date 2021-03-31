<?php

class ControllerExtensionModuleMssync extends Controller {

    private $error = [];

    public function index() {

        $data = array();

        $this->load->language("extension/module/mssync");

        if (($this->request->server["REQUEST_METHOD"] == "POST") && $this->validate()) {

            $this->load->model("setting/setting");

            $this->request->post["mssync_vat"] = (!isset($this->request->post["mssync_vat"])) ? "false" : "true";
            $this->request->post["mssync_sync_show_zero_quantity_products"] = (!isset($this->request->post["mssync_show_zero_qty_prod"])) ? 0 : 1;
            $this->request->post["mssync_password"] = ($this->request->post["mssync_password"] === "******") ? $this->config->get("mssync_password") : $this->request->post["mssync_password"];
                    
            $this->request->post = $this->initDefaultSettings($this->request->post);

            $this->model_setting_setting->editSetting("mssync", $this->request->post);
            $this->session->data["success"] = $this->language->get("text_success");
            $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=module', true));
        }

        $this->document->setTitle($this->language->get("heading_title"));

        $data["error_warning"] = (isset($this->error["warning"])) ? $this->error["warning"] : "";

        if ($this->config->get("mssync_status")) {
            $data["mssync_status"] = 1;
            $data["enabled_selected"] = "selected='selected'";
            $data["disabled_selected"] = "";
        } else {
            $data["mssync_status"] = 0;
            $data["enabled_selected"] = "";
            $data["disabled_selected"] = "selected='selected'";
        }

        $data["breadcrumbs"] = array();

        $data["breadcrumbs"][] = array(
            "text" => $this->language->get("text_home"),
            "href" => $this->url->link("common/dashboard", "token=" . $this->session->data["token"], true)
        );

        $data["breadcrumbs"][] = array(
            "text" => $this->language->get("text_extension"),
            "href" => $this->url->link("extension/extension", "token=" . $this->session->data["token"] . "&type=module", true)
        );

        $data["breadcrumbs"][] = array(
            "text" => $this->language->get("heading_title"),
            "href" => $this->url->link("extension/module/mssync", "token=" . $this->session->data["token"], true)
        );

        $data["action"] = $this->url->link("extension/module/mssync", "token=" . $this->session->data["token"], true);
        $data["cancel"] = $this->url->link("extension/extension", "token=" . $this->session->data["token"] . "&type=module", true);

        $data["heading_title"] = $this->language->get("heading_title");
        $data["button_save"] = $this->language->get("button_save");
        $data["button_cancel"] = $this->language->get("button_cancel");
        $data["text_edit"] = $this->language->get("text_edit");
        $data["entry_status"] = $this->language->get("entry_status");
        $data["text_enabled"] = $this->language->get("text_enabled");
        $data["text_disabled"] = $this->language->get("text_disabled");
        $data["text_form_login"] = $this->language->get("text_form_login");
        $data["text_form_password"] = $this->language->get("text_form_password");
        $data["text_form_organization"] = $this->language->get("text_form_organization_uuid");
        $data["text_form_vat_included"] = $this->language->get("text_form_vat_included");
        $data["text_form_store"] = $this->language->get("text_from_store_uuid");
        $data["text_form_sync_product"] = $this->language->get("text_form_sync_product");
        $data["sync_products_href"] = $this->url->link("extension/module/mssync/syncProducts", "token=" . $this->session->data["token"] . "&all_products=true", "SSL");
        $data["text_form_show_zero_qnt_prod"] = $this->language->get("text_form_show_zero_qnt_prod");
        $data["text_form_sync_assortment"] = $this->language->get("text_form_sync_assortment");
        $data["sync_assortment_href"] = $this->url->link("extension/module/mssync/syncAssortment", "token=" . $this->session->data["token"], "SSL");
        $data["text_actual_price"] = $this->language->get("text_actual_price");
        $data["help_mssync_login"] = $this->language->get('help_mssync_login');
        $data["help_mssync_password"] = $this->language->get('help_mssync_password');
        $data["help_mssync_organization"] = $this->language->get('help_mssync_organization');
        $data["help_mssync_store"] = $this->language->get('help_mssync_store');
        $data["entry_status"] = $this->language->get('entry_status');
        $data["text_form_state"] = $this->language->get("text_form_state");
        $data["help_mssync_state"] = $this->language->get("help_mssync_state");
        $data["text_project_uuid"] = $this->language->get("text_project_uuid");
        $data["text_form_sync"] = $this->language->get("text_form_sync");
        $data["text_form_add_products"] = $this->language->get("text_form_add_products");
        $data["add_products_href"] = $this->url->link("extension/module/mssync/addProductsToMs", "token=" . $this->session->data["token"], "SSL");
        
        $data["print_organizations"] = false;
        $data["print_stores"] = false;
        $data["mssync_organization_uuid"] = "";
        $data["mssync_store_uuid"] = "";

        $data["header"] = $this->load->controller("common/header");
        $data["column_left"] = $this->load->controller("common/column_left");
        $data["footer"] = $this->load->controller("common/footer");

        $data["mssync_current_sale_price_name"] = (isset($this->request->post["mssync_current_sale_price_name"])) ? $this->request->post["mssync_current_sale_price_name"] : $this->config->get("mssync_current_sale_price_name");

        $data["mssync_login"] = (isset($this->request->post["mssync_login"])) ? $this->request->post["mssync_login"] : $this->config->get("mssync_login");

        $data["mssync_password"] = (isset($this->request->post["mssync_password"])) ? $this->request->post["mssync_password"] : $this->config->get("mssync_password");

        if (isset($this->request->post["mssync_vat"]) || $this->config->get("mssync_vat_included") == "true") {

            $data["mssync_vat_in_sel"] = "checked";
        } else {
            $data["mssync_vat_in_sel"] = "";
        }

        if (strlen($this->config->get("mssync_login")) > 0 && strlen($this->config->get("mssync_password")) > 0) {

            $data["store_uuid_required"] = "required";
            $data["text_form_organization_name"] = $this->language->get("text_form_organization_name");

            if (strlen($this->config->get("mssync_organization_uuid")) <= 0 || strlen($this->config->get("mssync_store_uuid")) <= 0) {

                $this->load->model("extension/module/mssync_organisation");
            }

            if (strlen($this->config->get("mssync_organization_uuid")) <= 0) {

                $data["organization_uuid_readonly"] = "";
                $data["mssync_organization_uuid_list"] = $this->model_extension_module_mssync_organisation->getOrganizationUUID();
                
                $data["print_organizations"] = true;
                $data["count_organizations"] = count($data["mssync_organization_uuid_list"]);
            } else {
                $data["organization_uuid_readonly"] = "readonly";
                $data["mssync_organization_uuid"] = $this->config->get("mssync_organization_uuid");
                $data["print_organizations"] = false;
            }

            if (strlen($this->config->get("mssync_store_uuid")) <= 0) {

                $data["print_stores"] = true;
                $data["store_uuid_readonly"] = "";
                $data["mssync_store_uuid"] = "";
                $data["mssync_store_uuid_list"] = $this->model_extension_module_mssync_organisation->getStoreUUID();
                $data["count_stores"] = count($data["mssync_store_uuid_list"]);
            } else {
                $data["print_stores"] = false;
                $data["store_uuid_readonly"] = "readonly";
                $data["mssync_store_uuid"] = $this->config->get("mssync_store_uuid");
            }

            if (strlen($this->config->get("mssync_new_order_state_uuid")) <= 0) {

                $this->load->model("extension/module/mssync_orders");

                $data["print_states"] = true;
                $data["mssync_states_uuid_list"] = $this->model_extension_module_mssync_orders->getOrderStatusesListFromMs();
                $data["mssync_states_uuid_list_count"] = count($data["mssync_states_uuid_list"]);
                $data["state_uuid_required"] = "required";
            } else {
                $data["print_states"] = false;
                $data["mssync_new_order_state_uuid"] = $this->config->get("mssync_new_order_state_uuid");
            }
        } else {
            $data["store_uuid_required"] = "";
            $data["state_uuid_required"] = "";
        }
        
        $data["error_login"] = (isset($this->error["error_login"])) ? $this->error["error_login"] : "";

        $data["error_password"] = (isset($this->error["error_password"])) ? $this->error["error_password"] : "";

        $this->response->setOutput($this->load->view('extension/module/mssync', $data));
    }

    public function install() {

        $this->load->model("setting/setting");
        $this->load->model("extension/event");
        $this->load->model("extension/module/mssync_install");

        $install_data = [];
        $install_data["mssync_login"] = "";
        $install_data["mssync_password"] = "";
        $install_data["mssync_organization_uuid"] = "";
        $install_data["mssync_store_uuid"] = "";
        $install_data["mssync_project_uuid"] = "";
        $install_data["mssync_group_uuid"] = "";
        $install_data["mssync_root_folders"] = "";
        $install_data["mssync_new_order_state_uuid"] = "";
        $install_data["module_mssync_status"] = 0;

        $install_data = $this->initDefaultSettings($install_data);

        $this->model_setting_setting->editSetting("mssync", $install_data);

        $this->model_extension_event->addEvent("mssync_add_counterparty_after_admin", "admin/model/customer/customer/addCustomer/after", "extension/module/mssync/addCustomer");
        $this->model_extension_event->addEvent("mssync_add_counterparty_after_customer", "catalog/model/account/customer/addCustomer/after", "extension/module/mssync/addCustomer");
        $this->model_extension_event->addEvent("mssync_delete_customer_uuid_after_admin_delete_customer", "admin/model/customer/customer/deleteCustomer/after", "extension/module/mssync/deleteCustomerUUID");
        $this->model_extension_event->addEvent("mssync_delete_product_uuid_after_admin", "admin/model/catalog/product/deleteProduct/after", "extension/module/mssync/deleteProductUUID");
        $this->model_extension_event->addEvent("mssync_add_order_after_customer", "catalog/model/checkout/order/addOrderHistory/after", "extension/module/mssync/addOrder");
        $this->model_extension_event->addEvent("mssync_delete_order_uuid", "catalog/model/checkout/order/deleteOrder/after", "extension/module/mssync/deleteOrder");

        $this->model_extension_module_mssync_install->createRequiredTables();
    }

    public function uninstall() {

        $this->load->model("setting/setting");
        $this->load->model("extension/event");

        $this->model_setting_setting->deleteSetting("mssync");
        $this->model_extension_event->deleteEventByCode("mssync_add_counterparty_after_admin");
        $this->model_extension_event->deleteEventByCode("mssync_add_counterparty_after_customer");
        $this->model_extension_event->deleteEventByCode("mssync_delete_product_uuid_after_admin");
        $this->model_extension_event->deleteEventByCode("mssync_add_order_after_customer");
        $this->model_extension_event->deleteEventByCode("mssync_delete_customer_uuid_after_admin_delete_customer");
        $this->model_extension_event->deleteEventByCode("mssync_delete_order_uuid");
    }

    //admin/model/customer/customer/addCustomer/after
    public function addCustomer(&$route, &$args, &$output) {
        
        if ($this->config->get("mssync_status")) {
            $this->load->language("extension/module/mssync");
            $this->load->model("extension/module/mssync_customers");

            foreach ($args as $customer) {
                $this->model_extension_module_mssync_customers->createCustomer($customer);
            }
        }

        $this->response->redirect($this->url->link('customer/customer', 'token=' . $this->session->data['token'], true));
    }

    //admin/model/customer/customer/deleteCustomer/after
    public function deleteCustomerUUID(&$route, &$args, &$output) {

        if ($this->config->get("mssync_status")) {
            $this->load->model("extension/module/mssync_customers");

            foreach ($args as $customer_id) {
                $this->model_extension_module_mssync_customers->deleteCustomerUUID($customer_id);
            }
        }
    }
    
    //admin/model/catalog/product/deleteProduct/after
    public function deleteProductUUID(&$route, &$args, &$output) {

        if ($this->config->get("mssync_status")) {
            $this->load->model("extension/module/mssync_products");

            foreach ($args as $product_id) {
                $this->model_extension_module_mssync_products->deleteProductUUID($product_id);
            }
        }

        $this->response->redirect($this->url->link('catalog/product', 'token=' . $this->session->data['token'], true));
    }
    
    public function addProductsToMs(){
        
        $this->load->language("extension/module/mssync");
        
        $this->load->model("extension/module/mssync_utils");
        $this->load->model("extension/module/mssync_products");
        $this->load->model("catalog/product");
        
        $products_uuids = $this->model_extension_module_mssync_products->getAllProductUUIDS();
        $prod_uuid_hash_table = array();
        
        foreach($products_uuids as $product_uuid){
            $prod_uuid_hash_table[$product_uuid["id_product"]] = $product_uuid["uuid_product"];
        }
        
        $products = $this->model_catalog_product->getProducts();
        
        foreach ($products as $product){
            
            if(isset($prod_uuid_hash_table[$product["product_id"]]) 
                    && strlen($prod_uuid_hash_table[$product["product_id"]]) > 0){
                continue;
            }
            
            $this->model_extension_module_mssync_products->addProduct($product);
        }
        
        $this->response->redirect($this->url->link('extension/module/mssync', 'token=' . $this->session->data['token'], true));
    }
    
    public function syncProducts() {

        if ($this->config->get("mssync_status")) {
            $this->load->language("extension/module/mssync");
            $this->load->model("extension/module/mssync_products");
            $this->load->model("catalog/product");
            $this->load->model("tool/image");

            $all_products = (isset($this->request->get["all_products"])) ? true : false;

            $this->model_extension_module_mssync_products->syncProducts($all_products);
        }

        $this->response->redirect($this->url->link('extension/module/mssync', 'token=' . $this->session->data['token'], true));
    }

    public function syncAssortment() {

        if ($this->config->get("mssync_status")) {
            $this->load->model("extension/module/mssync_products");
            $this->load->language("extension/module/mssync");

            $all_products = (isset($this->request->get["all_products"])) ? true : false;

            $this->model_extension_module_mssync_products->syncAssortment($all_products);
        }
        
        $this->response->redirect($this->url->link('extension/module/mssync', 'token=' . $this->session->data['token'], true));
    }

    private function initDefaultSettings(&$install_data) {

        $install_data["mssync_vat_included"] = false;
        $install_data["mssync_show_zero_qty_prod"] = false;
        $install_data["mssync_api_url"] = "https://online.moysklad.ru/api/remap/1.1";
        $install_data["mssync_product_image_dir_to_db"] = "catalog/";
        $install_data["mssync_log_file"] = DIR_LOGS . "mssync.txt";
        $install_data["mssync_current_sale_price_name"] = "Цена продажи";

        return $install_data;
    }

    //poprietary
    protected function validate() {

        if (!$this->user->hasPermission('modify', "extension/module/mssync")) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

}
