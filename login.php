<?php
header('Content-Type: application/json');
include 'db.php';

$postData = file_get_contents("php://input");
$request = json_decode($postData, true);

if (!isset($request['username']) || !isset($request['password'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario y contraseña requeridos.']);
    exit();
}

$username = $request['username'];
$password = $request['password'];

$stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($hashed_password);
$stmt->fetch();

if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
    echo json_encode(['success' => true, 'message' => 'Ingreso valido!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas.']);
}

$stmt->close();
$conn->close();
