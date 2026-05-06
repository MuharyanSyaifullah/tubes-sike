<?php
function h(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function getSummary(PDO $pdo): array
{
    $totalPatients = (int)$pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
    $activeReports = (int)$pdo->query("SELECT COUNT(*) FROM reports")->fetchColumn();
    $avgProgress = (int)$pdo->query("SELECT COALESCE(AVG(progress), 0) FROM patients")->fetchColumn();

    return [
        'totalPatients' => $totalPatients,
        'activeReports' => $activeReports,
        'avgProgress' => $avgProgress,
    ];
}

function getPatients(PDO $pdo, string $search = ''): array
{
    if ($search !== '') {
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE name LIKE :q OR code LIKE :q OR diagnosis LIKE :q ORDER BY id DESC");
        $stmt->execute(['q' => "%$search%"]);
        return $stmt->fetchAll();
    }

    return $pdo->query("SELECT * FROM patients ORDER BY id DESC")->fetchAll();
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

function getActivities(PDO $pdo): array
{
    $sql = "SELECT r.report_date, r.activity_type, p.name
            FROM reports r
            JOIN patients p ON p.id = r.patient_id
            ORDER BY r.id DESC
            LIMIT 5";
    return $pdo->query($sql)->fetchAll();
}

function getWatchlist(PDO $pdo): array
{
    $sql = "SELECT * FROM patients ORDER BY FIELD(status, 'high-risk', 'urgent', 'monitoring', 'stable'), progress ASC LIMIT 3";
    return $pdo->query($sql)->fetchAll();
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
