<?php

require "exceptions/DatabaseConnectionException.php";
require "lib/functions.php";

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

if ($auth->check()) redirect("index.php");

$errors = [];
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $validator = Validator::create(array(
        'email' => "required|min_length:3",
        'password' => 'required|min_length:3',
    ), [
        'email' => $email,
        'password' => $password,
    ]);

    try {
        $validator->validate();

        $login = $auth->login($email, $password);

        if($login)
            redirect("index.php");
        $errors["credentials"] = ["INVALID!"];
    } catch (ValidationException $e) {
        $errors = $e->getErrors();
    }
}

?>
<!doctype html>
<html>
<head>
    <title>Giriş Yap</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
</head>
<body>
<div class="container-fluid pt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">Login</h3>
                </div>
                <div class="card-body">

                    <?php if($errors): ?>
                    <div class="alert alert-danger">
                        <?php foreach($errors as $FIELD => $rules): ?>
                        <p><?= $FIELD ?>: <?= implode(',', $rules) ?></p>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <div class="row mx-0 align-items-center mb-2">
                            <label for="email" class="col-md-4">E-Mail:</label>
                            <div class="col-md-8">
                                <input class="form-control" type="bunaEklemiycem" name="email" value="<?= $_POST['email'] ?? null ?>" id="email" placeholder="E-Mail Address" />
                            </div>
                        </div>
                        <div class="row mx-0 align-items-center mb-2">
                            <label for="password" class="col-md-4">Password:</label>
                            <div class="col-md-8">
                                <input class="form-control" id="email" type="password" name="password" value="<?= $_POST['password'] ?? null ?>" placeholder="Password" />
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">Login In</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Popper -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.6.0/dist/umd/popper.min.js" integrity="sha384-KsvD1yqQ1/1+IA7gi3P0tyJcT3vR+NdBTt13hSJ2lnve8agRGXTTyNaBYmCR/Nwi" crossorigin="anonymous"></script>
<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.min.js" integrity="sha384-nsg8ua9HAw1y0W1btsyWgBklPnCUAFLuTMS2G72MMONqmOymq585AcH49TLBQObG" crossorigin="anonymous"></script>
</body>
</html>
