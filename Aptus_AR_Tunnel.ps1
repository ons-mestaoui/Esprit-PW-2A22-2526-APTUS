$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$logPinggy = Join-Path $ScriptDir "pinggy_log.txt"
$logLT = Join-Path $ScriptDir "localtunnel_log.txt"
$urlFile = Join-Path $ScriptDir "pinggy_tunnel_url.txt"

# Force UTF8 for console output
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

function Write-Box {
    param($text, $color="Cyan")
    $len = $text.Length + 10
    $line = "=" * $len
    Write-Host "`n$line" -ForegroundColor $color
    Write-Host "     $text     " -ForegroundColor $color
    Write-Host "$line" -ForegroundColor $color
}

# Force cleaning previous instances
Write-Host "Reinitialisation des connexions..." -ForegroundColor Gray
Get-Process -Name ssh -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Get-Process -Name node -ErrorAction SilentlyContinue | Where-Object { $_.CommandLine -match "localtunnel" } | Stop-Process -Force -ErrorAction SilentlyContinue
Start-Sleep -Milliseconds 500

# Clear old logs
foreach ($f in @($logPinggy, $logLT, $urlFile)) {
    if (Test-Path $f) { try { Remove-Item $f -Force -ErrorAction SilentlyContinue } catch {} }
}

Write-Box "Serveur Securise Aptus" "Cyan"

$foundUrl = $false
$url = ""

# --- METHOD 1: PINGGY (SSH) ---
Write-Host "1/2 - Tentative via Pinggy (SSH)..." -ForegroundColor Gray
$sshCmd = "ssh -o StrictHostKeyChecking=no -o ServerAliveInterval=30 -o ConnectTimeout=10 -T -p 443 -R0:localhost:80 a.pinggy.io"
$fullSsh = "$sshCmd > `"$logPinggy`" 2>&1"
$procSsh = Start-Process -FilePath "cmd.exe" -ArgumentList "/c $fullSsh" -PassThru -WindowStyle Hidden

$stopwatch = [System.Diagnostics.Stopwatch]::StartNew()
while ($stopwatch.Elapsed.TotalSeconds -lt 20) {
    if (Test-Path $logPinggy) {
        $content = Get-Content $logPinggy -Raw -ErrorAction SilentlyContinue
        if ($content -match "(https://[a-zA-Z0-9\-.]+\.pinggy(?:-free)?\.link)") {
            $url = $matches[1]
            $foundUrl = $true
            break
        }
    }
    if ($procSsh.HasExited) { break }
    Start-Sleep -Milliseconds 500
}

# --- METHOD 2: LOCALTUNNEL (NODE.JS) ---
if (-not $foundUrl) {
    Write-Host "Pinggy a echoue ou est trop lent. Repli sur la Methode 2..." -ForegroundColor Yellow
    if (-not $procSsh.HasExited) { Stop-Process -Id $procSsh.Id -Force -ErrorAction SilentlyContinue }
    
    Write-Host "2/2 - Tentative via Localtunnel (Node.js)..." -ForegroundColor Gray
    $ltCmd = "npx.cmd -y localtunnel --port 80"
    $fullLt = "$ltCmd > `"$logLT`" 2>&1"
    $procLt = Start-Process -FilePath "cmd.exe" -ArgumentList "/c $fullLt" -PassThru -WindowStyle Hidden

    $stopwatch.Restart()
    while ($stopwatch.Elapsed.TotalSeconds -lt 25) {
        if (Test-Path $logLT) {
            $content = Get-Content $logLT -Raw -ErrorAction SilentlyContinue
            if ($content -match "(https://[a-zA-Z0-9\-.]+\.loca\.lt)") {
                $url = $matches[1]
                $foundUrl = $true
                $currentProc = $procLt
                break
            }
        }
        if ($procLt.HasExited) { break }
        Start-Sleep -Milliseconds 500
    }
} else {
    $currentProc = $procSsh
}

# --- RESULT ---
if ($foundUrl) {
    $url | Out-File -FilePath $urlFile -Encoding utf8
    Write-Host "`n>>> Tunnel Securise etabli avec succes ! <<<" -ForegroundColor Green
    Write-Host "URL: $url" -ForegroundColor Yellow
    Write-Host "`nCe lien HTTPS est certifie et fonctionnera sur mobile."
    Write-Host "Le QR Code se mettra a jour automatiquement."
    Write-Host "/!\ GARDEZ CETTE FENETRE OUVERTE /!\" -ForegroundColor Red
    
    while ($currentProc -and -not $currentProc.HasExited) { Start-Sleep -Seconds 1 }
} else {
    Write-Host "`n[ERREUR] Impossible d'etablir le tunnel." -ForegroundColor Red
    Write-Host "Les deux methodes ont echoue." -ForegroundColor Yellow
    
    Write-Host "`n--- Diagnostic Pinggy ---" -ForegroundColor Gray
    if (Test-Path $logPinggy) { Get-Content $logPinggy } else { Write-Host "Aucun log." }
    
    Write-Host "`n--- Diagnostic Localtunnel ---" -ForegroundColor Gray
    if (Test-Path $logLT) { Get-Content $logLT } else { Write-Host "Aucun log." }

    Write-Host "`nSolutions possibles :" -ForegroundColor White
    Write-Host "1. Verifiez que XAMPP est bien lance sur le port 80."
    Write-Host "2. Essayez de lancer 'npm install -g localtunnel' puis réessayez."
    Write-Host "3. Verifiez votre pare-feu Windows."
    Start-Sleep -Seconds 60
}
