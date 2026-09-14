@echo off
setlocal EnableExtensions EnableDelayedExpansion

:: Nuclear cleanup of all malformed vhost blocks, then rebuild clean
:: Run as Administrator

title HTTPS Vhost Complete Rebuild

echo ================================================================
echo         Rebuilding VirtualHost Configuration (Complete Clean)
echo ================================================================
echo.

net session >nul 2>&1
if not "%errorlevel%"=="0" (
    echo ERROR: Please run as Administrator.
    pause <con
    exit /b 1
)

set "XAMPP_PATH=D:\xampp"
set "VHOSTS_CONF=%XAMPP_PATH%\apache\conf\extra\httpd-vhosts.conf"
set "DOMAIN=inv.snapspro.local"
set "DOCROOT_APACHE=D:/xampp/htdocs/snaps_inv"
set "CERT_FILE_APACHE=D:/xampp/apache/conf/ssl.crt/inv.snapspro.local.crt"
set "KEY_FILE_APACHE=D:/xampp/apache/conf/ssl.key/inv.snapspro.local.key"
set "WORK_TMP=%TEMP%"

echo [1/4] Backing up vhosts config...
copy "%VHOSTS_CONF%" "%VHOSTS_CONF%.backup.bak" >nul 2>&1
echo Backup created: %VHOSTS_CONF%.backup.bak
echo.

echo [2/4] Removing ALL AUTO_HTTPS blocks (all tags)...
set "TMP_NUKE_PS=%WORK_TMP%\nuke_all_autohttps_%RANDOM%.ps1"
>"%TMP_NUKE_PS%" echo $p = '%VHOSTS_CONF%'
>>"%TMP_NUKE_PS%" echo $content = Get-Content -Raw -Path $p
>>"%TMP_NUKE_PS%" echo $content = [regex]::Replace($content, '(?s)# BEGIN AUTO_HTTPS.*?# END AUTO_HTTPS.*?\r?\n?', '')
>>"%TMP_NUKE_PS%" echo $content = [regex]::Replace($content, '\s+^$', '')
>>"%TMP_NUKE_PS%" echo $content = $content.Trim()
>>"%TMP_NUKE_PS%" echo Set-Content -Path $p -Value $content -Encoding ASCII

powershell -NoProfile -ExecutionPolicy Bypass -File "%TMP_NUKE_PS%"
if exist "%TMP_NUKE_PS%" del "%TMP_NUKE_PS%" >nul 2>&1
echo All AUTO_HTTPS blocks removed.
echo.

echo [3/4] Writing clean VirtualHost block...
set "TMP_WRITE_PS=%WORK_TMP%\write_clean_vhost_%RANDOM%.ps1"
>"%TMP_WRITE_PS%" echo $ErrorActionPreference = 'Stop'
>>"%TMP_WRITE_PS%" echo try {
>>"%TMP_WRITE_PS%" echo ^    $p = '%VHOSTS_CONF%'
>>"%TMP_WRITE_PS%" echo ^    $vhost = @"
>>"%TMP_WRITE_PS%" echo.
>>"%TMP_WRITE_PS%" echo # BEGIN AUTO_HTTPS_inv_snapspro_local
>>"%TMP_WRITE_PS%" echo ^<VirtualHost *:80^>
>>"%TMP_WRITE_PS%" echo     ServerName inv.snapspro.local
>>"%TMP_WRITE_PS%" echo     DocumentRoot "D:/xampp/htdocs/snaps_inv"
>>"%TMP_WRITE_PS%" echo     ^<Directory "D:/xampp/htdocs/snaps_inv"^>
>>"%TMP_WRITE_PS%" echo         AllowOverride All
>>"%TMP_WRITE_PS%" echo         Require all granted
>>"%TMP_WRITE_PS%" echo     ^</Directory^>
>>"%TMP_WRITE_PS%" echo ^</VirtualHost^>
>>"%TMP_WRITE_PS%" echo.
>>"%TMP_WRITE_PS%" echo ^<VirtualHost *:443^>
>>"%TMP_WRITE_PS%" echo     ServerName inv.snapspro.local
>>"%TMP_WRITE_PS%" echo     DocumentRoot "D:/xampp/htdocs/snaps_inv"
>>"%TMP_WRITE_PS%" echo     SSLEngine on
>>"%TMP_WRITE_PS%" echo     SSLCertificateFile "D:/xampp/apache/conf/ssl.crt/inv.snapspro.local.crt"
>>"%TMP_WRITE_PS%" echo     SSLCertificateKeyFile "D:/xampp/apache/conf/ssl.key/inv.snapspro.local.key"
>>"%TMP_WRITE_PS%" echo     ^<Directory "D:/xampp/htdocs/snaps_inv"^>
>>"%TMP_WRITE_PS%" echo         AllowOverride All
>>"%TMP_WRITE_PS%" echo         Require all granted
>>"%TMP_WRITE_PS%" echo     ^</Directory^>
>>"%TMP_WRITE_PS%" echo ^</VirtualHost^>
>>"%TMP_WRITE_PS%" echo # END AUTO_HTTPS_inv_snapspro_local
>>"%TMP_WRITE_PS%" echo "@
>>"%TMP_WRITE_PS%" echo ^    Add-Content -Path $p -Value $vhost -Encoding ASCII
>>"%TMP_WRITE_PS%" echo } catch {
>>"%TMP_WRITE_PS%" echo ^    Write-Host "ERROR: $_"
>>"%TMP_WRITE_PS%" echo ^    exit 1
>>"%TMP_WRITE_PS%" echo }

powershell -NoProfile -ExecutionPolicy Bypass -File "%TMP_WRITE_PS%"
if not "%errorlevel%"=="0" (
    echo [ERROR] Failed to write VirtualHost block!
    if exist "%TMP_WRITE_PS%" del "%TMP_WRITE_PS%" >nul 2>&1
    pause <con
    exit /b 1
)
if exist "%TMP_WRITE_PS%" del "%TMP_WRITE_PS%" >nul 2>&1
echo Clean VirtualHost block added.
echo.

echo [4/4] Validating Apache config...
"%XAMPP_PATH%\apache\bin\httpd.exe" -t -f "%XAMPP_PATH%\apache\conf\httpd.conf" >"%TEMP%\apache_test.log" 2>&1
if not "%errorlevel%"=="0" (
    echo [ERROR] Apache config still has errors:
    type "%TEMP%\apache_test.log"
    echo.
    echo Restoring backup and exiting...
    copy "%VHOSTS_CONF%.backup.bak" "%VHOSTS_CONF%" >nul 2>&1
    pause <con
    exit /b 1
)
echo Apache config is valid!
echo.

echo ================================================================
echo Rebuild complete! Configuration is clean.
echo.
echo Next steps:
echo 1) Restart Apache via XAMPP Control Panel or command:
echo    taskkill /IM httpd.exe /F ^&^& timeout /t 2 ^&^& start "" "%XAMPP_PATH%\apache\bin\httpd.exe"
echo.
echo 2) Test HTTPS:
echo    https://inv.snapspro.local
echo.
echo 3) You should see either:
echo    - Green lock (if cert is trusted)
echo    - Certificate warning (if cert not yet trusted)
echo    - NOT ERR_SSL_PROTOCOL_ERROR
echo ================================================================
echo.
pause <con
exit /b 0
