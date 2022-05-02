<?php

class ControllerExtensionModuleMssync extends Controller {

    private $error = [];

    protected $syncProductsUrl = 'extension/module/mssync/syncProducts';
    protected $syncAssortmentUrl = 'extension/module/mssync/syncAssortment';
    protected $addProductsToMsUrl = 'extension/module/mssync/addProductsToMs';

    public function index() {

        $this->load->language("extension/module/mssync");

        if (($this->request->server["REQUEST_METHOD"] == "POST") && $this->validate()) {

            $this->load->model("setting/setting");  // ok

            $this->request->post["mssync_vat"] = (!isset($this->request->post["mssync_vat"])) ? "false" : "true";
            $this->request->post["mssync_sync_show_zero_quantity_products"] = (!isset($this->request->post["mssync_show_zero_qty_prod"])) ? 0 : 1;
            $this->request->post["mssync_password"] = ($this->request->post["mssync_password"] === "******") ? $this->config->get("mssync_password") : $this->request->post["mssync_password"];

            $this->request->post = $this->initDefaultSettings($this->request->post);

            /**
             * why it's $this->request-post here but not the array?
             * I'm thought about editSetting like about black box. I don't know what code will be in it
             * so I don't want and actually must not make assumptions about it
             * Maybe, in the future, implementation will be different and code inside will expect request->post array
             * instead of my custom
             */
            $this->model_setting_setting->editSetting("mssync", $this->request->post);
            $this->session->data["success"] = $this->language->get("text_success");
            $this->response->redirect($this->getMarketplaceLink());
        }

        $this->setPageTitle();

        $login_and_pass_exist = strlen($this->config->get("mssync_login")) > 0 && strlen($this->config->get("mssync_password")) > 0;
        $org_not_exist = strlen($this->config->get("mssync_organization_uuid")) <= 0;
        $store_not_exist = strlen($this->config->get("mssync_store_uuid")) <= 0;
        $new_order_state_uuid_exist = strlen($this->config->get("mssync_new_order_state_uuid")) <= 0;

        $data = $this->initData(
            $login_and_pass_exist,
            $org_not_exist,
            $store_not_exist,
            $new_order_state_uuid_exist
        );

        $this->response->setOutput($this->load->view('extension/module/mssync', $data));
    }

    public function install() {

        $this->load->model("setting/setting");
        $this->load->model("setting/event");
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
        // todo выдать текущему пользователю права на modify
        $install_data = $this->initDefaultSettings($install_data);

        $this->model_setting_setting->editSetting("mssync", $install_data);

        $this->model_setting_event->addEvent("mssync_add_counterparty_after_admin", "admin/model/customer/customer/addCustomer/after", "extension/module/mssync/addCustomer");
        $this->model_setting_event->addEvent("mssync_add_counterparty_after_customer", "catalog/model/account/customer/addCustomer/after", "extension/module/mssync/addCustomer");
        $this->model_setting_event->addEvent("mssync_delete_customer_uuid_after_admin_delete_customer", "admin/model/customer/customer/deleteCustomer/after", "extension/module/mssync/deleteCustomerUUID");
        $this->model_setting_event->addEvent("mssync_delete_product_uuid_after_admin", "admin/model/catalog/product/deleteProduct/after", "extension/module/mssync/deleteProductUUID");
        $this->model_setting_event->addEvent("mssync_add_order_after_customer", "catalog/model/checkout/order/addOrderHistory/after", "extension/module/mssync/addOrder");
        $this->model_setting_event->addEvent("mssync_delete_order_uuid", "catalog/model/checkout/order/deleteOrder/after", "extension/module/mssync/deleteOrder");

        $this->model_extension_module_mssync_install->createRequiredTables();
    }

    public function uninstall() {

        $this->load->model("setting/setting");
        $this->load->model("setting/event");

        $this->model_setting_setting->deleteSetting("mssync");
        $this->model_setting_event->deleteEventByCode("mssync_add_counterparty_after_admin");
        $this->model_setting_event->deleteEventByCode("mssync_add_counterparty_after_customer");
        $this->model_setting_event->deleteEventByCode("mssync_delete_product_uuid_after_admin");
        $this->model_setting_event->deleteEventByCode("mssync_add_order_after_customer");
        $this->model_setting_event->deleteEventByCode("mssync_delete_customer_uuid_after_admin_delete_customer");
        $this->model_setting_event->deleteEventByCode("mssync_delete_order_uuid");
    }

    public function setPageTitle(){
        $this->document->setTitle($this->language->get("heading_title"));
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
        $install_data["mssync_current_sale_price_name"] = "Цена продажи";  // todo заменить яз перем.

        return $install_data;
    }

