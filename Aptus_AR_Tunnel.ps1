$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$logFile = Join-Path $ScriptDir "tunnel_log.txt"
$urlFile = Join-Path $ScriptDir "tunnel_url.txt"

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
if (Test-Path $logFile) { try { Remove-Item $logFile -Force -ErrorAction SilentlyContinue } catch {} }
if (Test-Path $urlFile) { try { Remove-Item $urlFile -Force -ErrorAction SilentlyContinue } catch {} }

Write-Box "Serveur Securise Aptus" "Cyan"
Write-Host "Connexion au tunnel (Localtunnel - HTTPS certifie)..."

Write-Host "Démarrage du tunnel sécurisé..." -ForegroundColor Gray

# Utilisation de cmd /c pour rediriger proprement stdout et stderr (2>&1) 
# Cela évite l'erreur PowerShell sur les redirections identiques.
$fullCmd = "npx localtunnel --port 80 > `"$logFile`" 2>&1"
$proc = Start-Process -FilePath "cmd.exe" -ArgumentList "/c $fullCmd" -PassThru -WindowStyle Hidden

$foundUrl = $false
$timeout = 40
$stopwatch = [System.Diagnostics.Stopwatch]::StartNew()

while ($stopwatch.Elapsed.TotalSeconds -lt $timeout) {
    if (Test-Path $logFile) {
        try {
            $content = Get-Content $logFile -Raw -ErrorAction SilentlyContinue
            if ($content -match "(https://[a-zA-Z0-9\-]+\.loca\.lt)") {
                $url = $matches[1]
                $url | Out-File -FilePath $urlFile -Encoding utf8
                Write-Host "`n>>> Tunnel Securise etabli avec succes ! <<<" -ForegroundColor Green
                Write-Host "URL: $url" -ForegroundColor Yellow
                Write-Host "`nCe lien HTTPS est certifie et fonctionnera sur mobile."
                Write-Host "Le QR Code se mettra a jour automatiquement."
                Write-Host "/!\ GARDEZ CETTE FENETRE OUVERTE /!\" -ForegroundColor Red
                $foundUrl = $true
                break
            }
        } catch {}
    }
    
    if ($proc -and $proc.HasExited) {
        break
    }
    
    Start-Sleep -Milliseconds 500
}

if (-not $foundUrl) {
    Write-Host "`n[ERREUR] Impossible d'etablir le tunnel." -ForegroundColor Red
    if (Test-Path $logFile) {
        $lastLog = Get-Content $logFile -Raw -ErrorAction SilentlyContinue
        if ($lastLog) {
            Write-Host "`nDétails :" -ForegroundColor Gray
            Write-Host $lastLog
        } else {
            Write-Host "Vérifiez que Node.js est bien installé sur cette machine." -ForegroundColor Yellow
        }
    }
    Start-Sleep -Seconds 20
} else {
    while ($proc -and -not $proc.HasExited) { Start-Sleep -Seconds 1 }
}
