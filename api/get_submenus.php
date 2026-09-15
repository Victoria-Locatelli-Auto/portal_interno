<?php
/**
 * API para carregar submenus de um menu
 */

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$menu_id = (int)($_GET['menu_id'] ?? 0);

if ($menu_id <= 0) {
    echo json_encode(['error' => 'Menu ID inválido', 'submenus' => []]);
    exit;
}

$stmt = $conn->prepare("SELECT id, title FROM custom_submenus WHERE menu_id = ? AND is_active = TRUE ORDER BY order_position ASC");
$stmt->bind_param("i", $menu_id);
$stmt->execute();
$result = $stmt->get_result();
$submenus = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode(['submenus' => $submenus]);
