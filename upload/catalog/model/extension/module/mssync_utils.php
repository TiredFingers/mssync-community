<?php

class ModelExtensionModuleMssyncUtils extends Model {

    public function sendWarningTo($email, $subject, $text) {

        $mail = new Mail();
        $mail->protocol = $this->config->get('config_mail_protocol');
        $mail->parameter = $this->config->get('config_mail_parameter');
        $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
        $mail->smtp_username = $this->config->get('config_mail_smtp_username');
        $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
        $mail->smtp_port = $this->config->get('config_mail_smtp_port');
        $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

        $mail->setTo($email);
        $mail->setFrom("warning-no-reply-" . $this->config->get('config_email'));
        $mail->setSender('Warning Robot');
        $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
        $mail->setText($text);
        $mail->send();
    }

    /**
     * 
     * @param string $curl_res
     * @param string $log
     * @param int $line
     * @param string $json_flag
     * @return array
     * 
     * ver 1.1
     * rev 31.08.18
     * anatoliy.iwanov@yandex.ru
     */
    //to light
    public function getJson($curl_res, $log, $line, $json_flag) {

        $result = array("error" => false);

        if (is_array($curl_res)) {

            $this->curlErrorHandler($curl_res["error"], $log, $line);
            $result["error"] = true;
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
     * @param string $curl_error
     * @param string $log
     * @param int $line
     */
    //to light
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
     * ver 1.1
     * rev 31.08.18
     */
    //to light
    public function msApiErrorHandler($log, $line, $errors) {

        $log .= $this->language->get("error_msapi") . "\n" . $this->getMsApiErrors($errors, $line);
        $this->writeToLog($log);
    }

    /**
     * 
     * @param string $log
     * @param int $line
     * @param 'result of json_last_error()' $json_last_error
     * @param 'result of json_last_error_msg()' $json_last_error_msg
     * @param char $msg_flag
     * 
     * ver 1.1
     * rev 31.08.2018
     * anatoliy.iwanov@yandex.ru
     */
    //to light
    public function jsonErrorHandler($log, $line, $json_last_error, $json_last_error_msg, $msg_flag) {

        if ($msg_flag === "d") {
            $err_msg = $this->language->get("error_json_decode");
        }
        if ($msg_flag === "e") {
            $err_msg = $this->language->get("error_json_encode");
        }

        $json_errors = $this->getJsonErrors($json_last_error, $json_last_error_msg, $line);
        $count = count($json_errors);

        for ($i = 0; $i < $count - 1; $i++) {
            $log .= $err_msg . " " . $json_errors[$i] . "\n";
        }

        $this->writeToLog($log);
    }

    /**
     * @param string $url
     * @param array $get
     * @param array $options
     * @return error array || string if success
     * 
     * ver 1.1
     * rev 31.08.18
     * anatoliy.iwanov@yandex.ru
     */
    public function curlGet($url, $ms_login, $ms_password, $get = array(), $options = array()) {

        $error = array("error" => array());

        $defaults = array(
            CURLOPT_URL => $url . (strpos($url, '?') === false ? '?' : '') . http_build_query($get),
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 4,
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
     * @param string $json_data
     * @param string $request_type
     * @param array $options
     * @return mixed
     * 
     * ver 1.2
     * rev 31.08.18
     * anatoliy.iwanov@yandex.ru
     */
    //to light
    public function curlCustomRequest($url, $ms_login, $ms_password, $json_data, $request_type, $options = array()) {

        $error = array("error" => array());

        $defaults = array(
            CURLOPT_VERBOSE => TRUE,
            CURLOPT_HTTPHEADER => ["Content-Type: application/json",
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

        $settings = (count($options) > 0 && is_array($options)) ? array_merge($options, $defaults) : $defaults;

        if (!curl_setopt_array($ch, $settings)) {
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
     * @param float $price
     * @return int
     */
    public function sitePricePrepareForMs($price) {
        return (int) $price * 100;
    }

    /**
     * 
     * @param string $log
     */
    public function writeToLog($log) {
        file_put_contents($this->config->get("mssync_log_file"), htmlentities($log, ENT_COMPAT | ENT_HTML401, "UTF-8"), FILE_APPEND | LOCK_EX);
    }

    /**
     * @param int result of poprietary func json_last_error() $json_last_error
     * @param string result of poprietary func json_last_error_msg() $error_msg
     * @param int $line
     * @return array
     */
    private function getJsonErrors($json_last_error, $error_msg, $line) {

        $data = array("error" => array("json" => array()));

        $data[] = $json_last_error;
        $data[] = $error_msg;
        $data[] = $line;


        return $data;
    }

    /**
     * @param array $errors
     * @param int $line
     * @return string
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
     * @param array $errors
     * @param int $line
     * @return array
     */
    private function getCurlErrors($errors, $line) {

        $data = array("error" => array("curl" => array("line" => "")));
        foreach ($errors as $error) {
            $data["error"]["curl"][] = $error;
        }
        $data["error"]["curl"]["line"] = $line;

        return $data;
    }

}
