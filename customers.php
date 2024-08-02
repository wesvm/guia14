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
        $customer_id = $conn->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'Cliente agregado exitosamente.',
            'customer' => [
                'id' => $customer_id,
                'name' => $name,
                'last_name' => $last_name,
                'phone' => $phone,
                'address' => $address
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al agregar cliente.']);
    }

    $stmt->close();
} elseif ($method == 'PUT') {
    $putData = file_get_contents("php://input");
    $request = json_decode($putData, true);

    if (!isset($request['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID del cliente requerido.']);
        exit();
    }

    $id = $request['id'];
    $fields = [];
    $params = [];
    $types = '';

    if (isset($request['name'])) {
        $fields[] = 'name = ?';
        $params[] = $request['name'];
        $types .= 's';
    }
    if (isset($request['last_name'])) {
        $fields[] = 'last_name = ?';
        $params[] = $request['last_name'];
        $types .= 's';
    }
    if (isset($request['phone'])) {
        $fields[] = 'phone = ?';
        $params[] = $request['phone'];
        $types .= 's';
    }
    if (isset($request['address'])) {
        $fields[] = 'address = ?';
        $params[] = $request['address'];
        $types .= 's';
    }

    if (empty($fields)) {
        echo json_encode(['success' => false, 'message' => 'No se proporcionaron campos para actualizar.']);
        exit();
    }

    $sql = "UPDATE customers SET " . implode(', ', $fields) . " WHERE id = ?";
    $params[] = $id;
    $types .= 'i';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Cliente actualizado exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar cliente.']);
    }

    $stmt->close();
} elseif ($method == 'DELETE') {
    $deleteData = file_get_contents("php://input");
    $request = json_decode($deleteData, true);

    if (!isset($request['ids']) || !is_array($request['ids'])) {
        echo json_encode(['success' => false, 'message' => 'IDs de clientes no proporcionados o formato incorrecto.']);
        exit();
    }

    $ids = implode(',', array_map('intval', $request['ids']));
    $sql = "DELETE FROM customers WHERE id IN ($ids)";
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Cliente(s) eliminado(s) exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar cliente(s).']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}

$conn->close();
