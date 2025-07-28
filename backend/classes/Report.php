<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../includes/reverseGeocode.php'; // Include the standalone geocoding function

class Report
{
    private PDO $pdo;

    public function __construct()
    {
        // Get the PDO instance from the singleton Database class
        $this->pdo = Database::getInstance();
    }

    // Sanitize input to prevent XSS
    private function sanitize(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    // Create a new report with optional image upload
    public function create(array $data, array $files): array
    {
        // Check user authentication
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'message' => 'User not authenticated.', 'code' => 401];
        }

        // Sanitize inputs
        $title = $this->sanitize($data['reportTitle'] ?? '');
        $description = $this->sanitize($data['description'] ?? '');
        $location = $this->sanitize($data['location'] ?? '');

        // Parse location if coordinates are given and get readable address
        if (preg_match('/^([-]?\d{1,2}\.\d+),([-]?\d{1,3}\.\d+)$/', $location, $matches)) {
            $latitude = (float) $matches[1];
            $longitude = (float) $matches[2];
            // Use the global getAddressFromCoordinates function
            $location = getAddressFromCoordinates($latitude, $longitude);
        }

        // Validate required fields
        if (empty($title) || empty($description)) {
            return ['success' => false, 'message' => 'Title and Description are required.', 'code' => 400];
        }

        // Handle file upload if image is provided
        $uploadedImagePath = null;

        if (!empty($files['photo']) && $files['photo']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $files['photo']['tmp_name'];
            $fileName = basename($files['photo']['name']);
            $fileSize = $files['photo']['size'];
            $fileType = mime_content_type($fileTmpPath);

            // Allowed image types
            $allowedTypes = ['image/png', 'image/jpeg', 'image/gif'];
            if (!in_array($fileType, $allowedTypes)) {
                return ['success' => false, 'message' => 'Invalid file type. Only PNG, JPG, GIF allowed.', 'code' => 400];
            }

            if ($fileSize > 5 * 1024 * 1024) {
                return ['success' => false, 'message' => 'File size exceeds 5MB limit.', 'code' => 400];
            }

            // Ensure upload directory exists
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Create a unique filename and move uploaded file
            $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
            $newFileName = uniqid('report_', true) . '.' . $fileExt;
            $destPath = $uploadDir . $newFileName;

            if (!move_uploaded_file($fileTmpPath, $destPath)) {
                return ['success' => false, 'message' => 'Error saving uploaded file.', 'code' => 500];
            }

            $uploadedImagePath = $newFileName;
        }

        // Insert the new report into the database
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("
                INSERT INTO reports (user_id, title, description, image_path, location, status, date, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, 'pending', CURDATE(), NOW(), NOW())
            ");
            $stmt->execute([$_SESSION['user_id'], $title, $description, $uploadedImagePath, $location]);
            $newReportId = $this->pdo->lastInsertId();
            $this->pdo->commit();

            // Fetch the inserted report
            $stmtFetch = $this->pdo->prepare("SELECT * FROM reports WHERE report_id = ?");
            $stmtFetch->execute([$newReportId]);
            $newReport = $stmtFetch->fetch(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'message' => 'Report submitted successfully',
                'report' => [
                    'report_id' => (int)$newReport['report_id'],
                    'title' => htmlspecialchars($newReport['title']),
                    'description' => htmlspecialchars($newReport['description']),
                    'location' => htmlspecialchars($newReport['location']),
                    'status' => $newReport['status'],
                    'date' => $newReport['date'],
                    'image_path' => $newReport['image_path']
                ],
                'code' => 201
            ];
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("DB Error in Report.php: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred.', 'code' => 500];
        }
    }

