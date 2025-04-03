<?php


return[
    'dsn' => 'mysql:host=localhost;dbname=elettronica',
    'username' => 'root',
    'passwd' => '',
    'options' => [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_OBJ]
];


