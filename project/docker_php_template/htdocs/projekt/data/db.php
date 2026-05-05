<?php

$host     = 'db_server';
$dbname   = 'vibeseat';
$user     = 'root';
$password = 'rootpassword';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die('Verbindung fehlgeschlagen: ' . $conn->connect_error);
}