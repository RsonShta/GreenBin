<?php
require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/auth.php';
requireRole(['superAdmin', 'admin']);

require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/db.php';

$status = $_GET['status'] ?? 'all';

try {
    $sql = "SELECT report_id, title, description, image_path, status FROM reports";
    $params = [];

    if ($status !== 'all') {
        $sql .= " WHERE status = ?";
        $params[] = $status;
    }

    $sql .= " ORDER BY report_id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ Fix image path
    foreach ($reports as &$r) {
        if (!empty($r['image_path'])) {
            $r['image_path'] = "/GreenBin/uploads/" . $r['image_path'];
        } else {
            $r['image_path'] = "/GreenBin/frontend/img/no-image.png";
        }
    }

    echo json_encode(["success" => true, "reports" => $reports]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
