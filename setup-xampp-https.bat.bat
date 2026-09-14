@echo off
setlocal EnableExtensions EnableDelayedExpansion

:: Interactive HTTPS setup for XAMPP Apache on Windows
:: Run this file as Administrator.

title XAMPP HTTPS Setup Wizard


call :main
set "RC=%ERRORLEVEL%"
if not "%RC%"=="0" (
    echo.
    echo [ERROR] Setup failed with code %RC%.
    rem Detect if input is redirected (not from console)
    (2>&1 <nul >nul findstr "" <&0) 2>nul 1>nul
    if errorlevel 1 (
        rem Input is redirected, open a new cmd window to show error and pause
        start "Error" cmd /k "echo [ERROR] Setup failed with code %RC%. & echo. & echo Press any key to close... & pause"
    ) else (
        echo Press any key to close...
        pause >nul <con
    )
)
exit /b %RC%

:main

net session >nul 2>&1
if not "%errorlevel%"=="0" (
    echo.
    echo [ERROR] Please run this script as Administrator.
    echo Right click the .bat file and choose "Run as administrator".
    echo.
    pause <con
    exit /b 1
)

echo ================================================================
echo                 XAMPP HTTPS Setup Wizard
echo ================================================================
echo.
echo This script will:
echo 1) Create SSL certificate/key
echo 2) Install certificate into Windows Trusted Root store
echo 3) Add domain to Windows hosts file
echo 4) Add HTTP+HTTPS VirtualHost to Apache vhosts config
echo 5) Ensure required SSL directives are enabled in httpd.conf
echo 6) Ensure Listen 443 is active in httpd-ssl.conf
echo 7) Validate Apache config
echo 8) Restart Apache to apply all changes
echo.

set "DEFAULT_XAMPP=D:\xampp"
set /p "XAMPP_PATH=Enter XAMPP path [D:\xampp]: "
if "%XAMPP_PATH%"=="" set "XAMPP_PATH=%DEFAULT_XAMPP%"

set "HTTPD_CONF=%XAMPP_PATH%\apache\conf\httpd.conf"
set "VHOSTS_CONF=%XAMPP_PATH%\apache\conf\extra\httpd-vhosts.conf"
set "HOSTS_FILE=C:\Windows\System32\drivers\etc\hosts"
set "OPENSSL_EXE=%XAMPP_PATH%\apache\bin\openssl.exe"
set "HTTPD_EXE=%XAMPP_PATH%\apache\bin\httpd.exe"
set "CRT_DIR=%XAMPP_PATH%\apache\conf\ssl.crt"
set "KEY_DIR=%XAMPP_PATH%\apache\conf\ssl.key"

set "WORK_TMP=%TEMP%"
if "%WORK_TMP%"=="" set "WORK_TMP=%SystemRoot%\Temp"
if not exist "%WORK_TMP%" set "WORK_TMP=%SystemRoot%\Temp"
if not exist "%WORK_TMP%" mkdir "%WORK_TMP%" >nul 2>&1

if not exist "%HTTPD_CONF%" (
    echo.
    echo [ERROR] httpd.conf not found: %HTTPD_CONF%
    (2>&1 <nul >nul findstr "" <&0) 2>nul 1>nul
    if errorlevel 1 (
        start "Error" cmd /k "echo [ERROR] httpd.conf not found: %HTTPD_CONF%. & echo. & echo Press any key to close... & pause"
    ) else (
        pause <con
    )
    exit /b 1
)
if not exist "%VHOSTS_CONF%" (
    echo.
    echo [ERROR] httpd-vhosts.conf not found: %VHOSTS_CONF%
    (2>&1 <nul >nul findstr "" <&0) 2>nul 1>nul
    if errorlevel 1 (
        start "Error" cmd /k "echo [ERROR] httpd-vhosts.conf not found: %VHOSTS_CONF%. & echo. & echo Press any key to close... & pause"
    ) else (
        pause <con
    )
    exit /b 1
)
if not exist "%OPENSSL_EXE%" (
    echo.
    echo [ERROR] openssl.exe not found: %OPENSSL_EXE%
    (2>&1 <nul >nul findstr "" <&0) 2>nul 1>nul
    if errorlevel 1 (
        start "Error" cmd /k "echo [ERROR] openssl.exe not found: %OPENSSL_EXE%. & echo. & echo Press any key to close... & pause"
    ) else (
        pause <con
    )
    exit /b 1
)
if not exist "%HTTPD_EXE%" (
    echo.
    echo [ERROR] httpd.exe not found: %HTTPD_EXE%
    (2>&1 <nul >nul findstr "" <&0) 2>nul 1>nul
    if errorlevel 1 (
        start "Error" cmd /k "echo [ERROR] httpd.exe not found: %HTTPD_EXE%. & echo. & echo Press any key to close... & pause"
    ) else (
        pause <con
    )
    exit /b 1
)

