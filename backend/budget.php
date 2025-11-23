<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "plumber";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT id, name, email, phone, address, city, state, budget_date, notes, serviceType, created_at 
            FROM budgets
            ORDER BY id DESC";

    $result = $conn->query($sql);
    $budget = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $budget[] = $row;
        }
    }

    echo json_encode($budget);
    $conn->close();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $required = ['nome','email','telefone','endereco','cidade','uf','data','serviceType'];

    foreach ($required as $field) {
        if (!isset($data[$field])) {
            echo json_encode(['success' => false, 'error' => 'Invalid input']);
            exit;
        }
    }

    $name = $data['nome'];
    $email = $data['email'];
    $phone = $data['telefone'];
    $address = $data['endereco'];
    $city = $data['cidade'];
    $state = $data['uf'];
    $budget_date = $data['data'];
    $serviceType = intval($data['serviceType']);
    $notes = isset($data['observacoes']) ? $data['observacoes'] : null;

    $stmt = $conn->prepare("
        INSERT INTO budgets
        (name, email, phone, address, city, state, budget_date, notes, serviceType) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("ssssssssi", $name, $email, $phone, $address, $city, $state, $budget_date, $notes, $serviceType);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Insert failed']);
    }

    $stmt->close();
    $conn->close();
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid HTTP method']);
$conn->close();
?>
