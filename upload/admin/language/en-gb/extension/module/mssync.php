<?php
$_['heading_title'] = 'Manage synchronization with Мой Склад';

// Text
$_["text_form_organization_name"] = "The organization from Мой Склад which should be tied to the site";
$_["text_form_state"] = "The status of the new order in Мой Склад";
$_["text_actual_price"] = "The name of the price that should get to the site from the Мой Склад";
$_["current_sale_price"] = "Sale price";
$_["text_extension"] = "Extensions";
$_["text_success"] = "Settings succesfully changed!";
$_["text_edit"] = "Manage synchronization with Мой Склад";
$_["text_form_login"] = "Login for Мой Склад";
$_["text_form_password"] = "Password for Мой Склад";
$_["text_form_organization_uuid"] = "Organization from Мой Склад";
$_["text_from_store_uuid"] = "Store UUID from Мой Склад";
$_["text_form_vat_included"] = "Include taxes in the price";
$_["text_form_store_name"] = "Select the warehouse that will be linked to this store";
$_["text_form_sync_product"] = "Get products from Мой Склад";
$_["text_form_sync"] = "Synchronize";
$_["counterparty_description"] = "Added through the admin area of the site";
$_["text_in_stock"] = "In stock";
$_["text_out_of_stock"] = "Out of stock";
$_["text_tax_title"] = "Taxes";
$_["text_without_description"] = "No product description";
$_["text_common_undefined"] = "Unspecified";
$_["text_common_country"] = "Russian Federation";
$_["text_common_measure"] = "PCs.";
$_["text_form_show_zero_qnt_prod"] = "Show products with zero quantity";
$_["text_form_sync_assortment"] = "Synchronize assortment";
$_["text_project_uuid"] = "Project UUID from Мой Склад";
$_["text_group_uuid"] = "Group UUID from Мой Склад";

// Entry
$_["entry_status"] = "Status";

// Error
$_["error_permission"] = "You have no rights to change the mssync module!";


/////////// LOG

$_["method_start"] = "The beginning of the method ";
$_["method_end"] = "The end of the method ";
$_["log_new_customer"] = "Added a new user";
$_["log_upd_customer"] = "The user was found and updated";
$_["log_entities_zero_count"] = "Returned an empty request from Мой Склад"; 
$_["log_bad_product_sync"] = "Unable to sync product with uuid: ";
$_["log_no_entities"] = "Empty result from Мой Склад ";
$_["log_product_already_synced"] = "The product is already synchronized ";
///// getOrganizationUUID

///// syncCounterparty

$_["log_archived_counterparty"] = "The counterparty is in the archive";

///// getStoreUUID

$_["log_stores_res"] = "Stores as a result of the work received:";
$_["log_no_stores"] = "The request did not return any stores";

///// createCounterparty

// syncOrdersFromMs
$_["no_user_uuid"] = "No user in the database with uuid: ";
$_["new"] = "New";


/////////// LOG ERROR TEXT

$_["error_curl_get"] = "the curlGet method returned an array with the following errors:";
$_["error_curl_init"] = "Curl initialization error";
$_["error_curl_settings"] = "Error transferring settings to curl";

$_["error_msapi"] = "error received from Мой Склад";

/////////// HELP MSG
$_["help_mssync_login"] = "User login is created for the synchronization of the store with Мой Склад";
$_["help_mssync_password"] = "The password of the user created to synchronize the store with Мой Склад";
$_["help_mssync_organization"] = "The organization code from the MS that will be linked to this site. It is inserted automatically. To change only last resort.";
$_["help_mssync_store"] = "Warehouse code from Мой Склад which will be linked to this site. It is inserted automatically. To change only last resort.";
