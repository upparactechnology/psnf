<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Verification | <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=2.2.1">
</head>
<body>
<div class="guest-shell">
    <div class="auth-card auth-wide">
        <span class="auth-brand">Public Verification</span>
        <h1>Certificate Verification</h1>
        <p>Enter the unique verification code printed on the certificate.</p>

        <form method="get" class="form-grid two">
            <input type="hidden" name="page" value="verify">
            <label>
                Verification Code
                <input type="text" name="code" value="<?= e($code) ?>" placeholder="e.g. a94f3210ef32ab12" required>
            </label>
            <div class="align-end-actions">
                <button type="submit">Verify</button>
            </div>
        </form>

        <?php if ($code !== ''): ?>
            <?php if ($record !== null): ?>
                <div class="flash flash-success top-gap-sm">Certificate is valid.</div>
                <div class="auth-two-col top-gap-sm">
                    <div class="panel">
                        <h3>Certificate Details</h3>
                        <p><strong>Participant:</strong> <?= e($record['name']) ?></p>
                        <p><strong>Institute:</strong> <?= e((string) $record['institute']) ?></p>
                        <p><strong>Title:</strong> <?= e((string) $record['title']) ?></p>
                        <p><strong>Category:</strong> <?= e($record['category_name']) ?></p>
                        <p><strong>Conference:</strong> <?= e($record['conference_name']) ?> (<?= e((string) $record['year']) ?>)</p>
                        <p><strong>Issued Date:</strong> <?= e((string) $record['issued_date']) ?></p>
                        <p><strong>Generated At:</strong> <?= e((string) $record['generated_at']) ?></p>
                        <p><strong>Verify URL:</strong> <a href="<?= e($verifyUrl) ?>"><?= e($verifyUrl) ?></a></p>
                    </div>
                    <div class="auth-aside-panel">
                        <h3>QR Code</h3>
                        <?php if ($qrImageUrl !== ''): ?>
                            <img src="<?= e($qrImageUrl) ?>" alt="Verification QR" class="verify-qr">
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="flash flash-danger top-gap-sm">Invalid verification code. Certificate not found.</div>
            <?php endif; ?>
        <?php endif; ?>

        <p class="small top-gap-sm">
            Admin login: <a href="index.php?page=login">Open Admin Dashboard</a>
        </p>
    </div>
</div>
</body>
</html>
