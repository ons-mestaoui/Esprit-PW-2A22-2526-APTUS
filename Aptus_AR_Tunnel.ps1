$logFile = "C:\xampp\htdocs\aptus_first_official_version\tunnel_log.txt"
$urlFile = "C:\xampp\htdocs\aptus_first_official_version\tunnel_url.txt"
if (Test-Path $logFile) { Remove-Item $logFile }
if (Test-Path $urlFile) { Remove-Item $urlFile }

Write-Host "=============================================" -ForegroundColor Cyan
Write-Host "     Demarrage du Serveur Securise Aptus     " -ForegroundColor Cyan
Write-Host "=============================================" -ForegroundColor Cyan
Write-Host "Connexion au tunnel (patientez quelques secondes)..."

$process = Start-Process -FilePath "cmd.exe" -ArgumentList "/c ssh -p 443 -R0:localhost:80 -o StrictHostKeyChecking=no a.pinggy.io > `"$logFile`" 2>&1" -PassThru -WindowStyle Hidden

$foundUrl = $false
$timeout = 15
$stopwatch = [System.Diagnostics.Stopwatch]::StartNew()

while ($stopwatch.Elapsed.TotalSeconds -lt $timeout) {
    if (Test-Path $logFile) {
        $content = Get-Content $logFile -Raw
        if ($content -match "(https://[a-zA-Z0-9\-]+\.run\.pinggy-free\.link)") {
            $url = $matches[1]
            $url | Out-File -FilePath $urlFile -Encoding utf8
            Write-Host ""
            Write-Host ">>> Tunnel etabli avec succes ! <<<" -ForegroundColor Green
            Write-Host "URL: $url" -ForegroundColor Yellow
            Write-Host ""
            Write-Host "Le QR Code sur le site se mettra a jour automatiquement."
            Write-Host ""
            Write-Host "/!\ LAISSEZ CETTE FENETRE OUVERTE /!\" -ForegroundColor Red
            Write-Host "Pour fermer le tunnel, fermez simplement cette fenetre."
            $foundUrl = $true
            break
        }
    }
    Start-Sleep -Milliseconds 500
}

if (-not $foundUrl) {
    Write-Host "Echec de connexion au tunnel." -ForegroundColor Red
    Stop-Process -Id $process.Id -Force -ErrorAction SilentlyContinue
    Start-Sleep -Seconds 5
} else {
    while ($process.HasExited -eq $false) {
        Start-Sleep -Seconds 1
    }
}