echo.
set /p "DOMAIN=Enter domain (example: inv.almutlak.local): "
if "%DOMAIN%"=="" (
    echo [ERROR] Domain is required.
    (2>&1 <nul >nul findstr "" <&0) 2>nul 1>nul
    if errorlevel 1 (
        start "Error" cmd /k "echo [ERROR] Domain is required. & echo. & echo Press any key to close... & pause"
    ) else (
        pause <con
    )
    exit /b 1
)

set "DEFAULT_DOCROOT=D:\xampp\htdocs\snaps_billing_finance"
set /p "DOCROOT=Enter document root [%DEFAULT_DOCROOT%]: "
if "%DOCROOT%"=="" set "DOCROOT=%DEFAULT_DOCROOT%"

if not exist "%DOCROOT%" (
    echo.
    echo [WARNING] DocumentRoot does not exist: %DOCROOT%
    choice /c YN /m "Continue anyway"
    if errorlevel 2 exit /b 1
)

echo.
echo Certificate subject values:
set /p "C=Country (2 letters) [SA]: "
if "%C%"=="" set "C=SA"
set /p "ST=State/Province [Riyadh]: "
if "%ST%"=="" set "ST=Riyadh"
set /p "L=Locality/City [Riyadh]: "
if "%L%"=="" set "L=Riyadh"
set /p "O=Organization [LocalDev]: "
if "%O%"=="" set "O=LocalDev"
set /p "OU=Org Unit [IT]: "
if "%OU%"=="" set "OU=IT"
set /p "DAYS=Certificate days [825]: "
if "%DAYS%"=="" set "DAYS=825"

set "CERT_FILE=%CRT_DIR%\%DOMAIN%.crt"
set "KEY_FILE=%KEY_DIR%\%DOMAIN%.key"
set "SUBJ=/C=%C%/ST=%ST%/L=%L%/O=%O%/OU=%OU%/CN=%DOMAIN%"
set "DOCROOT_APACHE=%DOCROOT:\=/%"
set "CERT_FILE_APACHE=%CERT_FILE:\=/%"
set "KEY_FILE_APACHE=%KEY_FILE:\=/%"

echo.
echo ---------------------- Summary ----------------------
echo XAMPP Path   : %XAMPP_PATH%
echo Domain       : %DOMAIN%
echo DocumentRoot : %DOCROOT%
echo Cert File    : %CERT_FILE%
echo Key File     : %KEY_FILE%
echo Subject      : %SUBJ%
echo -----------------------------------------------------
echo.
choice /c YN /m "Proceed with setup"
if errorlevel 2 exit /b 1

if not exist "%CRT_DIR%" mkdir "%CRT_DIR%"
if not exist "%KEY_DIR%" mkdir "%KEY_DIR%"

echo.
echo [1/7] Creating SSL certificate...
set "OPENSSL_CONF=%XAMPP_PATH%\apache\conf\openssl.cnf"
"%OPENSSL_EXE%" req -x509 -nodes -days %DAYS% -newkey rsa:2048 -keyout "%KEY_FILE%" -out "%CERT_FILE%" -subj "%SUBJ%"
if not "%errorlevel%"=="0" (
    echo [ERROR] Failed to create certificate.
    (2>&1 <nul >nul findstr "" <&0) 2>nul 1>nul
    if errorlevel 1 (
        start "Error" cmd /k "echo [ERROR] Failed to create certificate. & echo. & echo Press any key to close... & pause"
    ) else (
        pause <con
    )
    exit /b 1
)

