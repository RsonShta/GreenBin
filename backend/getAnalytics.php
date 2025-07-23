<?php
require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/auth.php';
requireRole(['superAdmin', 'admin', 'user']);
require $_SERVER['DOCUMENT_ROOT'] . '/GreenBin/backend/includes/db.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

try {
    $analytics = [];
    
    // Determine if user should see all data (admin/superAdmin) or just their own
    $isAdmin = in_array($user_role, ['admin', 'superAdmin']);
    $whereClause = $isAdmin ? '' : 'WHERE user_id = :user_id';
    $params = $isAdmin ? [] : [':user_id' => $user_id];

    // 1. Reports by Status
    $stmt = $pdo->prepare("
        SELECT status, COUNT(*) as count 
        FROM reports 
        $whereClause
        GROUP BY status
    ");
    $stmt->execute($params);
    $statusData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 2. Reports by Month (Last 12 months)
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(date, '%Y-%m') as month,
            COUNT(*) as count,
            SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_count
        FROM reports 
        $whereClause
        AND date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(date, '%Y-%m')
        ORDER BY month ASC
    ");
    $stmt->execute($params);
    $monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 3. Response Time Analysis (for resolved reports)
    $stmt = $pdo->prepare("
        SELECT 
            AVG(DATEDIFF(updated_at, date)) as avg_response_days,
            MIN(DATEDIFF(updated_at, date)) as min_response_days,
            MAX(DATEDIFF(updated_at, date)) as max_response_days
        FROM reports 
        $whereClause
        AND status = 'resolved' 
        AND updated_at IS NOT NULL
    ");
    $stmt->execute($params);
    $responseTime = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 4. Environmental Impact
    $stmt = $pdo->prepare("
        SELECT 
            SUM(co2_reduction_kg) as total_co2,
            COUNT(*) as total_reports,
            SUM(CASE WHEN status = 'resolved' THEN co2_reduction_kg ELSE 0 END) as resolved_co2
        FROM reports 
        $whereClause
    ");
    $stmt->execute($params);
    $environmentalData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 5. Top Locations (if admin/superAdmin)
    if ($isAdmin) {
        $stmt = $pdo->prepare("
            SELECT 
                location,
                COUNT(*) as report_count,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_count
            FROM reports 
            WHERE location IS NOT NULL AND location != ''
            GROUP BY location 
            ORDER BY report_count DESC 
            LIMIT 10
        ");
        $stmt->execute();
        $locationData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $locationData = [];
    }
    
    // 6. User Activity (if admin/superAdmin)
    if ($isAdmin) {
        $stmt = $pdo->prepare("
            SELECT 
                u.user_name,
                COUNT(r.report_id) as total_reports,
                SUM(CASE WHEN r.status = 'resolved' THEN 1 ELSE 0 END) as resolved_reports,
                SUM(r.co2_reduction_kg) as co2_contribution
            FROM users u
            LEFT JOIN reports r ON u.user_id = r.user_id
            WHERE u.user_role = 'user'
            GROUP BY u.user_id, u.user_name
            HAVING total_reports > 0
            ORDER BY total_reports DESC
            LIMIT 10
        ");
        $stmt->execute();
        $userData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $userData = [];
    }
    
    // 7. Weekly Activity (Last 8 weeks)
    $stmt = $pdo->prepare("
        SELECT 
            WEEK(date, 1) as week_num,
            YEAR(date) as year,
            COUNT(*) as count,
            SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_count
        FROM reports 
        $whereClause
        AND date >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK)
        GROUP BY YEAR(date), WEEK(date, 1)
        ORDER BY year DESC, week_num DESC
    ");
    $stmt->execute($params);
    $weeklyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 8. Performance Metrics
    $currentMonth = date('Y-m');
    $lastMonth = date('Y-m', strtotime('-1 month'));
    
    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN DATE_FORMAT(date, '%Y-%m') = ? THEN 1 ELSE 0 END) as current_month_reports,
            SUM(CASE WHEN DATE_FORMAT(date, '%Y-%m') = ? THEN 1 ELSE 0 END) as last_month_reports,
            SUM(CASE WHEN DATE_FORMAT(date, '%Y-%m') = ? AND status = 'resolved' THEN 1 ELSE 0 END) as current_month_resolved,
            SUM(CASE WHEN DATE_FORMAT(date, '%Y-%m') = ? AND status = 'resolved' THEN 1 ELSE 0 END) as last_month_resolved
        FROM reports 
        $whereClause
    ");
    $executeParams = $isAdmin ? 
        [$currentMonth, $lastMonth, $currentMonth, $lastMonth] : 
        [$currentMonth, $lastMonth, $currentMonth, $lastMonth, $user_id];
    $stmt->execute($executeParams);
    $performanceData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $analytics = [
        'statusDistribution' => $statusData,
        'monthlyTrend' => $monthlyData,
        'responseTime' => $responseTime,
        'environmentalImpact' => $environmentalData,
        'topLocations' => $locationData,
        'userActivity' => $userData,
        'weeklyActivity' => $weeklyData,
        'performance' => $performanceData,
        'isAdmin' => $isAdmin
    ];
    
    echo json_encode(['success' => true, 'analytics' => $analytics]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to fetch analytics data: ' . $e->getMessage()]);
}
?>
