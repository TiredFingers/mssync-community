<?php

class ModelExtensionModuleMssyncUtils extends Model{
    
    /**
     * 
     * @param array $data *required ['rows'] key*
     * @param int $count
     * @return array[index]['key']['value']
     * 
     * ver 1.0.2.0
     * rev 02.10.18
     * anatoliy.iwanov@yandex.ru
     */
    public function createList($data) {
        
        $list = array(0 => array());

        $count = count($data["rows"]);

        for ($i = 0; $i < $count; $i++) {
            if (!isset($data["rows"][$i]["archived"]) 
                    || $data["rows"][$i]["archived"] != true) {
                $list[$i]["id"] = $data["rows"][$i]["id"];
                $list[$i]["name"] = $data["rows"][$i]["name"];
            }
        }

        return $list;
    }
    
    /**
     * @param string $url
     * @param string $ms_login
     * @param string $ms_password
     * @param array $get
     * @param array $options
     * @return error array || string if success
     * 
     * ver 1.0.2.1
     * rev 02.10.18
     * anatoliy.iwanov@yandex.ru
     */
    public function curlGet($url, $ms_login, $ms_password, $get = array(), $options = array()) {

        $error = array("error" => array());

        $defaults = array(
            CURLOPT_URL => $url . (strpos($url, '?') === false ? '?' : '') . http_build_query($get),
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_USERPWD => $ms_login . ":" . $ms_password
        );

        if (!$ch = curl_init()) {
            $error["error"][] = $this->language->get("error_curl_init");
            $error["error"][] = curl_errno($ch);
            $error["error"][] = curl_error($ch);
            curl_close($ch);
            return $error;
        }

        if (!curl_setopt_array($ch, ($options + $defaults))) {
            $error["error"][] = $this->language->get("error_curl_settings");
            $error["error"][] = curl_errno($ch);
            $error["error"][] = curl_error($ch);
            curl_close($ch);
            return $error;
        }

        if (!$result = curl_exec($ch)) {
            return curl_error($ch);
        }



        curl_close($ch);
        return $result;
    }
    
    /**
     * 
     * @param string $url
     * @param string $ms_login
     * @param string $ms_password
     * @param array $post
     * @param array $options
     * @return array
     * 
     * ver 1.1
     * rev 31.08.18
     * anatoliy.iwanov@yandex.ru
     */
    public function curlPost($url, $ms_login, $ms_password, $post, $options = array()) {

        $error = array("error" => array());

        $defaults = array(
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 4,
            CURLOPT_POSTFIELDS => http_build_query($post),
            CURLOPT_USERPWD => $ms_login . ":" . $ms_password
        );

        if (!$ch = curl_init()) {
            $error["error"][] = $this->language->get("error_curl_init");
            $error["error"][] = curl_errno($ch);
            $error["error"][] = curl_error($ch);
            curl_close($ch);
            return $error;
        }


        if (!curl_setopt_array($ch, ($options + $defaults))) {
            $error["error"][] = $this->language->get("error_curl_settings");
            $error["error"][] = curl_errno($ch);
            $error["error"][] = curl_error($ch);
            curl_close($ch);
            return $error;
        }

        if (!$result = curl_exec($ch)) {
            $error["error"][] = curl_error($ch);
            curl_close($ch);

            return $error;
        }

        curl_close($ch);
        return $result;
    }

    /**
     * 
     * @param string $url
     * @param string $ms_login
     * @param string $ms_password
     * @param string $json_data
     * @param string $request_type
     * @param array $options
     * @return mixed
     * 
     * ver 1.1
     * rev 16.02.17
     * anatoliy.iwanov@yandex.ru
     */
    public function curlCustomRequest($url, $ms_login, $ms_password, $json_data, $request_type, $options = array()) {

        $error = array("error" => array());

        $defaults = array(
            CURLOPT_VERBOSE => TRUE,
            CURLOPT_HTTPHEADER => [ "Content-Type: application/json",
                "Content-Length: " . strlen($json_data),
                "X-Requested-With: XMLHttpRequest",
                "Accept: application/json, text/javascript, */*; q=0.01"],
            CURLOPT_CUSTOMREQUEST => $request_type,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 4,
            CURLOPT_POSTFIELDS => $json_data,
            CURLOPT_USERPWD => $ms_login . ":" . $ms_password
        );

        if (!$ch = curl_init()) {
            $error["error"][] = $this->language->get("error_curl_init");
            $error["error"][] = curl_errno($ch);
            $error["error"][] = curl_error($ch);
            curl_close($ch);
            return $error;
        }

        if (!curl_setopt_array($ch, ($options + $defaults))) {
            $error["error"][] = $this->language->get("error_curl_settings");
            $error["error"][] = curl_errno($ch);
            $error["error"][] = curl_error($ch);
            curl_close($ch);
            return $error;
        }

        if (!$result = curl_exec($ch)) {
            return curl_error($ch);
        }

        curl_close($ch);
        return $result;
    }

