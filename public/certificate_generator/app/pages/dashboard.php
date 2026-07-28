<?php
declare(strict_types=1);

// ─── ERP-Integrated Dashboard ────────────────────────────────────────────────
// Pulls real stats from psnf_drm: students, invoices, certificates, participants
// Falls back to 0 safely when tables don't exist yet.

$safeCount = static function (string $sql, array $params = []): int {
    try {
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    } catch (\Throwable $e) {
        return 0;
    }
};

$safeRows = static function (string $sql, array $params = []): array {
    try {
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    } catch (\Throwable $e) {
        return [];
    }
};

// ─── Certificate Generator native tables ─────────────────────────────────────
$totalConferences  = $safeCount('SELECT COUNT(*) FROM conferences');
$totalTemplates    = $safeCount('SELECT COUNT(*) FROM certificate_types');
$totalParticipants = $safeCount('SELECT COUNT(*) FROM participants');
$totalGenerated    = $safeCount('SELECT COUNT(*) FROM generated_certificates');
$totalEmails       = $safeCount("SELECT COUNT(*) FROM email_logs WHERE status = 'sent'");

// ─── ERP-integrated stats ─────────────────────────────────────────────────────
// Students
$totalStudents    = $safeCount('SELECT COUNT(*) FROM students WHERE deleted_at IS NULL');
// Fees/Invoices
$totalInvoices    = $safeCount('SELECT COUNT(*) FROM fee_invoices');
$pendingInvoices  = $safeCount("SELECT COUNT(*) FROM fee_invoices WHERE status = 'pending'");
$paidInvoices     = $safeCount("SELECT COUNT(*) FROM fee_invoices WHERE status = 'paid'");
// Receipts
$totalReceipts    = $safeCount('SELECT COUNT(*) FROM receipts');
// ERP Certificates (from certificates table linked to students)
$erpCertificates  = $safeCount('SELECT COUNT(*) FROM certificates');
$thisMonthCerts   = $safeCount("SELECT COUNT(*) FROM generated_certificates WHERE MONTH(generated_at)=MONTH(NOW()) AND YEAR(generated_at)=YEAR(NOW())");
$thisMonthParts   = $safeCount("SELECT COUNT(*) FROM participants WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())");

// ─── Recent participants (cert gen) ──────────────────────────────────────────
$recentParticipants = $safeRows(
    'SELECT p.name, p.email, ct.name AS template_name, p.status, p.created_at
     FROM participants p
     INNER JOIN certificate_types ct ON ct.id = p.certificate_type_id
     ORDER BY p.created_at DESC LIMIT 8'
);

// ─── Recent ERP students ──────────────────────────────────────────────────────
$recentStudents = $safeRows(
    'SELECT first_name, last_name, admission_number, created_at FROM students
     WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT 5'
);

// ─── Recent invoices ──────────────────────────────────────────────────────────
$recentInvoices = $safeRows(
    'SELECT fi.invoice_number, fi.total_amount, fi.status, fi.due_date,
            s.first_name, s.last_name
     FROM fee_invoices fi
     JOIN students s ON s.id = fi.student_id
     ORDER BY fi.created_at DESC LIMIT 5'
);

// ─── Storage size ─────────────────────────────────────────────────────────────
$storageQuotaGb = 10.0;
$generatedStorageBytes = 0;
if (defined('GENERATED_ROOT') && is_dir(GENERATED_ROOT)) {
    try {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(GENERATED_ROOT, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) { if ($f->isFile()) $generatedStorageBytes += $f->getSize(); }
    } catch (\Throwable $e) {}
}
$storageUsagePercent = $storageQuotaGb > 0
    ? min(100.0, round(($generatedStorageBytes / ($storageQuotaGb * 1073741824)) * 100, 1))
    : 0.0;

// ─── Getting Started Checklist ────────────────────────────────────────────────
$gettingStarted = [
    ['label' => 'Create a certificate event / category',   'done' => $totalConferences > 0,  'url' => url('conferences')],
    ['label' => 'Design a certificate template',           'done' => $totalTemplates > 0,    'url' => url('certificate-types')],
    ['label' => 'Add students as recipients',              'done' => $totalParticipants > 0, 'url' => url('participants')],
    ['label' => 'Generate & download certificates',        'done' => $totalGenerated > 0,    'url' => url('generate')],
];
$gettingStartedCompleted = count(array_filter($gettingStarted, fn($i) => $i['done']));

render_view('dashboard.php', [
    'pageTitle'                => 'Certificate Generator Dashboard',
    'currentPeriodLabel'       => (new DateTimeImmutable('first day of this month'))->format('M j') . ' – ' . (new DateTimeImmutable('last day of this month'))->format('M j, Y'),
    // Cert gen native
    'totalConferences'         => $totalConferences,
    'totalParticipants'        => $totalParticipants,
    'totalGenerated'           => $totalGenerated,
    'totalEmails'              => $totalEmails,
    'openRate'                 => 0.0,
    'totalTemplates'           => $totalTemplates,
    'activeTemplates'          => $totalTemplates,
    // ERP bridge
    'totalStudents'            => $totalStudents,
    'totalInvoices'            => $totalInvoices,
    'pendingInvoices'          => $pendingInvoices,
    'paidInvoices'             => $paidInvoices,
    'totalReceipts'            => $totalReceipts,
    'erpCertificates'          => $erpCertificates,
    'thisMonthCerts'           => $thisMonthCerts,
    'thisMonthParts'           => $thisMonthParts,
    // Recent data
    'recentParticipants'       => $recentParticipants,
    'recentStudents'           => $recentStudents,
    'recentInvoices'           => $recentInvoices,
    // Storage
    'quota' => [
        'certificate_cap'      => 25000,
        'certificate_percent'  => min(100.0, round(($totalGenerated / 25000) * 100, 1)),
        'storage_cap_gb'       => $storageQuotaGb,
        'storage_used_bytes'   => $generatedStorageBytes,
        'storage_percent'      => $storageUsagePercent,
    ],
    'gettingStarted'           => $gettingStarted,
    'gettingStartedCompleted'  => $gettingStartedCompleted,
    'trend' => [
        'certificates' => 0.0,
        'emails'       => 0.0,
        'openRate'     => 0.0,
        'templates'    => 0.0,
    ],
]);
