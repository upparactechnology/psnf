<?php
/** @var array<int, array{type: string, message: string}> $flashes */
$user = current_user();
$currentPage = (string) ($_GET['page'] ?? 'dashboard');
$isEditorPage = $currentPage === 'field-editor';
$pageTitle = $pageTitle ?? APP_NAME;
$userRole = (string) ($user['role'] ?? 'member');
$userRoleLabel = strtoupper(str_replace('_', ' ', $userRole));

$humanizePage = static function (string $page): string {
    $text = str_replace(['-', '_'], ' ', trim($page));
    $text = preg_replace('/\s+/', ' ', $text) ?? '';
    return $text === '' ? 'Dashboard' : ucwords($text);
};

$pageHeaderByPage = [
    'dashboard' => [
        'section' => 'Workspace',
        'title' => 'Dashboard',
        'description' => 'Track certificate operations, delivery status, and usage trends in one place.',
        'primary_action' => [
            'label' => 'Create Template',
            'url' => url('certificate-types'),
        ],
    ],
    'certificate-types' => [
        'section' => 'Workspace',
        'title' => 'Templates',
        'description' => 'Design and manage your digital credentials. Create professional assets for every milestone.',
        'primary_action' => [
            'label' => '+ Create New Template',
            'url' => '#templateStartCard',
        ],
    ],
    'generate' => [
        'section' => 'Operations',
        'title' => 'Certificates',
        'description' => 'Manage and track issued certificates across your organization.',
    ],
    'participants' => [
        'section' => 'Operations',
        'title' => 'Students',
        'description' => 'Generate and manage certificates for enrolled students.',
    ],
    'participants-import' => [
        'section' => 'Operations',
        'title' => 'Import Recipients',
        'description' => 'Upload CSV files and map columns to add participants in bulk.',
    ],
    'emails' => [
        'section' => 'Operations',
        'title' => 'Campaigns',
        'description' => 'Configure email delivery templates and track campaign activity.',
    ],
    'conferences' => [
        'section' => 'Master Data',
        'title' => 'Conferences',
        'description' => 'Manage conference workspaces, years, and related certificate programs.',
    ],
    'users' => [
        'section' => 'Admin',
        'title' => 'Users',
        'description' => 'Manage access, roles, and workspace permissions for your team.',
    ],
    'settings' => [
        'section' => 'Admin',
        'title' => 'Settings',
        'description' => 'Configure application defaults, appearance, and operational preferences.',
    ],
    'verify' => [
        'section' => 'Public',
        'title' => 'Verify Certificate',
        'description' => 'Validate issued credentials with code-based verification.',
    ],
    'field-editor' => [
        'section' => 'Master Data',
        'title' => 'Field Editor',
        'description' => 'Position dynamic fields and fine-tune template layout precisely.',
    ],
];

$defaultPageHeader = [
    'section' => 'Workspace',
    'title' => $pageTitle !== APP_NAME ? (string) $pageTitle : $humanizePage($currentPage),
    'description' => 'Manage workspace activities and certificate workflows from one place.',
    'primary_action' => null,
];

$activePageHeader = $defaultPageHeader;
if (isset($pageHeaderByPage[$currentPage])) {
    $activePageHeader = array_replace($activePageHeader, $pageHeaderByPage[$currentPage]);
}

if (isset($headerMeta) && is_array($headerMeta)) {
    $activePageHeader = array_replace($activePageHeader, $headerMeta);
}

$headerActions = [];
if (isset($activePageHeader['actions']) && is_array($activePageHeader['actions'])) {
    $headerActions = array_values(array_filter($activePageHeader['actions'], static fn ($action): bool => is_array($action)));
}

$userName = (string) ($user['full_name'] ?? 'User');
$userInitials = '';
foreach (preg_split('/\s+/', trim($userName)) ?: [] as $part) {
    if ($part === '') {
        continue;
    }

    $userInitials .= strtoupper(substr($part, 0, 1));
    if (strlen($userInitials) >= 2) {
        break;
    }
}