    protected function createBreadcrumbs(){

        $breadcrumbs = array();

        $breadcrumbs[] = array(
            "text" => $this->language->get("text_home"),
            "href" => $this->url->link("common/dashboard", $this->getUserTokenStr(), true)
        );

        $breadcrumbs[] = array(
            "text" => $this->language->get("text_extension"),
            "href" => $this->getMarketplaceLink()
        );

        $breadcrumbs[] = array(
            "text" => $this->language->get("heading_title"),
            "href" => $this->url->link("extension/module/mssync", $this->getUserTokenStr(), true)
        );

        return $breadcrumbs;
    }

    protected function getUserTokenStr(){
        return 'user_token=' . $this->session->data["user_token"];
    }

    protected function getMarketplaceLink(){
        return $this->url->link('marketplace/extension', $this->getUserTokenStr() . '&type=module', true);
    }

    protected function getMssyncStatus(){
        return $this->config->get('mssync_status') ? 1 : 0;
    }

    protected function initData(
        $password_and_login_exists = false,
        $org_not_exist = false,
        $store_not_exist = false,
        $new_order_state_uuid_exist = false
    ){
        $data = [];

        $data['error_warning'] = (isset($this->error["warning"])) ? $this->error["warning"] : "";
        $data['mssync_status'] = $this->getMssyncStatus();
        $data['enabled_selected'] = $data['mssync_status'] === 1 ? "selected='selected'" : '';
        $data['disabled_selected'] = $data['mssync_status'] === 0 ? "selected='selected'" : '';

        $data["action"] = $this->getActionUrl();
        $data["cancel"] = $this->getMarketplaceLink();

        $langvars = [
            'heading_title', 'button_save', 'button_cancel', 'text_edit', 'entry_status', 'text_enabled', 'text_disabled',
            'text_form_login', 'text_form_password', 'text_form_vat_included', 'text_form_sync_product', 'text_form_sync_assortment',
            'text_actual_price', 'help_mssync_login', 'help_mssync_password', 'help_mssync_organization', 'help_mssync_store',
            'entry_status', 'text_form_state', 'help_mssync_state', 'text_project_uuid', 'text_form_sync', 'text_form_add_products',
            ''
        ];

        foreach($langvars as $langvar){
            $data[$langvar] = $this->language->get($langvar);
        }

        $data["text_form_organization"] = $this->language->get("text_form_organization_uuid");  // todo fix
        $data["text_form_store"] = $this->language->get("text_from_store_uuid");  // todo fix

        $data["sync_products_href"] = $this->getSyncProductsUrl();

        //$data["text_form_show_zero_qnt_prod"] = $this->language->get("text_form_show_zero_qnt_prod");  // todo delete

        $data["sync_assortment_href"] = $this->getSyncAssortmentUrl();
        $data["add_products_href"] = $this->getAddProductsToMsUrl();

        $data["breadcrumbs"] = $this->createBreadcrumbs();

        $data["print_organizations"] = false;
        $data["print_stores"] = false;
        $data["mssync_organization_uuid"] = "";
        $data["mssync_store_uuid"] = "";

        $data["header"] = $this->load->controller("common/header");
        $data["column_left"] = $this->load->controller("common/column_left");
        $data["footer"] = $this->load->controller("common/footer");

        $data["mssync_current_sale_price_name"] = (isset($this->request->post["mssync_current_sale_price_name"])) ? $this->request->post["mssync_current_sale_price_name"] : $this->config->get("mssync_current_sale_price_name");
        $data["mssync_login"] = (isset($this->request->post["mssync_login"])) ? $this->request->post["mssync_login"] : $this->config->get("mssync_login");
        $data['mssync_password'] = (isset($this->request->post['mssync_password'])) ? $this->request->post['mssync_password'] : $this->config->get('mssync_password');
        $data['mssync_vat_in_sel'] = $this->getMssyncVatInSel();

        if($password_and_login_exists){
            $data["store_uuid_required"] = "required";

            $langvars = ['text_form_organization_name'];

            foreach($langvars as $langvar){
                $data[$langvar] = $this->language->get($langvar);
            }

            $data['organization_uuid_readonly'] = $this->getOrganizationUuidReadonlyFlag($org_not_exist);
            $data['mssync_organization_uuid_list'] = $this->getMssyncOrganizationUuidList($org_not_exist);
            $data['print_organizations'] = $this->getPrintOrganizations($org_not_exist);
            $data['count_organizations'] = $this->getCountOrganizations($org_not_exist, $data['mssync_organization_uuid_list']);
            $data['mssync_organization_uuid'] = $this->getOrganizationUuid($org_not_exist);
            $data['print_stores'] = $this->getPrintStores($store_not_exist);
            $data['store_uuid_readonly'] = $this->getStoreUuidReadonlyFlag($store_not_exist);
            $data['mssync_store_uuid'] = $this->getStoreUuid($store_not_exist);
            $data['mssync_store_uuid_list'] = $this->getStoreUuidList($store_not_exist);
            $data['count_stores'] = $this->getCountStores($store_not_exist, $data['mssync_store_uuid_list']);
            $data['print_states'] = $this->getPrintStates($new_order_state_uuid_exist);
            $data['mssync_states_uuid_list'] = $this->getStatesUuidList($new_order_state_uuid_exist);
            $data['mssync_states_uuid_list_count'] = $this->getStatesUuidCount($new_order_state_uuid_exist);
            $data['state_uuid_required'] = $this->getStateUuidRequired($new_order_state_uuid_exist);
            $data['mssync_new_order_state_uuid'] = $this->getNewOrderStateUuid($new_order_state_uuid_exist);

        }
        else{
            $data["store_uuid_required"] = "";
            $data["state_uuid_required"] = "";
        }

        $data["error_login"] = (isset($this->error["error_login"])) ? $this->error["error_login"] : "";
        $data["error_password"] = (isset($this->error["error_password"])) ? $this->error["error_password"] : "";

        return $data;
    }

