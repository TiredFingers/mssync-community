<?php
$_['heading_title'] = 'Управление синхронизацией с Моим Складом';

// Text
$_["text_form_add_products"] = "Отправить продукты в Мой Склад";
$_["text_form_organization_name"] = "Организация из МС которая должна быть привязана к сайту";
$_["text_form_state"] = "Статус нового заказа в МС";
$_["text_actual_price"] = "Наименование цены которая должна попасть на сайт из МС ";
$_["current_sale_price"] = "Цена продажи";
$_["text_extension"] = "Расширения";
$_["text_success"] = "Настройки успешно изменены!";
$_["text_edit"] = "Настройки синхронизации с Моим Складом";
$_["text_form_login"] = "Логин для Моего Склада";
$_["text_form_password"] = "Пароль для Моего Склада";
$_["text_form_organization_uuid"] = "UUID организации из Моего Склада";
$_["text_from_store_uuid"] = "UUID магазина из Моего Склада";
$_["text_form_vat_included"] = "Включать налоги в стоимость";
$_["text_form_store_name"] = "Выберите склад который будет привязан к этому магазину";
$_["text_form_sync_product"] = "Получить продукты из МС";
$_["text_form_sync"] = "Синхронизировать";
$_["text_form_sync_couterpartys"] = "Синхронизировать контрагентов с МС";
$_["text_form_sync_order_statuses"] = "Получить статусы заказов из МС";
$_["counterparty_description"] = "Добавлен через админку сайта";
$_["text_in_stock"] = "В наличии";
$_["text_out_of_stock"] = "Нет в наличии";
$_["text_tax_title"] = "Налоги";
$_["text_without_description"] = "Описание товара отсутствует";
$_["text_common_undefined"] = "Не указано";
$_["text_common_country"] = "Российская Федерация";
$_["text_common_measure"] = "шт.";
$_["text_form_sync_orders"] = "Получить заказы из МС";
$_["new_order_status_name"] = "Новый";
$_["text_sync_check_tag"] = "розничный покупатель"; // обязательно должно называться также как в МС
$_["text_sync_products_pathname1"] = "Сопутствующие товары";
$_["text_sync_products_pathname2"] = "Bardahl";
$_["text_form_show_zero_qnt_prod"] = "Показывать товары с нулевым остатком";
$_["text_form_no_sync"] = "Синхронизация не происходит";
$_["text_sync_all_orders"] = "Синхронизировать все заказы";
$_["text_form_sync_in_process"] = "Происходит синхронизация";
$_["text_form_sync_limit_each"] = "100 заказов каждые ";
$_["text_form_minutes"] = " минут.";
$_["text_form_stop_sync"] = "Остановить синхронизацию.";
$_["text_form_stoped_sync"] = "Синхронизация остановлена";
$_["text_form_sync_product_folders"] = "Синхронизировать группы продуктов";
$_["text_form_sync_assortment"] = "Синхронизировать остатки";
$_["text_form_sync_delivery"] = "Синхронизировать услуги доставки";
$_["text_sync_delivery_pathname"] = "Услуги доставки";
$_["text_project_uuid"] = "UUID проекта из Моего Склада";
$_["text_group_uuid"] = "UUID группы из Моего Склада";
$_["text_root_folders"] = "Корневые группы товаров для синхронизации";

// Entry
$_["entry_status"] = "Статус";

// Error
$_["error_permission"] = "У Вас нет прав для изменения модуля Аккаунт!";


/////////// LOG

$_["method_start"] = "Начало работы метода ";
$_["method_end"] = "Конец работы метода ";
$_["log_new_customer"] = "Добавлен новый пользователь";
$_["log_upd_customer"] = "Пользователь найден и обновлён";
$_["log_entities_zero_count"] = "Запрос к API МС не вернул ни одной сущности"; 
$_["log_bad_product_sync"] = "Не удалось синхронизировать продукт с uuid: ";
$_["log_no_entities"] = "Пустой результат из МС";
$_["log_product_already_synced"] = "Продукт уже синхронизирован";
///// getOrganizationUUID

///// syncCounterparty

$_["log_archived_counterparty"] = "Контрагент находится в архиве";

///// getStoreUUID

$_["log_stores_res"] = "Магазинов в результате работы получено: ";
$_["log_no_stores"] = "Запрос не вернул ни одного магазина";

///// createCounterparty

// syncOrdersFromMs
$_["no_user_uuid"] = "В базе нет пользователя с uuid: ";
$_["new"] = "Новый";


/////////// LOG ERROR TEXT

$_["error_curl_get"] = "метод curlGet вернул массив со следующими ошибками:";
$_["error_curl_init"] = "Ошибка инициализации curl";
$_["error_curl_settings"] = "Ошибка при передаче настроек в curl";

$_["error_msapi"] = "ошибка получена из Моего Склада";

/////////// HELP MSG
$_["help_mssync_login"] = "Логин пользователя созданного для синхронизации магазина с Моим Складом";
$_["help_mssync_password"] = "Пароль пользователя созданного для синхронизации магазина с Моим Складом";
$_["help_mssync_organization"] = "Код организации из МС которая будет привязана к этому сайту. Подставляется автоматически. Менять только в крайнем случае.";
$_["help_mssync_store"] = "Код склада из МС который будет привязан к этому сайту. Подставляется автоматически. Менять только в крайнем случае.";
