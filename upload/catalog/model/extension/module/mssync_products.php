<?php

class ModelExtensionModuleMssyncProducts extends Model{
    
    /**
     * 
     * @param int $product_id
     * @return string
     * 
     * ver 1.0
     * rev 31.08.18
     * anatoliy.iwanov@yadex.ru
     */
    public function getProductUUID($product_id) {

        $res = $this->db->query("SELECT uuid_product "
                . "FROM " . DB_PREFIX . "mssync_product_uuid "
                . "WHERE id_product = " . (int) $product_id);

        if ($res->num_rows > 0) {
            return (string) $res->row["uuid_product"];
        }

        return "";
    }

}