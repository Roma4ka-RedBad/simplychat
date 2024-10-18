<?php

function getConnection()
{
    $host = '127.127.126.50';
    $dbname = 'simplychat';
    $user = 'root';
    $password = '';
    try {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => true
        ];
        return new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password, $options);
    } catch (PDOException $e) {
        toast('Error', 'Database connect error: ' . $e->getMessage());
        exit;
    }
}