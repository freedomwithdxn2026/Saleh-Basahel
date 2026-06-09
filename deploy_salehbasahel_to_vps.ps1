$ErrorActionPreference = 'Stop'

$Root = Split-Path -Parent $MyInvocation.MyCommand.Path
$Stage = Join-Path $Root '.deploy-salehbasahel-stage'
$Key = Join-Path $Root 'saleh_vps_deploy_key'
$Server = 'root@161.97.114.28'
$RemoteStage = '/root/salehbasahel-site'
$RemoteUpload = '/root/salehbasahel-site-upload'

if (-not (Test-Path -LiteralPath $Stage)) {
    throw "Deploy stage not found: $Stage"
}

if (-not (Test-Path -LiteralPath $Key)) {
    throw "Deploy key not found: $Key"
}

Write-Host "Preparing remote deployment directory..." -ForegroundColor Cyan
ssh -i "$Key" -o BatchMode=yes $Server "rm -rf '$RemoteUpload'"
if ($LASTEXITCODE -ne 0) {
    throw 'Could not prepare the remote deployment directory.'
}

Write-Host "Uploading deployment package..." -ForegroundColor Cyan
scp -i "$Key" -o BatchMode=yes -r "$Stage" "${Server}:$RemoteUpload"
if ($LASTEXITCODE -ne 0) {
    throw 'Could not upload the deployment package.'
}

Write-Host "Running VPS deployment..." -ForegroundColor Cyan
ssh -i "$Key" -o BatchMode=yes $Server "rm -rf '$RemoteStage' && mv '$RemoteUpload' '$RemoteStage' && test -f '$RemoteStage/Admin/AuthController.php' && chmod +x '$RemoteStage/deploy_salehbasahel.sh' && '$RemoteStage/deploy_salehbasahel.sh'"
if ($LASTEXITCODE -ne 0) {
    throw 'The VPS deployment command failed.'
}

Write-Host "Deployment command finished. Checking live site..." -ForegroundColor Cyan
try {
    $response = Invoke-WebRequest -Uri 'https://salehbasahel.com/en' -UseBasicParsing -TimeoutSec 20
    Write-Host "Live site responded with HTTP $($response.StatusCode)." -ForegroundColor Green
} catch {
    Write-Warning "Deployment finished, but the live HTTP check failed: $($_.Exception.Message)"
}