if ($userInitials === '') {
    $userInitials = 'U';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=2.5.1">
    <?php if ($isEditorPage): ?>
        <link rel="stylesheet" href="assets/css/editor-v3.css?v=3.0.0">
    <?php endif; ?>
</head>
<body class="<?= $isEditorPage ? 'field-editor-page' : '' ?>">
<?php if ($user !== null): ?>
<div class="app-shell<?= $isEditorPage ? ' app-shell-editor' : '' ?>">
    <aside class="sidebar" id="appSidebar">
        <div class="sidebar-header">
            <div class="brand-block">
                <a href="/psnf/public/dashboard" style="text-decoration:none; color:inherit; display:flex; items-center:center; gap:8px;" title="Back to Main ERP Launcher">
                    <span class="brand-logo" aria-hidden="true"></span>
                    <div class="brand-copy">
                        <h1>Certificates</h1>
                        <p>← Back to ERP Launcher</p>
                    </div>
                </a>
            </div>

            <a class="sidebar-utility-btn" href="<?= e(url('logout')) ?>" title="Sign Out" aria-label="Sign Out">
                <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false">
                    <path d="M9 3h8a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H9" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M14 12H3m4-4-4 4 4 4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>

        <div class="sidebar-user-card sidebar-user-card-top">
            <span class="sidebar-user-avatar"><?= e($userInitials) ?></span>
            <div>
                <strong><?= e($userName) ?></strong>
                <small><?= e($userRoleLabel) ?></small>
            </div>
        </div>

        <div class="sidebar-search">
            <label class="sr-only" for="sidebarMenuSearch">Filter menu</label>
            <input id="sidebarMenuSearch" type="search" placeholder="Filter menu" autocomplete="off">
        </div>

        <nav class="sidebar-nav">
            <div class="nav-group">
                <div class="nav-group-title">
                    <span>Operations</span>
                    <span class="nav-group-caret" aria-hidden="true"></span>
                </div>

                <a class="nav-link" href="/psnf/public/dashboard" style="color: #6366f1; font-weight: 700; border-bottom: 1px solid rgba(99,102,241,0.2); margin-bottom: 4px; padding-bottom: 8px;">
                    <span class="nav-icon" aria-hidden="true">←</span>
                    <span class="nav-link-title">Back to ERP</span>
                </a>

                <a class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>" href="<?= e(url('dashboard')) ?>">
                    <span class="nav-icon nav-icon-dashboard" aria-hidden="true"></span>
                    <span class="nav-link-title">Dashboard</span>
                </a>

                <a class="nav-link <?= ($currentPage === 'participants' || $currentPage === 'participants-import') ? 'active' : '' ?>" href="<?= e(url('participants')) ?>">
                    <span class="nav-icon nav-icon-recipients" aria-hidden="true"></span>
                    <span class="nav-link-title">Students</span>
                </a>
            </div>

            <div class="nav-group">
                <div class="nav-group-title">
                    <span>Master Data</span>
                    <span class="nav-group-caret" aria-hidden="true"></span>
                </div>

                <a class="nav-link <?= $currentPage === 'certificate-types' ? 'active' : '' ?>" href="<?= e(url('certificate-types')) ?>">
                    <span class="nav-icon nav-icon-templates" aria-hidden="true"></span>
                    <span class="nav-link-title">Templates</span>
                </a>

                <a class="nav-link <?= $currentPage === 'field-editor' ? 'active' : '' ?>" href="<?= e(url('field-editor')) ?>">
                    <span class="nav-icon nav-icon-editor" aria-hidden="true"></span>
                    <span class="nav-link-title">Field Editor</span>
                </a>
            </div>

            <div class="nav-group">
                <div class="nav-group-title">
                    <span>Actions</span>
                    <span class="nav-group-caret" aria-hidden="true"></span>
                </div>

                <a class="nav-link <?= $currentPage === 'generate' ? 'active' : '' ?>" href="<?= e(url('generate')) ?>">
                    <span class="nav-icon" aria-hidden="true">📄</span>
                    <span class="nav-link-title">Certificates</span>
                </a>

                <a class="nav-link <?= $currentPage === 'emails' ? 'active' : '' ?>" href="<?= e(url('emails')) ?>">
                    <span class="nav-icon" aria-hidden="true">✉️</span>
                    <span class="nav-link-title">Email Campaigns</span>
                </a>

                <a class="nav-link <?= $currentPage === 'printer' ? 'active' : '' ?>" href="<?= e(url('printer')) ?>">
                    <span class="nav-icon" aria-hidden="true">🖨️</span>
                    <span class="nav-link-title">Print Queue</span>
                </a>

                <?php if ($userRole === 'super_admin'): ?>
                <a class="nav-link <?= $currentPage === 'settings' ? 'active' : '' ?>" href="<?= e(url('settings')) ?>">
                    <span class="nav-icon" aria-hidden="true">⚙️</span>
                    <span class="nav-link-title">Settings</span>
                </a>
                <?php endif; ?>
            </div>
        </nav>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay" hidden></div>

    <main class="main-content<?= $isEditorPage ? ' main-content-editor' : '' ?>">
        <?php if (!$isEditorPage): ?>
            <header class="topbar">
                <div class="topbar-left">
                    <button type="button" class="sidebar-toggle" data-sidebar-toggle aria-controls="appSidebar" aria-expanded="false">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>

                    <div class="topbar-heading">
                        <h2><?= e((string) ($activePageHeader['title'] ?? $pageTitle)) ?></h2>
                        <small><?= e((string) ($user['full_name'] ?? 'User')) ?> · <?= e($userRoleLabel) ?></small>
                    </div>
                </div>

                <div class="topbar-search-wrap">
                    <label class="sr-only" for="layoutSearch">Search credentials</label>
                    <input id="layoutSearch" type="search" placeholder="Search credentials..." autocomplete="off">
                </div>

                <div class="topbar-actions">
                    <?php if ($currentPage === 'dashboard'): ?>
                        <a class="button button-muted topbar-cta" href="<?= e(url('participants-import')) ?>">Upload Recipients</a>
                        <a class="button button-muted topbar-cta" href="<?= e(url('generate', ['step' => 1])) ?>">Generate Certificates</a>
                        <a class="button topbar-cta" href="<?= e(url('certificate-types')) ?>">Create Template</a>
                    <?php else: ?>
                        <?php foreach ($headerActions as $action): ?>
                            <?php
                            $actionLabel = (string) ($action['label'] ?? 'Action');
                            $actionUrl = (string) ($action['url'] ?? '#');
                            $actionClass = trim('button button-muted topbar-cta ' . (string) ($action['class'] ?? ''));
                            ?>
                            <a class="<?= e($actionClass) ?>" href="<?= e($actionUrl) ?>"><?= e($actionLabel) ?></a>
                        <?php endforeach; ?>
                        <div class="dropdown" data-dropdown>
                            <button type="button" class="button button-muted dropdown-toggle" data-dropdown-trigger>
                                Quick Actions
                            </button>
                            <div class="dropdown-menu" data-dropdown-menu>
                                <a href="<?= e(url('certificate-types')) ?>">Create Template</a>
                                <a href="<?= e(url('participants')) ?>">Add Recipients</a>
                                <a href="<?= e(url('generate')) ?>">Open Send Flow</a>
                                <a href="<?= e(url('verify')) ?>" target="_blank">Verify Certificate</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </header>
        <?php endif; ?>

        <section class="content-area">
            <?php if ($flashes !== []): ?>
                <div class="toast-stack" aria-live="polite" aria-atomic="true">
                    <?php foreach ($flashes as $message): ?>
                        <div class="flash flash-<?= e($message['type']) ?>" data-toast>
                            <span><?= e($message['message']) ?></span>
                            <button type="button" class="flash-close" data-toast-close aria-label="Dismiss">×</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
<?php else: ?>
<div class="guest-shell">
    <?php foreach ($flashes as $message): ?>
        <div class="flash flash-<?= e($message['type']) ?>"><?= e($message['message']) ?></div>
    <?php endforeach; ?>
<?php endif; ?>