    /**
     * @param string $filename
     * @param string $url
     * @return array || array
     * 
     * ver 2.1.2.3
     * rev 26.09.18
     * anatoliy.iwanov@yandex.ru
     */
    public function uploadImageFromMs($filename, $url) {

        if (strlen($filename) > 4) {
            $filename_parts = explode(".", $filename);
            $extension = strtolower($filename_parts[count($filename_parts) - 1]);
            $allowed_extensions = array("png", "jpg", "jpeg", "jpe", "jp2", "bmp", "gif");
            $bad_extension = true;


            foreach ($allowed_extensions as $allowed_extension) {
                if ($extension === $allowed_extension) {
                    $bad_extension = false;
                    break;
                }
            }

            if ($bad_extension) {
                $error[] = $this->language->get("error_not_allowed_ext");
                return $error;
            }

            $ms_login = $this->config->get("mssync_login");
            $ms_password = $this->config->get("mssync_password");

            $image = $this->curlGetImage($url, array(), array(), $ms_login, $ms_password);

            if (is_array($image) && isset($image["error"])) {
                $error[] = $image["error"];
                return $error;
            }
            
            if(!is_dir(DIR_IMAGE) 
                    || file_put_contents(DIR_IMAGE . $filename, $image) === false){
                
                    $error[] = $this->language->get("error_file_upload");
                    return $error;
            }

            return DIR_IMAGE . $filename;
        } else {
            $error[] = $this->language->get("error_empty_filename");
            return $error;
        }
    }
    
    /**
     * From Moi Sklad we can get a float value without '.' 1800.00 in Moi Sklad looks in json result like 180000
     * with this method I try to solve it
     * 
     * @param int $price
     * @return float
     * 
     * ver 1.1
     * rev 02.06.17
     * anatoliy.iwanov@yandex.ru
     * 
     */
    public function msPricePrepareForDb($price) {
        return $price / 100;
    }
    
    /**
     * 
     * @param float $price
     * @return float
     * 
     * ver 2.1
     * rev 12.12.18
     * anatoliy.iwanov@yandex.ru
     */
    public function sitePricePrepareForMs($price) {
        return $price * 100;
    }
    
    /**
     * 
     * @param string $log
     * ver 0.2
     * rev 29.03.17
     * anatoliy.iwanov@yandex.ru
     */
    public function writeToLog($log) {
        file_put_contents($this->config->get("mssync_log_file"), 
                htmlentities($log, ENT_COMPAT | ENT_HTML401, "UTF-8"), 
                FILE_APPEND | LOCK_EX);
    }
    
    /**
     * 
     * @param string $curl_res
     * @param string $log
     * @param int $line
     * @param string $json_flag
     * @return array
     * 
     * ver 0.2.0.1
     * rev 03.09.17
     * anatoliy.iwanov@yandex.ru
     */
    public function getJson($curl_res, $log, $line, $json_flag = "d") {

        $result = array("error" => false);
        if (is_array($curl_res)) {

            $this->curlErrorHandler($curl_res["error"], $log, $line);
            $result ["error"] = true;
            return $result;
        }

        if ($json_flag == "e") {
            $ms_entities = json_encode($curl_res);
        } else {

            $ms_entities = json_decode($curl_res, true);
        }

        if (json_last_error() != JSON_ERROR_NONE) {
            $this->jsonErrorHandler($log, $line, json_last_error(), json_last_error_msg(), $json_flag);
            $result["error"] = true;
            return $result;
        }

        if (isset($ms_entities["errors"])) {
            $this->msApiErrorHandler($log, $line, $ms_entities["errors"]);
            $result["error"] = true;
            return $result;
        }

        return $ms_entities;
    }
    
