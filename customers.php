<?php
header('Content-Type: application/json');
include 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    $sql = "SELECT * FROM customers";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $customers = [];
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $customers]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontraron clientes.']);
    }
} elseif ($method == 'POST') {
    $postData = file_get_contents("php://input");
    $request = json_decode($postData, true);

    if (
        !isset($request['name']) || !isset($request['last_name']) ||
        !isset($request['phone']) || !isset($request['address'])
    ) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos.']);
        exit();
    }

    $name = $request['name'];
    $last_name = $request['last_name'];
    $phone = $request['phone'];
    $address = $request['address'];

    $stmt = $conn->prepare("INSERT INTO customers (name, last_name, phone, address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $last_name, $phone, $address);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Cliente agregado exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al agregar cliente.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}

$conn->close();
