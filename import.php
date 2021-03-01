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

if (isset($_FILES['import'])) {
    // Dosyanın barındığı yolu al
    $tmpName = $_FILES['import']['tmp_name'];
    $jsonString = file_get_contents($tmpName);
    $import = json_decode($jsonString);

    foreach ($import as $user) {
        $userCheck = $database->pdo->query("SELECT id FROM users WHERE email = '".$user->email."'")->fetch(PDO::FETCH_OBJ);
        if (!$userCheck) {
            $database->pdo->prepare("INSERT INTO users(name, password, email, phone) VALUES(?, ?, ?, ?)")->execute([
                $user->name,
                md5("secret"),
                $user->email,
                $user->phone,
            ]);

            $userId = $database->pdo->lastInsertId();
            foreach ($user->categories as $category) {
                $database->pdo->prepare("INSERT INTO categories(user_id, name) VALUES(?, ?)")->execute([
                    $userId,
                    $category->name,
                ]);
            }

            continue;
        }

        $database->pdo->prepare("UPDATE users SET name=?, email=?, phone=? WHERE id = ?")->execute([
            $user->name,
            $user->email,
            $user->phone,
            $userCheck->id,
        ]);

        foreach ($user->categories as $category) {
            $categoryId = $database->pdo->prepare("SELECT id FROM categories WHERE name = ? AND user_id = ?")->execute([
                $category->name,
                $userCheck->id,
            ]);

            if (!$categoryId)
                $database->pdo->prepare("INSERT INTO categories(name, user_id) VALUES(?, ?)")->execute([
                    $category->name,
                    $userCheck->id,
                ]);
        }
    }
}
?>
<!doctype html>
<html>
<head>
    <title></title>
</head>
<body>
<form method="post" enctype="multipart/form-data">
    <input type="file" name="import" />
    <button type="submit">Import</button>
</form>
</body>
</html>