echo.
echo [2/7] Installing certificate in Windows Trusted Root store...
set "TMP_CERT_INSTALL_PS=%WORK_TMP%\install_cert_%RANDOM%.ps1"
>"%TMP_CERT_INSTALL_PS%" echo $ErrorActionPreference = 'Stop'
>>"%TMP_CERT_INSTALL_PS%" echo $certPath = '%CERT_FILE%'
>>"%TMP_CERT_INSTALL_PS%" echo if (-not (Test-Path -Path $certPath)) { exit 2 }
>>"%TMP_CERT_INSTALL_PS%" echo $cert = New-Object System.Security.Cryptography.X509Certificates.X509Certificate2($certPath)
>>"%TMP_CERT_INSTALL_PS%" echo $thumb = $cert.Thumbprint.ToUpperInvariant()
>>"%TMP_CERT_INSTALL_PS%" echo function Add-ToRootStore([System.Security.Cryptography.X509Certificates.StoreLocation]$loc) {
>>"%TMP_CERT_INSTALL_PS%" echo ^    $store = New-Object System.Security.Cryptography.X509Certificates.X509Store('Root', $loc)
>>"%TMP_CERT_INSTALL_PS%" echo ^    $store.Open([System.Security.Cryptography.X509Certificates.OpenFlags]::ReadWrite)
>>"%TMP_CERT_INSTALL_PS%" echo ^    try {
>>"%TMP_CERT_INSTALL_PS%" echo ^        $exists = $false
>>"%TMP_CERT_INSTALL_PS%" echo ^        foreach ($c in $store.Certificates) { if ($c.Thumbprint.ToUpperInvariant() -eq $thumb) { $exists = $true; break } }
>>"%TMP_CERT_INSTALL_PS%" echo ^        if ($exists) { return 10 }
>>"%TMP_CERT_INSTALL_PS%" echo ^        $store.Add($cert)
>>"%TMP_CERT_INSTALL_PS%" echo ^        return 0
>>"%TMP_CERT_INSTALL_PS%" echo ^    } finally {
>>"%TMP_CERT_INSTALL_PS%" echo ^        $store.Close()
>>"%TMP_CERT_INSTALL_PS%" echo ^    }
>>"%TMP_CERT_INSTALL_PS%" echo }
>>"%TMP_CERT_INSTALL_PS%" echo try {
>>"%TMP_CERT_INSTALL_PS%" echo ^    $code = Add-ToRootStore ([System.Security.Cryptography.X509Certificates.StoreLocation]::LocalMachine)
>>"%TMP_CERT_INSTALL_PS%" echo ^    exit $code
>>"%TMP_CERT_INSTALL_PS%" echo } catch {
>>"%TMP_CERT_INSTALL_PS%" echo ^    try {
>>"%TMP_CERT_INSTALL_PS%" echo ^        $code = Add-ToRootStore ([System.Security.Cryptography.X509Certificates.StoreLocation]::CurrentUser)
>>"%TMP_CERT_INSTALL_PS%" echo ^        if ($code -eq 0) { exit 11 }
>>"%TMP_CERT_INSTALL_PS%" echo ^        exit $code
>>"%TMP_CERT_INSTALL_PS%" echo ^    } catch {
>>"%TMP_CERT_INSTALL_PS%" echo ^        exit 1
>>"%TMP_CERT_INSTALL_PS%" echo ^    }
>>"%TMP_CERT_INSTALL_PS%" echo }

powershell -NoProfile -ExecutionPolicy Bypass -File "%TMP_CERT_INSTALL_PS%"
set "CERT_INSTALL_RC=%ERRORLEVEL%"
if exist "%TMP_CERT_INSTALL_PS%" del "%TMP_CERT_INSTALL_PS%" >nul 2>&1

if "%CERT_INSTALL_RC%"=="10" (
    echo Certificate already trusted in Root store.
) else if "%CERT_INSTALL_RC%"=="11" (
    echo Certificate installed in CurrentUser Root store.
) else if "%CERT_INSTALL_RC%"=="0" (
    echo Certificate installed in LocalMachine Root store.
) else (
    echo [WARNING] Failed to install certificate into Root store.
    echo          HTTPS can still work; browser trust may show a warning.
    (2>&1 <nul >nul findstr "" <&0) 2>nul 1>nul
    if errorlevel 1 (
        start "Warning" cmd /k "echo [WARNING] Failed to install certificate into Root store. & echo HTTPS can still work; browser trust may show a warning. & echo. & echo Press any key to close... & pause"
    ) else (
        echo Continuing setup...
    )
)

