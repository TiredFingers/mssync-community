<?php

class ModelExtensionModuleMssyncInstall extends Model{
    
    //to light
    public function createRequiredTables(){
        
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mssync_customer_uuid` "
                . "( `customer_id` INT NOT NULL AUTO_INCREMENT , "
                . "`customer_uuid` VARCHAR(255) NOT NULL , "
                . "`sync_date` DATE NOT NULL, PRIMARY KEY (`customer_id`), "
                . "UNIQUE (`customer_uuid`))");
        
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mssync_product_uuid` "
                . "( `id_product` INT NOT NULL AUTO_INCREMENT , "
                . "`uuid_product` VARCHAR(255) NOT NULL , "
                . "`sync_date` DATE NOT NULL, PRIMARY KEY (`id_product`), "
                . "UNIQUE (`uuid_product`))");
        
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mssync_sync_state` 
            (`id` int(11) NOT NULL, 
            `code` varchar(100) NOT NULL COMMENT 'Флаг отмечающий что именно мы синхронизировали', 
            `state` enum('process','done') NOT NULL DEFAULT 'done' COMMENT 'Состояние синхронизации', 
            `offset` int(11) NOT NULL COMMENT 'Отступ в навигации по API Моего Склада', 
            `limit` int(11) NOT NULL COMMENT 'Кол-во элементов API Моего Склада для обработки. 100 - максимум на 02.05.17', 
            `sync_date` varchar(50) NOT NULL COMMENT 'Дата синхронизации. На 26.05.17 обязателен формат гггг-мм-дд чч:мм:сс')");
        
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mssync_sync_msorders`
			(`id_order` int(11) NOT NULL,
			`uuid_order` text NOT NULL,
			`status` tinytext NOT NULL)");
        
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mssync_order_uuid` ("
                . "`id` int(11) NOT NULL,"
                . "`id_order` int(11) NOT NULL,"
                . "`uuid` varchar(255) NOT NULL)");
        
    }
}