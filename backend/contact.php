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
    $sql = "SELECT id, name, email, message, created_at FROM contacts ORDER BY id DESC";
    $result = $conn->query($sql);

    $contacts = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $contacts[] = $row;
        }
    }

    echo json_encode($contacts);
    $conn->close();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['nome'], $data['email'], $data['mensagem'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
        exit;
    }

    $name = $data['nome'];
    $email = $data['email'];
    $message = $data['mensagem'];

    $stmt = $conn->prepare("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $message);

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
