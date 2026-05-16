<?php
function h(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function getSummary(PDO $pdo): array
{
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
        $pid = $_SESSION['patient_id'] ?? 0;
        $totalPatients = $pid ? 1 : 0;
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reports WHERE patient_id = ?"); $stmt->execute([$pid]); $activeReports = (int)$stmt->fetchColumn();
        $stmt = $pdo->prepare("SELECT COALESCE(AVG(progress), 0) FROM patients WHERE id = ?"); $stmt->execute([$pid]); $avgProgress = (int)$stmt->fetchColumn();
    } else {
        $totalPatients = (int)$pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
        $activeReports = (int)$pdo->query("SELECT COUNT(*) FROM reports")->fetchColumn();
        $avgProgress = (int)$pdo->query("SELECT COALESCE(AVG(progress), 0) FROM patients")->fetchColumn();
    }

    return [
        'totalPatients' => $totalPatients,
        'activeReports' => $activeReports,
        'avgProgress' => $avgProgress,
    ];
}

function getPatients(PDO $pdo, string $search = '', int $limit = 0, int $offset = 0): array
{
    $limitSql = "";
    if ($limit > 0) {
        $limitSql = " LIMIT $limit OFFSET $offset";
    }

    $roleFilter = "";
    $params = [];
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
        $roleFilter = " WHERE id = :pid";
        $params['pid'] = $_SESSION['patient_id'] ?? 0;
    }

    if ($search !== '') {
        $searchCondition = " (name LIKE :q OR code LIKE :q OR diagnosis LIKE :q)";
        $whereClause = $roleFilter ? $roleFilter . " AND " . $searchCondition : " WHERE " . $searchCondition;
        $stmt = $pdo->prepare("SELECT * FROM patients" . $whereClause . " ORDER BY id DESC" . $limitSql);
        $params['q'] = "%$search%";
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    $stmt = $pdo->prepare("SELECT * FROM patients" . $roleFilter . " ORDER BY id DESC" . $limitSql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getTotalPatientsCount(PDO $pdo, string $search = ''): int
{
    $roleFilter = "";
    $params = [];
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
        $roleFilter = " WHERE id = :pid";
        $params['pid'] = $_SESSION['patient_id'] ?? 0;
    }

    if ($search !== '') {
        $searchCondition = " (name LIKE :q OR code LIKE :q OR diagnosis LIKE :q)";
        $whereClause = $roleFilter ? $roleFilter . " AND " . $searchCondition : " WHERE " . $searchCondition;
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM patients" . $whereClause);
        $params['q'] = "%$search%";
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM patients" . $roleFilter);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}

function getPatientById(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
    $stmt->execute([$id]);
    $patient = $stmt->fetch();
    return $patient ?: null;
}

function getPatientReports(PDO $pdo, int $patientId): array
{
    $stmt = $pdo->prepare("SELECT * FROM reports WHERE patient_id = ? ORDER BY report_date DESC, id DESC");
    $stmt->execute([$patientId]);
    return $stmt->fetchAll();
}

function getSensorReadings(PDO $pdo, int $patientId): array
{
    $stmt = $pdo->prepare("SELECT * FROM sensor_readings WHERE patient_id = ? ORDER BY session_date ASC, id ASC");
    $stmt->execute([$patientId]);
    return $stmt->fetchAll();
}

function getActivities(PDO $pdo, ?int $patientId = null): array
{
    if ($patientId) {
        $stmt = $pdo->prepare("SELECT r.report_date, r.activity_type, p.name
                FROM reports r
                JOIN patients p ON p.id = r.patient_id
                WHERE r.patient_id = ?
                ORDER BY r.id DESC
                LIMIT 5");
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }

    if (isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
        return []; // Jangan tampilkan aktivitas pasien lain jika id tidak diset
    }

    $sql = "SELECT r.report_date, r.activity_type, p.name
            FROM reports r
            JOIN patients p ON p.id = r.patient_id
            ORDER BY r.id DESC
            LIMIT 5";
    return $pdo->query($sql)->fetchAll();
}

function getWatchlist(PDO $pdo): array
{
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
        $stmt->execute([$_SESSION['patient_id'] ?? 0]);
        return $stmt->fetchAll();
    } else {
        $sql = "SELECT * FROM patients ORDER BY FIELD(status, 'high-risk', 'urgent', 'monitoring', 'stable'), progress ASC";
        return $pdo->query($sql)->fetchAll();
    }
}

function statusLabel(string $status): string
{
    $map = [
        'stable' => 'Stable',
        'monitoring' => 'Monitoring',
        'high-risk' => 'High Risk',
        'urgent' => 'Urgent',
    ];
    return $map[$status] ?? ucfirst($status);
}
?>
