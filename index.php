<?php

require "exceptions/DatabaseConnectionException.php";
require "lib/functions.php";
require "lib/database.php";
require "lib/auth.php";

$config = include "config.php";

try {
    $database = new Database(
        "mysql:host=".$config['DB_HOST'].";dbname={$config['DB_NAME']}",
        $config['DB_USER'],
        $config['DB_PASSWORD']
    );
} catch (DatabaseConnectionException $e) {
    die("<h1>Veritabanı Bağlantı Hatası!</h1>");
}

$auth = new Auth($database);

if (!$auth->check()) redirect("login.php");

?>
<!doctype html>
<html>
<head>
    <title>Not Defterim - Kategoriler</title>
</head>
<body>

</body>
</html>