    // Fetch a report by its ID
    public function getReportById(int $reportId): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM reports WHERE report_id = ?");
            $stmt->execute([$reportId]);
            $report = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($report) {
                return ['success' => true, 'report' => $report, 'code' => 200];
            } else {
                return ['success' => false, 'message' => 'Report not found.', 'code' => 404];
            }
        } catch (PDOException $e) {
            error_log("DB Error in Report.php: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred.', 'code' => 500];
        }
    }

    // Get all reports ordered by creation date
    public function getAllReports(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM reports ORDER BY created_at DESC");
            $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'reports' => $reports, 'code' => 200];
        } catch (PDOException $e) {
            error_log("DB Error in Report.php: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred.', 'code' => 500];
        }
    }

    // Update a report's title and description if owned by the logged-in user
    public function update(int $reportId, array $data): array
    {
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'message' => 'User not authenticated.', 'code' => 401];
        }

        $title = $this->sanitize($data['reportTitle'] ?? '');
        $description = $this->sanitize($data['description'] ?? '');
        $location = $this->sanitize($data['location'] ?? ''); // Get location from data

        // Parse location if coordinates are given and get readable address
        if (preg_match('/^([-]?\d{1,2}\.\d+),([-]?\d{1,3}\.\d+)$/', $location, $matches)) {
            $latitude = (float) $matches[1];
            $longitude = (float) $matches[2];
            // Use the global getAddressFromCoordinates function
            $location = getAddressFromCoordinates($latitude, $longitude);
        }

        if (empty($title) || empty($description)) {
            return ['success' => false, 'message' => 'Title and Description are required.', 'code' => 400];
        }

        try {
            // Handle file upload if a new image is provided
            $uploadedImagePath = $data['existingImage'] ?? null; // Keep existing image by default

            if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['photo']['tmp_name'];
                $fileName = basename($_FILES['photo']['name']);
                $fileSize = $_FILES['photo']['size'];
                $fileType = mime_content_type($fileTmpPath);

                // Allowed image types
                $allowedTypes = ['image/png', 'image/jpeg', 'image/gif'];
                if (!in_array($fileType, $allowedTypes)) {
                    return ['success' => false, 'message' => 'Invalid file type. Only PNG, JPG, GIF allowed.', 'code' => 400];
                }

                if ($fileSize > 5 * 1024 * 1024) { // 5MB limit
                    return ['success' => false, 'message' => 'File size exceeds 5MB limit.', 'code' => 400];
                }

                // Ensure upload directory exists
                $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Create a unique filename
                $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
                $newFileName = uniqid('report_', true) . '.' . $fileExt;
                $destPath = $uploadDir . $newFileName;

                // Move the uploaded file
                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    // If a new image is uploaded, delete the old one if it exists
                    if (!empty($data['existingImage'])) {
                        $oldImagePath = $uploadDir . basename($data['existingImage']);
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    $uploadedImagePath = $newFileName; // Set the new image path
                } else {
                    return ['success' => false, 'message' => 'Error saving uploaded file.', 'code' => 500];
                }
            }

            // Update the report in the database
            $stmt = $this->pdo->prepare("
                UPDATE reports 
                SET title = ?, description = ?, location = ?, image_path = ?, updated_at = NOW() 
                WHERE report_id = ? AND user_id = ?
            ");
            $stmt->execute([$title, $description, $location, $uploadedImagePath, $reportId, $_SESSION['user_id']]);

            if ($stmt->rowCount() > 0) {
                // Fetch the updated report to return it
                $stmtFetch = $this->pdo->prepare("SELECT * FROM reports WHERE report_id = ?");
                $stmtFetch->execute([$reportId]);
                $updatedReport = $stmtFetch->fetch(PDO::FETCH_ASSOC);

                return [
                    'success' => true,
                    'message' => 'Report updated successfully.',
                    'report' => $updatedReport,
                    'code' => 200
                ];
            } else {
                // If no rows were affected, it might be because the data was the same
                // or the report doesn't exist. Let's check if the report exists.
                $stmtCheck = $this->pdo->prepare("SELECT * FROM reports WHERE report_id = ? AND user_id = ?");
                $stmtCheck->execute([$reportId, $_SESSION['user_id']]);
                if ($stmtCheck->rowCount() > 0) {
                    // Report exists, but no changes were made. Return success and the report.
                    return [
                        'success' => true,
                        'message' => 'No changes were made to the report.',
                        'report' => $stmtCheck->fetch(PDO::FETCH_ASSOC),
                        'code' => 200
                    ];
                }
                return ['success' => false, 'message' => 'Report not found or user not authorized to update.', 'code' => 404];
            }
        } catch (PDOException $e) {
            error_log("DB Error in Report.php: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred.', 'code' => 500];
        }
    }

    // Delete a report if owned by the logged-in user
    public function delete(int $reportId): array
    {
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'message' => 'User not authenticated.', 'code' => 401];
        }

        try {
            $stmt = $this->pdo->prepare("DELETE FROM reports WHERE report_id = ? AND user_id = ?");
            $stmt->execute([$reportId, $_SESSION['user_id']]);

            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Report deleted successfully.', 'code' => 200];
            } else {
                return ['success' => false, 'message' => 'Report not found or user not authorized to delete.', 'code' => 404];
            }
        } catch (PDOException $e) {
            error_log("DB Error in Report.php: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred.', 'code' => 500];
        }
    }

    // Get all reports created by a specific user
    public function getReportsByUserId(int $userId): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM reports WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$userId]);
            $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'reports' => $reports, 'code' => 200];
        } catch (PDOException $e) {
            error_log("DB Error in Report.php: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred.', 'code' => 500];
        }
    }
}
