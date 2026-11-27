# Auto-Installer for Flutter on Windows

# Ensure target directory C:\src exists
if (!(Test-Path "C:\src")) {
    New-Item -ItemType Directory -Force -Path "C:\src"
    Write-Host "Created directory C:\src" -ForegroundColor Green
}

$zipPath = "C:\src\flutter.zip"
$destPath = "C:\src"
$url = "https://storage.googleapis.com/flutter_infra_release/releases/stable/windows/flutter_windows_3.22.2-stable.zip"

Write-Host "=============================================" -ForegroundColor Green
Write-Host "          PSNF Flutter Auto-Installer        " -ForegroundColor Green
Write-Host "=============================================" -ForegroundColor Green
Write-Host ""

# Step 1: Download zip
Write-Host "Step 1: Downloading Flutter SDK stable zip..." -ForegroundColor Cyan
if (Test-Path $zipPath) {
    Remove-Item $zipPath -Force
}
try {
    Start-BitsTransfer -Source $url -Destination $zipPath -Description "Downloading Flutter SDK"
    Write-Host "✓ Download complete!" -ForegroundColor Green
} catch {
    Write-Host "BitsTransfer failed, trying fallback WebClient download..." -ForegroundColor Yellow
    $webClient = New-Object System.Net.WebClient
    $webClient.DownloadFile($url, $zipPath)
    Write-Host "✓ Download complete!" -ForegroundColor Green
}

# Step 2: Extract Zip
Write-Host ""
Write-Host "Step 2: Extracting Flutter to C:\src\flutter (this takes 1-2 minutes)..." -ForegroundColor Cyan
if (Test-Path "C:\src\flutter") {
    Write-Host "Existing Flutter directory found. Cleaning it up first..." -ForegroundColor Yellow
    Remove-Item "C:\src\flutter" -Recurse -Force
}
Expand-Archive -Path $zipPath -DestinationPath $destPath -Force
Write-Host "✓ Extraction complete!" -ForegroundColor Green

# Step 3: Remove downloaded zip
Write-Host ""
Write-Host "Step 3: Cleaning up downloaded zip file..." -ForegroundColor Cyan
Remove-Item $zipPath -Force
Write-Host "✓ Cleanup complete!" -ForegroundColor Green

# Step 4: Add path to User environment variable
Write-Host ""
Write-Host "Step 4: Setting up System environment variables..." -ForegroundColor Cyan
$binPath = "C:\src\flutter\bin"
$pathVar = [Environment]::GetEnvironmentVariable("Path", "User")

if ($pathVar -notlike "*$binPath*") {
    $newPath = $pathVar + ";" + $binPath
    # Clean up multiple semicolons if any
    $newPath = $newPath -replace ';+', ';'
    [Environment]::SetEnvironmentVariable("Path", $newPath, "User")
    Write-Host "✓ Flutter bin added to User Path successfully!" -ForegroundColor Green
} else {
    Write-Host "✓ Flutter bin is already configured in your User Path." -ForegroundColor Green
}

Write-Host ""
Write-Host "=============================================" -ForegroundColor Green
Write-Host "       Installation completed successfully!  " -ForegroundColor Green
Write-Host "=============================================" -ForegroundColor Green
Write-Host "IMPORTANT: You must close your current terminal window (or VS Code) and open a new one for the settings to take effect." -ForegroundColor Yellow
Write-Host "In the new terminal, run 'flutter doctor' to confirm." -ForegroundColor Yellow
Write-Host ""
