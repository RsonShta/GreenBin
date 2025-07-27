<?php
require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/auth.php';

requireRole(['superAdmin', 'admin']);

require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/db.php';

// Retrieve the 'status' parameter from the GET request, defaulting to 'all'
$status = $_GET['status'] ?? 'all';

try {
    // Base SQL query to select report details
    $sql = "SELECT report_id, title, description, image_path, status FROM reports";
    $params = [];

    // Add WHERE clause if a specific status is requested
    if ($status !== 'all') {
        $sql .= " WHERE status = ?";
        $params[] = $status;
    }

    // Order the results by report_id in descending order (latest first)
    $sql .= " ORDER BY report_id DESC";

    // Prepare and execute the SQL query with any applicable parameters
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Fetch all matching reports as an associative array
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process each report to set the correct image path
    foreach ($reports as &$r) {
        if (!empty($r['image_path'])) {
            $r['image_path'] = "/GreenBin/uploads/" . $r['image_path'];
        } else {
            $r['image_path'] = "/GreenBin/frontend/img/no-image.png";
        }
    }

    // Return a JSON response with the report data
    echo json_encode(["success" => true, "reports" => $reports]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
