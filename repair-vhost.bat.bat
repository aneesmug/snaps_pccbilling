@echo off
setlocal EnableExtensions EnableDelayedExpansion

:: Repair malformed vhost block
:: Run as Administrator

title HTTPS Vhost Repair Tool

echo ================================================================
echo              Repairing Malformed VirtualHost Block
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
set "DOCROOT=D:\xampp\htdocs\snaps_inv"
set "DOCROOT_APACHE=D:/xampp/htdocs/snaps_inv"
set "CERT_FILE_APACHE=D:/xampp/apache/conf/ssl.crt/inv.snapspro.local.crt"
set "KEY_FILE_APACHE=D:/xampp/apache/conf/ssl.key/inv.snapspro.local.key"
set "WORK_TMP=%TEMP%"

echo.
echo [1/3] Removing malformed VirtualHost block...
set "TMP_REMOVE_PS=%WORK_TMP%\remove_malformed_vhost_%RANDOM%.ps1"
>"%TMP_REMOVE_PS%" echo $p = '%VHOSTS_CONF%'
>>"%TMP_REMOVE_PS%" echo $content = Get-Content -Raw -Path $p
>>"%TMP_REMOVE_PS%" echo $content = [regex]::Replace($content, '(?s)# BEGIN AUTO_HTTPS_.*?# END AUTO_HTTPS_.*?\s*', '')
>>"%TMP_REMOVE_PS%" echo Set-Content -Path $p -Value $content -Encoding ASCII

powershell -NoProfile -ExecutionPolicy Bypass -File "%TMP_REMOVE_PS%"
if exist "%TMP_REMOVE_PS%" del "%TMP_REMOVE_PS%" >nul 2>&1
echo Done.

echo.
echo [2/3] Writing properly formatted VirtualHost block...
set "TMP_VHOST_PS=%WORK_TMP%\write_fixed_vhost_%RANDOM%.ps1"
>"%TMP_VHOST_PS%" echo $ErrorActionPreference = 'Stop'
>>"%TMP_VHOST_PS%" echo try {
>>"%TMP_VHOST_PS%" echo ^    $p = '%VHOSTS_CONF%'
>>"%TMP_VHOST_PS%" echo ^    $domain = '%DOMAIN%'
>>"%TMP_VHOST_PS%" echo ^    $doc = '%DOCROOT_APACHE%'
>>"%TMP_VHOST_PS%" echo ^    $crt = '%CERT_FILE_APACHE%'
>>"%TMP_VHOST_PS%" echo ^    $key = '%KEY_FILE_APACHE%'
>>"%TMP_VHOST_PS%" echo ^    $tag = $domain -replace '[^A-Za-z0-9]','_'
>>"%TMP_VHOST_PS%" echo ^    $blockLines = @(
>>"%TMP_VHOST_PS%" echo ^        '',
>>"%TMP_VHOST_PS%" echo ^        '# BEGIN AUTO_HTTPS_' + $tag,
>>"%TMP_VHOST_PS%" echo ^        '^<VirtualHost *:80^>',
>>"%TMP_VHOST_PS%" echo ^        '    ServerName ' + $domain,
>>"%TMP_VHOST_PS%" echo ^        '    DocumentRoot "' + $doc + '"',
>>"%TMP_VHOST_PS%" echo ^        '    ^<Directory "' + $doc + '"^>',
>>"%TMP_VHOST_PS%" echo ^        '        AllowOverride All',
>>"%TMP_VHOST_PS%" echo ^        '        Require all granted',
>>"%TMP_VHOST_PS%" echo ^        '    ^</Directory^>',
>>"%TMP_VHOST_PS%" echo ^        '^</VirtualHost^>',
>>"%TMP_VHOST_PS%" echo ^        '',
>>"%TMP_VHOST_PS%" echo ^        '^<VirtualHost *:443^>',
>>"%TMP_VHOST_PS%" echo ^        '    ServerName ' + $domain,
>>"%TMP_VHOST_PS%" echo ^        '    DocumentRoot "' + $doc + '"',
>>"%TMP_VHOST_PS%" echo ^        '    SSLEngine on',
>>"%TMP_VHOST_PS%" echo ^        '    SSLCertificateFile "' + $crt + '"',
>>"%TMP_VHOST_PS%" echo ^        '    SSLCertificateKeyFile "' + $key + '"',
>>"%TMP_VHOST_PS%" echo ^        '    ^<Directory "' + $doc + '"^>',
>>"%TMP_VHOST_PS%" echo ^        '        AllowOverride All',
>>"%TMP_VHOST_PS%" echo ^        '        Require all granted',
>>"%TMP_VHOST_PS%" echo ^        '    ^</Directory^>',
>>"%TMP_VHOST_PS%" echo ^        '^</VirtualHost^>',
>>"%TMP_VHOST_PS%" echo ^        '# END AUTO_HTTPS_' + $tag,
>>"%TMP_VHOST_PS%" echo ^        ''
>>"%TMP_VHOST_PS%" echo ^    )
>>"%TMP_VHOST_PS%" echo ^    $block = [string]::Join("`r`n", $blockLines)
>>"%TMP_VHOST_PS%" echo ^    Add-Content -Path $p -Value $block -Encoding ASCII
>>"%TMP_VHOST_PS%" echo } catch {
>>"%TMP_VHOST_PS%" echo ^    exit 1
>>"%TMP_VHOST_PS%" echo }

powershell -NoProfile -ExecutionPolicy Bypass -File "%TMP_VHOST_PS%"
if not "%errorlevel%"=="0" (
    echo [ERROR] Failed to write VirtualHost block.
    if exist "%TMP_VHOST_PS%" del "%TMP_VHOST_PS%" >nul 2>&1
    pause <con
    exit /b 1
)
if exist "%TMP_VHOST_PS%" del "%TMP_VHOST_PS%" >nul 2>&1
echo Done.

echo.
echo [3/3] Validating Apache config...
"%XAMPP_PATH%\apache\bin\httpd.exe" -t -f "%XAMPP_PATH%\apache\conf\httpd.conf"
if not "%errorlevel%"=="0" (
    echo [ERROR] Apache config validation failed!
    pause <con
    exit /b 1
)
echo Apache config is valid!

echo.
echo ================================================================
echo Repair complete. Now restart Apache:
echo.
echo Option 1: Close and reopen XAMPP Control Panel
echo Option 2: Run this command:
echo   taskkill /IM httpd.exe /F ^&^& timeout /t 2 ^&^& start "" "%XAMPP_PATH%\apache\bin\httpd.exe"
echo.
echo Then test: https://inv.snapspro.local
echo ================================================================
echo.
pause <con
exit /b 0