echo.
echo [3/7] Adding domain to hosts file (if missing)...
findstr /R /I /C:"^[ ]*127\.0\.0\.1[ ]\+%DOMAIN%$" "%HOSTS_FILE%" >nul 2>&1
if "%errorlevel%"=="0" (
    echo hosts entry already exists.
) else (
    >>"%HOSTS_FILE%" echo 127.0.0.1 %DOMAIN%
    echo hosts entry added.
)

echo.
echo [4/7] Ensuring SSL directives in httpd.conf...
set "TMP_HTTPD_PS=%WORK_TMP%\update_httpd_conf_%RANDOM%.ps1"
>"%TMP_HTTPD_PS%" echo $p = '%HTTPD_CONF%'
>>"%TMP_HTTPD_PS%" echo $lines = Get-Content -Path $p
>>"%TMP_HTTPD_PS%" echo $required = @('LoadModule ssl_module modules/mod_ssl.so','LoadModule socache_shmcb_module modules/mod_socache_shmcb.so','Include conf/extra/httpd-ssl.conf','Include conf/extra/httpd-vhosts.conf')
>>"%TMP_HTTPD_PS%" echo $out = New-Object System.Collections.Generic.List[string]
>>"%TMP_HTTPD_PS%" echo foreach($line in $lines) { if ($line -notmatch '^\s*Listen\s+443\s*$') { $out.Add($line) } }
>>"%TMP_HTTPD_PS%" echo $lines = $out.ToArray()
>>"%TMP_HTTPD_PS%" echo foreach ($r in $required) {
>>"%TMP_HTTPD_PS%" echo 	$has = $false
>>"%TMP_HTTPD_PS%" echo 	for ($i = 0; $i -lt $lines.Count; $i++) {
>>"%TMP_HTTPD_PS%" echo 		if ($lines[$i] -match ('^[\s]*#[\s]*' + [regex]::Escape($r) + '[\s]*$')) { $lines[$i] = $r; $has = $true; break }
>>"%TMP_HTTPD_PS%" echo 		if ($lines[$i] -match ('^[\s]*' + [regex]::Escape($r) + '[\s]*$')) { $has = $true; break }
>>"%TMP_HTTPD_PS%" echo 	}
>>"%TMP_HTTPD_PS%" echo 	if (-not $has) { $lines += $r }
>>"%TMP_HTTPD_PS%" echo }
>>"%TMP_HTTPD_PS%" echo Set-Content -Path $p -Value $lines -Encoding ASCII
powershell -NoProfile -ExecutionPolicy Bypass -File "%TMP_HTTPD_PS%"
if not "%errorlevel%"=="0" (
    echo [ERROR] Failed updating httpd.conf.
    (2>&1 <nul >nul findstr "" <&0) 2>nul 1>nul
    if errorlevel 1 (
        start "Error" cmd /k "echo [ERROR] Failed updating httpd.conf. & echo. & echo Press any key to close... & pause"
    ) else (
        pause <con
    )
    if exist "%TMP_HTTPD_PS%" del "%TMP_HTTPD_PS%" >nul 2>&1
    exit /b 1
)
if exist "%TMP_HTTPD_PS%" del "%TMP_HTTPD_PS%" >nul 2>&1