    /**
     * 
     * @param string $log
     * @param int $line
     * @param 'result of json_last_error()' $json_last_error
     * @param 'result of json_last_error_msg()' $json_last_error_msg
     * @param char $msg_flag
     * 
     * ver 1.0
     * rev 21.12.2016
     */
    public function jsonErrorHandler($log, $line, $json_last_error, $json_last_error_msg, $msg_flag) {

        if ($msg_flag === "d") {
            $err_msg = $this->language->get("error_json_decode");
        }
        if ($msg_flag === "e") {
            $err_msg = $this->language->get("error_json_encode");
        }

        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                $err_msg .= '\n - Достигнута максимальная глубина стека\n';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $err_msg .= '\n - Некорректные разряды или не совпадение режимов\n';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $err_msg .= '\n - Некорректный управляющий символ\n';
                break;
            case JSON_ERROR_SYNTAX:
                $err_msg .= '\n - Синтаксическая ошибка, не корректный JSON\n';
                break;
            case JSON_ERROR_UTF8:
                $err_msg .= '\n - Некорректные символы UTF-8, возможно неверная кодировка\n';
                break;
            default:
                $err_msg .= '\n - Неизвестная ошибка\n';
                break;
        } 
        
        $json_errors = $this->getJsonErrors($json_last_error, $json_last_error_msg, $line);

        $count = count($json_errors);

        for ($i = 0; $i < $count; $i++) {
            $log .= $err_msg . "\n" . $json_errors[$i] . "\n";
        }

