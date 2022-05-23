<?php
/**
 * define constant value
 * @author Reaza.Ahmadi <Reza.zx@live.com>
 * 
 */
require 'vendor/autoload.php';

DB::$host = 'localhost';
DB::$dbName = 'database-name';
DB::$user = 'db_username';
DB::$password = 'db_password';
DB::$encoding='UTF8';

define('_SITE_URL_','http://localhost');
define('_BASE_PATH_','/d4s/');
define('_DB_PERFIX_', 'zx_');
define('_MYSQL_ENGINE_','INNODB');
define('_KEY_','Enter a code to hash the data.');//dont change key after install...
define('_APP_VERSION_', '1.0.0');
define('_TOKEN_TIMEOUT_','3600');//secound

date_default_timezone_set('Asia/Tehran');

define('_IS_SSL_',false);