<?php
namespace k1n\core;

define('DB_HOST', 'localhost');
define('DB_NAME', '');
define('DB_USER', '');
define('DB_PASS', '');
define('DB_CHAR', 'utf8');

/**
 * Работа с БД
 **/
class DB
{
    protected static $instance = null;
    public function __construct() {}
    public function __clone() {}

    public static function instance()
    {
        if (self::$instance === null)
        {
            $opt  = array(
                PDO::ATTR_ERRMODE=> PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => TRUE,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"
            );
            $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHAR;
            self::$instance = new PDO($dsn, DB_USER, DB_PASS, $opt);

        }
        return self::$instance;
    }

    public static function __callStatic($method, $args)
    {
        return call_user_func_array(array(self::instance(), $method), $args);
    }

    public static function run($sql, $args = null)
    {
        $stmt = self::instance()->prepare($sql);
        $stmt->execute($args);
        return $stmt;
    }
}



/*
 * Класс для вывода информации в формате json
 * */
class Out
{
    static function CORS() {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST');
        header("Access-Control-Allow-Headers: X-Requested-With");
    }

    static function send($data, $status = 200) {
        header('Content-Type: application/json');
        echo json_encode(array("status"=>$status, "result" => $data));
        exit;
    }
}




class Request
{
    static function post($url, $data, $headers = false) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        if ($headers !== false) curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        return curl_exec($curl);
    }

    static function get($url, $headers = false) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        if ($headers !== false) curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        return curl_exec($curl);
    }
}




class Filter
{
    static function O($str) {
        if ($str == "null" || $str == "NULL" || $str == null) return "0";
        return $str;
    }


    static function phone($phone)
    {
        if (preg_match('/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/', $phone)) return true;
        else return false;
    }


    static function phone_filter($str) {
        return implode('', array_filter(str_split($str), function($digit) {
            return (is_numeric($digit));
        }));
    }

    static function email($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) return true;
        else return false;
    }



    static function only_digits($str) {
        return preg_replace("/[^0-9]/", '', $str);
    }


    static public function isDate($value) {
        if (!$value) {
            return false;
        } else {
            $date = date_parse($value);
            return checkdate($date['month'], $date['day'], $date['year']);
        }
    }
}




class Tools
{
    static function forceFilePutContents ($filepath, $message) {
        try {
            if (strlen($message) < 10) exit;
            $isInFolder = preg_match("/^(.*)\/([^\/]+)$/", $filepath, $filepathMatches);
            if($isInFolder) {
                $folderName = $filepathMatches[1];
                $fileName = $filepathMatches[2];
                if (!is_dir($folderName)) {
                    mkdir($folderName, 0777, true);
                }
            }
            file_put_contents($filepath, $message);
        } catch (Exception $e) {
        }
    }


    static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }


    static function getIp() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }


    static function maskEmail($email, $minLength = 3, $maxLength = 10, $mask = "***") {
        $atPos = strrpos($email, "@");
        $name = substr($email, 0, $atPos);
        $len = strlen($name);
        $domain = substr($email, $atPos);

        if (($len / 2) < $maxLength) $maxLength = ($len / 2);

        $shortenedEmail = (($len > $minLength) ? substr($name, 0, $maxLength) : "");
        return  "{$shortenedEmail}{$mask}{$domain}";
    }
}