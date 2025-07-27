<?php
// Include the authentication script to ensure the user is logged in
require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/auth.php';

// Only allow users with 'superAdmin' or 'admin' roles to access this script
requireRole(['superAdmin', 'admin']);

// Include the database connection script
require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/db.php';

// Retrieve the 'report_id' from the GET request, defaulting to 0 if not provided
$reportId = $_GET['report_id'] ?? 0;

// Check if reportId is valid (non-zero); return an error response if invalid
if (!$reportId) {
    echo json_encode(["success" => false, "error" => "Invalid report ID"]);
    exit;
}

try {
    // Prepare an SQL query to fetch the report details by ID
    $stmt = $pdo->prepare("SELECT report_id, user_id, title, description, image_path, location, status
                           FROM reports WHERE report_id = ?");
    
    // Execute the query with the provided report ID
    $stmt->execute([$reportId]);

    // Fetch the report details as an associative array
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($report) {
        // If an image path exists, prepend the uploads directory path
        if (!empty($report['image_path'])) {
            $report['image_path'] = "/GreenBin/uploads/" . $report['image_path'];
        } else {
            // Use a placeholder image if no image path is set
            $report['image_path'] = "/GreenBin/frontend/img/no-image.png";
        }

        // Return a successful JSON response with the report data
        echo json_encode(["success" => true, "report" => $report]);
    } else {
        // Report not found in the database
        echo json_encode(["success" => false, "error" => "Report not found"]);
    }
} catch (Exception $e) {
    // Return an error response if an exception occurs (e.g., DB connection issue)
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
