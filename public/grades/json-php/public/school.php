<?php
include '../config/auth.php';
include '../config/db.php';

if ($userLogged === false) {
    $_SESSION['alert'] = [
        'type' => 'danger',
        'message' => 'Access denied: You need to be logged in',
    ];
    header("Location: index.php");
    exit;
}

if (!isset($_REQUEST['term'])) {
    echo 'Error';
    exit;
}

$stmt = $pdo->prepare('SELECT name FROM Institution WHERE name LIKE :prefix');

$stmt->execute([
    ':prefix' => $_REQUEST['term'] . "%"
]);

$retval = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
  $retval[] = $row['name'];
}

header('Content-Type: application/json; charset=utf-8');
echo(json_encode($retval, JSON_PRETTY_PRINT));
