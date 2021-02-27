<?php

class Auth
{
    protected $user;
    protected $database;

    public function __construct($database) {
        $this->database = $database;

        session_start();

        if (isset($_SESSION['user_id']))
            $this->user = $_SESSION['user_id'];
    }

    public function login($email, $password)  {
        $user = $this->database->selectOne("users", ["email" => "\"".$email."\""]);

        $status = $user && $user->password == md5($password);

        if($status)
            $_SESSION['user_id'] = $user->id;

        return $status;
    }

    public function check() {
        return isset($_SESSION['user_id']);
    }

    public function logout() {
        if (!$this->check())
            return;

        unset($_SESSION['user_id']);
    }
}