    protected function getNewOrderStateUuid($state_uuid_exist){

        return !$state_uuid_exist ? $this->config->get('mssync_new_order_state_uuid') : null;
    }

    protected function getStateUuidRequired($state_uuid_exist = false){
        return $state_uuid_exist ? 'required' : null;
    }

    protected function getStatesUuidCount($state_uuid_exist = false, $states_list = []){
        return $state_uuid_exist ? count($states_list) : null;
    }

    protected function getStatesUuidList($state_uuid_exist = false){
        $this->load->model("extension/module/mssync_orders");
        return $state_uuid_exist ? $this->model_extension_module_mssync_orders->getOrderStatusesListFromMs() : null;
    }

    protected function getPrintStates($state_uuid_exist = false){
        return $state_uuid_exist;
    }

    protected function getCountStores($store_not_exist = false, $uuid_list = []){
        return $store_not_exist ? count($uuid_list) : null;
    }

    protected function getStoreUuidList($store_not_exist = false){
        $this->load->model('extension/module/mssync_organisation');
        return $store_not_exist ? $this->model_extension_module_mssync_organisation->getStoreUuid() : null;
    }

    protected function getStoreUuid($store_not_exist = false){
        return $store_not_exist ? '' : $this->config->get('mssync_store_uuid');
    }

    protected function getPrintStores($store_not_exist = false){
        return $store_not_exist;
    }

    protected function getCountOrganizations($org_not_exist = false, $uuid_list = []){
        if($org_not_exist){
            return count($uuid_list);
        }
        return null;
    }

    protected function getOrganizationUuid($org_not_exist = false){
        return !$org_not_exist ? $this->config->get('mssync_organization_uuid')  : null;
    }

    protected function getPrintOrganizations($org_not_exist = false){
        return $org_not_exist ? true : null;
    }

    protected function getMssyncOrganizationUuidList($org_not_exist = false){
        if($org_not_exist){
            $this->load->model('extension/module/mssync_organisation');
            return $this->model_extension_module_mssync_organisation->getOrganizationUUID();
        }

        return null;
    }

    protected function getStoreUuidReadonlyFlag($store_not_exist = false){
        return $store_not_exist ? '' : 'readonly';
    }

    protected function getOrganizationUuidReadonlyFlag($org_not_exist = false){

        return $org_not_exist ? '' : 'readonly';
    }

    protected function getMssyncVatInSel(){
        return (isset($this->request->post['mssync_vat'])
            || $this->config->get('mssync_vat_included') === 'true') ? 'checked' : '';
    }

    protected function getSyncAssortmentUrl(){
        return $this->makeUrl($this->syncAssortmentUrl, $this->getUserTokenStr(), true);
    }

    protected function getAddProductsToMsUrl(){
        return $this->makeUrl($this->addProductsToMsUrl, $this->getUserTokenStr(), true);
    }

    protected function getSyncProductsUrl(){
        return $this->makeUrl($this->syncProductsUrl, $this->getUserTokenStr() . '&all_products=true', true);
    }

    protected function getActionUrl(){
        return $this->makeUrl('extension/module/mssync', $this->getUserTokenStr(), true);
    }

    protected function makeUrl($link, $token, $secure = false){
        return $this->url->link($link, $token, $secure);
    }

    //poprietary
    protected function validate() {

        if (!$this->user->hasPermission('modify', "extension/module/mssync")) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

}