echo.
echo.
echo [5/8] Writing VirtualHost config (rewrite safe block)...
set "TMP_VHOST_PS=%WORK_TMP%\write_vhost_%RANDOM%.ps1"
>"%TMP_VHOST_PS%" echo $ErrorActionPreference = 'Stop'
>>"%TMP_VHOST_PS%" echo try {
>>"%TMP_VHOST_PS%" echo ^    $p = '%VHOSTS_CONF%'
>>"%TMP_VHOST_PS%" echo ^    $domain = '%DOMAIN%'
>>"%TMP_VHOST_PS%" echo ^    $doc = '%DOCROOT_APACHE%'
>>"%TMP_VHOST_PS%" echo ^    $crt = '%CERT_FILE_APACHE%'
>>"%TMP_VHOST_PS%" echo ^    $key = '%KEY_FILE_APACHE%'
>>"%TMP_VHOST_PS%" echo ^    $mask = [regex]::Replace($domain, '[A-Za-z0-9]', '_')
>>"%TMP_VHOST_PS%" echo ^    $begin = '# BEGIN AUTO_HTTPS' + $mask
>>"%TMP_VHOST_PS%" echo ^    $end = '# END AUTO_HTTPS' + $mask
>>"%TMP_VHOST_PS%" echo ^    $content = Get-Content -Raw -Path $p
>>"%TMP_VHOST_PS%" echo ^    $content = [regex]::Replace($content, '(?im)^.*AUTO_HTTPS.*' + [regex]::Escape($domain) + '.*\r?\n?', '')
>>"%TMP_VHOST_PS%" echo ^    $content = [regex]::Replace($content, '(?s)# BEGIN AUTO_HTTPS.*?# END AUTO_HTTPS.*?\r?\n?', '')
>>"%TMP_VHOST_PS%" echo ^    $content = [regex]::Replace($content, '(?s)^\s*^<VirtualHost \*:80^>\s*ServerName\s+' + [regex]::Escape($domain) + '.*?^</VirtualHost^>\s*', '')
>>"%TMP_VHOST_PS%" echo ^    $content = [regex]::Replace($content, '(?s)^\s*^<VirtualHost \*:443^>\s*ServerName\s+' + [regex]::Escape($domain) + '.*?^</VirtualHost^>\s*', '')
>>"%TMP_VHOST_PS%" echo ^    $blockLines = @(
>>"%TMP_VHOST_PS%" echo ^        $begin,
>>"%TMP_VHOST_PS%" echo ^        '^<VirtualHost *:80^>',
>>"%TMP_VHOST_PS%" echo ^        '    ServerName ' + $domain,
>>"%TMP_VHOST_PS%" echo ^        '    DocumentRoot "' + $doc + '"',
>>"%TMP_VHOST_PS%" echo ^        '    Redirect "/" "https://' + $domain + '/"',
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
>>"%TMP_VHOST_PS%" echo ^        $end
>>"%TMP_VHOST_PS%" echo ^    )
>>"%TMP_VHOST_PS%" echo ^    $block = [string]::Join("`r`n", $blockLines)
>>"%TMP_VHOST_PS%" echo ^    $base = $content.TrimEnd()
>>"%TMP_VHOST_PS%" echo ^    if ([string]::IsNullOrWhiteSpace($base)) { $out = $block + "`r`n" } else { $out = $base + "`r`n`r`n" + $block + "`r`n" }
>>"%TMP_VHOST_PS%" echo ^    Set-Content -Path $p -Value $out -Encoding ASCII
>>"%TMP_VHOST_PS%" echo ^    exit 0
>>"%TMP_VHOST_PS%" echo } catch {
>>"%TMP_VHOST_PS%" echo ^    exit 1
>>"%TMP_VHOST_PS%" echo }

powershell -NoProfile -ExecutionPolicy Bypass -File "%TMP_VHOST_PS%"
set "VHOST_RC=%ERRORLEVEL%"
if exist "%TMP_VHOST_PS%" del "%TMP_VHOST_PS%" >nul 2>&1
if not "%VHOST_RC%"=="0" (
    echo [ERROR] Failed to write VirtualHost block to %VHOSTS_CONF%
    (2>&1 <nul >nul findstr "" <&0) 2>nul 1>nul
    if errorlevel 1 (
        start "Error" cmd /k "echo [ERROR] Failed to append VirtualHost block to %VHOSTS_CONF%. & echo. & echo Press any key to close... & pause"
    ) else (
        pause <con
    )
    exit /b 1
) else (
    echo VirtualHost config rewritten.
)
echo [6/8] Ensuring Listen 443 is active in httpd-ssl.conf...
set "SSL_CONF=%XAMPP_PATH%\apache\conf\extra\httpd-ssl.conf"
if not exist "%SSL_CONF%" goto :skip_ssl_fix
set "TMP_SSL_FIX_PS=%WORK_TMP%\fix_ssl_conf_%RANDOM%.ps1"
>"%TMP_SSL_FIX_PS%" echo $p = '%SSL_CONF%'
>>"%TMP_SSL_FIX_PS%" echo $lines = Get-Content -Path $p
>>"%TMP_SSL_FIX_PS%" echo $hasListen = $false
>>"%TMP_SSL_FIX_PS%" echo for ($i = 0; $i -lt $lines.Count; $i++) {
>>"%TMP_SSL_FIX_PS%" echo ^    if ($lines[$i] -match '^[\s]*#[\s]*Listen[\s]+443[\s]*$') { $lines[$i] = 'Listen 443'; $hasListen = $true; continue }
>>"%TMP_SSL_FIX_PS%" echo ^    if ($lines[$i] -match '^[\s]*Listen[\s]+443[\s]*$') { $hasListen = $true }
>>"%TMP_SSL_FIX_PS%" echo }
>>"%TMP_SSL_FIX_PS%" echo if (-not $hasListen) { $lines = ,('Listen 443') + $lines }
>>"%TMP_SSL_FIX_PS%" echo Set-Content -Path $p -Value $lines -Encoding ASCII
powershell -NoProfile -ExecutionPolicy Bypass -File "%TMP_SSL_FIX_PS%"
if not "%errorlevel%"=="0" echo [WARNING] Could not update Listen 443 in httpd-ssl.conf. Continuing anyway...
if exist "%TMP_SSL_FIX_PS%" del "%TMP_SSL_FIX_PS%" >nul 2>&1
goto :after_ssl_fix

