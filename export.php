<?php

require "exceptions/DatabaseConnectionException.php";
require "lib/functions.php";
require "lib/Database.php";

$config = include "config.php";

try {
    $database = new Lib\Database(
        "mysql:host=".$config['DB_HOST'].";dbname={$config['DB_NAME']}",
        $config['DB_USER'],
        $config['DB_PASSWORD']
    );
} catch (DatabaseConnectionException $e) {
    die("<h1>Veritabanı Bağlantı Hatası!</h1>");
}

$output = [];
$users = $database->pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_OBJ);
foreach ($users as $user) {
    $categories = $database->pdo->query("SELECT name FROM categories WHERE user_id = ".$user->id)->fetchAll(PDO::FETCH_OBJ);
    $output[] = [
        "name" => $user->name,
        "email" => $user->email,
        "phone" => $user->phone,
        "categories" => $categories,
    ];
}

header("Content-disposition: attachment; filename=users.json");
header("Content-type: application/json");
echo json_encode($output, JSON_PRETTY_PRINT);