# --- KONFIGURASI --- #
$botToken = "8439242731:AAFu7y7a1IAkfRX5ZUnYj2c3TRyhE4OTPtE"                # Ganti dengan token Telegram bot kamu
$webhookPath = "/telegram/webhook"           # Ganti sesuai route webhook di Laravel
$laravelPort = 8000 
# ----------------- #

# Jalankan Laravel di background
$laravel = Start-Process "php" -ArgumentList "artisan serve --host=127.0.0.1 --port=8000" -PassThru
Start-Sleep -Seconds 2   # tunggu Laravel siap

# Hentikan ngrok lama jika masih jalan
Get-Process ngrok -ErrorAction SilentlyContinue | Stop-Process -Force

# Jalankan ngrok
Write-Host "Menjalankan ngrok..."
$ngrokProcess = Start-Process "ngrok" -ArgumentList "http 8000 --log=stdout" -NoNewWindow -RedirectStandardOutput "ngrok.log" -PassThru
Start-Sleep -Seconds 3   # tunggu ngrok buat log

# Ambil URL ngrok dari log
$ngrokURL = Select-String -Path "ngrok.log" -Pattern "https://.*\.ngrok-free\.app" | Select-Object -First 1 | ForEach-Object {$_.Matches[0].Value}

if (-not $ngrokURL) {
    Write-Host "Gagal mendapatkan URL ngrok!"
    $laravel.Kill()
    $ngrokProcess.Kill()
    exit
}

Write-Host "Ngrok URL: $ngrokURL"

# Update .env APP_URL
(Get-Content .env) | ForEach-Object {
    $_ -replace '^APP_URL=.*', "APP_URL=$ngrokURL"
} | Set-Content .env

# Clear cache Laravel
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# --- UPDATE WEBHOOK TELEGRAM --- #
$webhookURL = "$ngrokURL$webhookPath"
Write-Host "Mengupdate webhook Telegram ke: $webhookURL"
Invoke-WebRequest -Uri "https://api.telegram.org/bot$botToken/setWebhook?url=$webhookURL" -Method Post

Write-Host "Laravel dan ngrok siap. APP_URL sudah diperbarui dan webhook Telegram sudah di-update."
Write-Host "URL publik: $ngrokURL"

# Tunggu user tekan CTRL+C untuk stop
Write-Host "Tekan CTRL+C untuk menghentikan Laravel dan ngrok..."
try {
    while ($true) { Start-Sleep -Seconds 1 }
} finally {
    $laravel.Kill()
    $ngrokProcess.Kill()
    Write-Host "Laravel dan ngrok dihentikan."
}