        $this->writeToLog($log);
    }
    
    /**
     * 
     * @param string $curl_error
     * @param string $log
     * @param int $line
     * 
     * ver 0.2
     * rev 21.12.2016
     * anatoliy.iwanov@yandex.ru
     */
    public function curlErrorHandler($curl_error, $log, $line) {

        $log .= $this->language->get("error_curl_get") . "\n";
        $log .= $this->getCurlErrors($curl_error, $line) . "\n";
        $this->writeToLog($log);
    }
    
    /**
     * 
     * @param string $log
     * @param int $line
     * @param array $errors
     * 
     * return void
     * 
     * ver 1.0
     * rev 30.08.18
     */
    public function msApiErrorHandler($log, $line, $errors) {

        $log .= $this->language->get("error_msapi") . "\n" . $this->getMsApiErrors($errors, $line);
        $this->writeToLog($log);
    }
    
    /**
     * 
     * @param $code string 
     * @param $all boolean sync all orders flag
     * @return array
     * 
     * ver 1.1.4
     * rev 27.05.17
     * anatoliy.iwanov@yandex.ru
     */
    public function getSyncStateInfo($code, $all) {

        $info = array(
            "code" => $code,
            "state" => "",
            "offset" => 0,
            "limit" => 1000,
            "sync_date" => ($all == true) ? "" : "&updatedFrom=" . date("Y-m-d") . "%2000:00:00"
        );

        $res = $this->db->query("SELECT * FROM " . DB_PREFIX . "mssync_sync_state "
                . "WHERE code = '" . $this->db->escape($code) . "' "
                . "AND state = 'process'");

        if ($res->num_rows > 0) {

            $info["code"] = $code;
            $info["state"] = $res->row["state"];
            $info["offset"] = (int) $res->row["offset"];
            $info["limit"] = (int) $res->row["limit"];
            $info["sync_date"] = ($all == true) ? "" : "&updatedFrom=" . $res->row["sync_date"];
        }

        return $info;
    }
    
    /**
     * 
     * @param  string $code
     * @param  string $state
     * @param  int $offset
     * @param  int $limit
     * 
     * ver 1.1.1
     * rev 30.08.18
     * anatoliy.iwanov@yandex.ru
     */
    public function setSyncStateInfo($code, $state, $offset, $limit) {

        $this->deleteSyncStateInfo($code);

        $this->db->query("INSERT INTO `" . DB_PREFIX . "mssync_sync_state` "
                . "SET `code` = '" . $this->db->escape($code) . "', "
                . "`state` = '" . $this->db->escape($state) . "', "
                . "`offset` = " . (int) $offset . ", "
                . "`limit` = " . (int) $limit . ", "
                . "`sync_date` = '" . date("Y-m-d") . "%2000:00:00'");
    }
    
    /**
     * @param $status_name string 
     * @return int
     * 
     * ver 1.1.1
     * rev 30.08.18
     * anatoliy.iwanov@yandex.ru
     */
    public function selectStockStatusIdByName($status_name) {

        $status_id = 0;

        $query = $this->db->query("SELECT stock_status_id "
                . "FROM " . DB_PREFIX . "stock_status "
                . "WHERE name = '" . $this->db->escape($status_name) . "'");
        if ($query->num_rows > 0) {
            $status_id = $query->row["stock_status_id"];
        }

        return (int) $status_id;
    }
    
    /**
     * 
     * @param string $code
     * @return void Description
     * 
     * ver 1.1
     * rev 30.08.18
     * anatoliy.iwanov@yandex.ru
     */
    private function deleteSyncStateInfo($code) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "mssync_sync_state` "
                . "WHERE `code` = '" . $this->db->escape($code) . "'");
    }
    
    /**
     * @param int result of poprietary func json_last_error() $json_last_error
     * @param string result of poprietary func json_last_error_msg() $error_msg
     * @param int $line
     * @return array
     * 
     * ver 1.0
     * rev 24.12.16
     */
    private function getJsonErrors($json_last_error, $error_msg, $line) {

        $data = array();

        $data[] = $json_last_error;
        $data[] = $error_msg;
        $data[] = $line;


        return $data;
    }
    
    /**
     * @param array $errors
     * @param int $line
     * @return array
     * 
     * ver 1.2
     * rev 24.12.16
     * anatoliy.iwanov@yandex.ru
     */
    private function getCurlErrors($errors, $line) {

        $data = array("error" => array("curl" => array("line" => "")));
        foreach ($errors as $error) {
            $data["error"]["curl"][] = $error;
        }
        $data["error"]["curl"]["line"] = $line;

        return $data;
    }
    
    /**
     * @param array $errors
     * @param int $line
     * @return string
     * 
     * ver 1.0
     * rev 21.12.16
     */
    private function getMsApiErrors($errors, $line) {
        $result = "";

        foreach ($errors as $error) {

            foreach ($error as $key => $value) {
                $result .= $this->msApiErrorCodeHandler($value) . "\n";
                $result .= "key: " . $key . " value: " . $value . "\n";
            }
        }

        $result .= "line: " . $line . "\n";
        return (string) $result;
    }
    
    /**
     * 
     * @param string $error_code
     * @return string
     * 
     * ver 1.0
     * rev 30.08.18
     */
    private function msApiErrorCodeHandler($error_code) {

        $request_string = "error_msapi_" . $error_code;
        $result = $this->language->get($request_string);

        if ($result === $request_string) {
            $result = $this->language->get("error_msapi_unknown_code");
        }

        return (string) $result;
    }
    
    /**
     * 
     * @param string $url
     * @param array $get
     * @param array $options
     * @param string $ms_login
     * @param string $ms_password
     * @return mixed
     * 
     * ver 2.0.0.1
     * rev 21.12.17
     * anatoliy.iwanov@yandex.ru
     */
    private function curlGetImage($url, $get, $options, $ms_login, $ms_password) {

        $error = array("error" => array());

        $default_options = array(
            CURLOPT_URL => $url . (((strpos($url, '?') === false) && !empty($get)) ? '?' : '') . http_build_query($get),
            CURLOPT_HEADER => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_BINARYTRANSFER => 1,
            CURLOPT_USERPWD => $ms_login . ":" . $ms_password
        );

        if (!$ch = curl_init()) {
            $error["error"][] = $this->language->get("error_curl_init");
            $error["error"][] = curl_errno($ch);
            $error["error"][] = curl_error($ch);
            curl_close($ch);
            return $error;
        }

        if (!curl_setopt_array($ch, ($options + $default_options))) {
            $error["error"][] = $this->language->get("error_curl_settings");
            $error["error"][] = curl_errno($ch);
            $error["error"][] = curl_error($ch);
            curl_close($ch);

            return $error;
        }

        if (!$result = curl_exec($ch)) {
            trigger_error(curl_error($ch));
        }

        curl_close($ch);
        return $result;
    }

}