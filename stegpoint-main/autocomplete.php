<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "csv_db 6";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$type = $_GET['type'] ?? ''; // 'first_name' ou 'last_name'
$query = $_GET['query'] ?? '';

if (!in_array($type, ['first_name', 'last_name'])) {
    echo json_encode([]);
    exit;
}

$col = $type === 'first_name' ? 'COL 2' : 'COL 3';
$sql = "SELECT DISTINCT `$col` AS name FROM groupement_20250620 WHERE `$col` LIKE ? LIMIT 10";
$stmt = $conn->prepare($sql);
$likeQuery = $query . '%';
$stmt->bind_param('s', $likeQuery);
$stmt->execute();
$result = $stmt->get_result();

$suggestions = [];
while ($row = $result->fetch_assoc()) {
    $suggestions[] = $row['name'];
}

echo json_encode($suggestions);
$conn->close();
?>