:skip_ssl_fix
echo [WARNING] SSL config file not found: %SSL_CONF%

:after_ssl_fix

set "TMP_VHOST_CLEAN_PS=%WORK_TMP%\clean_vhosts_echo_%RANDOM%.ps1"
>"%TMP_VHOST_CLEAN_PS%" echo $p = '%VHOSTS_CONF%'
>>"%TMP_VHOST_CLEAN_PS%" echo $content = Get-Content -Raw -Path $p
>>"%TMP_VHOST_CLEAN_PS%" echo $content = [regex]::Replace($content, '(?im)^\s*echo\b.*\r?\n?', '')
>>"%TMP_VHOST_CLEAN_PS%" echo Set-Content -Path $p -Value $content -Encoding ASCII
powershell -NoProfile -ExecutionPolicy Bypass -File "%TMP_VHOST_CLEAN_PS%"
if exist "%TMP_VHOST_CLEAN_PS%" del "%TMP_VHOST_CLEAN_PS%" >nul 2>&1

echo.
echo [7/8] Validating Apache config...
"%HTTPD_EXE%" -t -f "%HTTPD_CONF%"
if not "%errorlevel%"=="0" (
    echo.
    echo [ERROR] Apache syntax check failed. Please review output above.
    (2>&1 <nul >nul findstr "" <&0) 2>nul 1>nul
    if errorlevel 1 (
        start "Error" cmd /k "echo [ERROR] Apache syntax check failed. Please review output above. & echo. & echo Press any key to close... & pause"
    ) else (
        pause <con
    )
    exit /b 1
)

echo.
echo [8/8] Attempting to restart Apache...
tasklist | findstr /I httpd >nul
if "%errorlevel%"=="0" (
    echo Stopping Apache...
    taskkill /IM httpd.exe /F >nul 2>&1
    timeout /t 2 /nobreak >nul
)
echo Starting Apache...
if exist "%XAMPP_PATH%\apache\bin\httpd.exe" (
    start "" "%XAMPP_PATH%\apache\bin\httpd.exe"
    timeout /t 3 /nobreak >nul
    tasklist | findstr /I httpd >nul
    if "%errorlevel%"=="0" (
        echo Apache started successfully.
    ) else (
        echo WARNING: Apache may not have started. Check XAMPP Control Panel.
    )
) else (
    echo WARNING: Could not find httpd.exe. Restart Apache from XAMPP Control Panel.
)

echo.
echo ================================================================
echo Setup complete.
echo Domain: https://%DOMAIN%
echo.
echo Next:
echo 1) Wait 5 seconds for Apache to fully start
echo 2) Open https://%DOMAIN%
echo 3) If certificate warning appears, it is expected for self-signed certs
echo.
echo If HTTPS still fails (ERR_SSL_PROTOCOL_ERROR):
echo   - Run test-https-setup.bat.bat to diagnose
echo   - Check Apache error logs in storage/logs
echo ================================================================
echo.
pause <con
exit /b 0

