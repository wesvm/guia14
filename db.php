<?php

$conn = new mysqli('localhost', 'root', '', 'db_movil');

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
