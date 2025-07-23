<?php

require_once __DIR__ . '/Database.php';

class Report
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    private function sanitize(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    private function getAddressFromCoordinates(float $latitude, float $longitude): string
    {
        $apiKey = '920278be1d4d4f0aa2542e3aaf52b5e9';
        $url = "https://api.opencagedata.com/geocode/v1/json?q={$latitude}+{$longitude}&key={$apiKey}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response);

        if ($data && $data->status->code === 200 && count($data->results) > 0) {
            return $data->results[0]->formatted;
        }

        return 'Unknown location';
    }

    public function create(array $data, array $files): array
    {
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'message' => 'User not authenticated.', 'code' => 401];
        }

        $title = $this->sanitize($data['reportTitle'] ?? '');
        $description = $this->sanitize($data['description'] ?? '');
        $location = $this->sanitize($data['location'] ?? '');
        $latitude = null;
        $longitude = null;

        if (preg_match('/^([-]?\d{1,2}\.\d+),([-]?\d{1,3}\.\d+)$/', $location, $matches)) {
            $latitude = (float) $matches[1];
            $longitude = (float) $matches[2];
            $location = $this->getAddressFromCoordinates($latitude, $longitude);
        }

        if (empty($title) || empty($description)) {
            return ['success' => false, 'message' => 'Title and Description are required.', 'code' => 400];
        }

        $uploadedImagePath = null;

        if (!empty($files['photo']) && $files['photo']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $files['photo']['tmp_name'];
            $fileName = basename($files['photo']['name']);
            $fileSize = $files['photo']['size'];
            $fileType = mime_content_type($fileTmpPath);

            $allowedTypes = ['image/png', 'image/jpeg', 'image/gif'];
            if (!in_array($fileType, $allowedTypes)) {
                return ['success' => false, 'message' => 'Invalid file type. Only PNG, JPG, GIF allowed.', 'code' => 400];
            }

            if ($fileSize > 5 * 1024 * 1024) {
                return ['success' => false, 'message' => 'File size exceeds 5MB limit.', 'code' => 400];
            }

            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
            $newFileName = uniqid('report_', true) . '.' . $fileExt;
            $destPath = $uploadDir . $newFileName;

            if (!move_uploaded_file($fileTmpPath, $destPath)) {
                return ['success' => false, 'message' => 'Error saving uploaded file.', 'code' => 500];
            }

            $uploadedImagePath = $newFileName;
        }

        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("
                INSERT INTO reports (user_id, title, description, image_path, location, status, date, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, 'pending', CURDATE(), NOW(), NOW())
            ");
            $stmt->execute([$_SESSION['user_id'], $title, $description, $uploadedImagePath, $location]);
            $newReportId = $this->pdo->lastInsertId();
            $this->pdo->commit();

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

    public function update(int $reportId, array $data): array
    {
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'message' => 'User not authenticated.', 'code' => 401];
        }

        $title = $this->sanitize($data['reportTitle'] ?? '');
        $description = $this->sanitize($data['description'] ?? '');

        if (empty($title) || empty($description)) {
            return ['success' => false, 'message' => 'Title and Description are required.', 'code' => 400];
        }

        try {
            $stmt = $this->pdo->prepare("
                UPDATE reports 
                SET title = ?, description = ?, updated_at = NOW() 
                WHERE report_id = ? AND user_id = ?
            ");
            $stmt->execute([$title, $description, $reportId, $_SESSION['user_id']]);

            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Report updated successfully.', 'code' => 200];
            } else {
                return ['success' => false, 'message' => 'Report not found or user not authorized to update.', 'code' => 404];
            }
        } catch (PDOException $e) {
            error_log("DB Error in Report.php: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred.', 'code' => 500];
        }
    }

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
}
