@echo off
setlocal EnableExtensions EnableDelayedExpansion

:: Interactive HTTPS setup for XAMPP Apache on Windows
:: Run this file as Administrator.

title XAMPP HTTPS Setup Wizard

net session >nul 2>&1
if not "%errorlevel%"=="0" (
    echo.
    echo [ERROR] Please run this script as Administrator.
    echo Right click the .bat file and choose "Run as administrator".
    echo.
    pause
    exit /b 1
)

echo ================================================================
echo                 XAMPP HTTPS Setup Wizard
echo ================================================================
echo.
echo This script will:
echo 1) Create SSL certificate/key
echo 2) Add domain to Windows hosts file
echo 3) Add HTTP+HTTPS VirtualHost to Apache vhosts config
echo 4) Ensure required SSL directives are enabled in httpd.conf
echo 5) Validate Apache config
echo 6) Restart Apache automatically
echo 7) Trust certificate in Windows (optional, recommended)
echo 8) Configure Firefox to use Windows cert store (optional)
echo.

set "DEFAULT_XAMPP=D:\xampp"
set /p "XAMPP_PATH=Enter XAMPP path [D:\xampp]: "
if "%XAMPP_PATH%"=="" set "XAMPP_PATH=%DEFAULT_XAMPP%"

set "HTTPD_CONF=%XAMPP_PATH%\apache\conf\httpd.conf"
set "VHOSTS_CONF=%XAMPP_PATH%\apache\conf\extra\httpd-vhosts.conf"
set "HOSTS_FILE=C:\Windows\System32\drivers\etc\hosts"
set "OPENSSL_EXE=%XAMPP_PATH%\apache\bin\openssl.exe"
set "OPENSSL_CNF=%XAMPP_PATH%\apache\conf\openssl.cnf"
set "HTTPD_EXE=%XAMPP_PATH%\apache\bin\httpd.exe"
set "CRT_DIR=%XAMPP_PATH%\apache\conf\ssl.crt"
set "KEY_DIR=%XAMPP_PATH%\apache\conf\ssl.key"

if not exist "%HTTPD_CONF%" (
    echo.
    echo [ERROR] httpd.conf not found: %HTTPD_CONF%
    pause
    exit /b 1
)
if not exist "%VHOSTS_CONF%" (
    echo.
    echo [ERROR] httpd-vhosts.conf not found: %VHOSTS_CONF%
    pause
    exit /b 1
)
if not exist "%OPENSSL_EXE%" (
    echo.
    echo [ERROR] openssl.exe not found: %OPENSSL_EXE%
    pause
    exit /b 1
)
if not exist "%OPENSSL_CNF%" (
    echo.
    echo [ERROR] openssl.cnf not found: %OPENSSL_CNF%
    pause
    exit /b 1
)
if not exist "%HTTPD_EXE%" (
    echo.
    echo [ERROR] httpd.exe not found: %HTTPD_EXE%
    pause
    exit /b 1
)

echo.
set /p "DOMAIN=Enter domain (example: inv.almutlak.local): "
if "%DOMAIN%"=="" (
    echo [ERROR] Domain is required.
    pause
    exit /b 1
)

set "RAW_DOMAIN=%DOMAIN%"
set "DOMAIN=%DOMAIN:http://=%"
set "DOMAIN=%DOMAIN:https://=%"
for /f "tokens=1 delims=/" %%A in ("%DOMAIN%") do set "DOMAIN=%%A"
for /f "tokens=1 delims=:" %%A in ("%DOMAIN%") do set "DOMAIN=%%A"
for /f "tokens=* delims= " %%A in ("%DOMAIN%") do set "DOMAIN=%%A"

echo %DOMAIN%| findstr /R /I "^[a-z0-9][a-z0-9.-]*[a-z0-9]$" >nul
if not "%errorlevel%"=="0" (
    echo [ERROR] Invalid domain format: %RAW_DOMAIN%
    echo Please enter hostname only, for example: inv.almutlak.local
    pause
    exit /b 1
)

if /I not "%RAW_DOMAIN%"=="%DOMAIN%" (
    echo [INFO] Normalized domain to: %DOMAIN%
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
set "SAN=DNS:%DOMAIN%"

echo.
echo ---------------------- Summary ----------------------
echo XAMPP Path   : %XAMPP_PATH%
echo Domain       : %DOMAIN%
echo DocumentRoot : %DOCROOT%
echo Cert File    : %CERT_FILE%
echo Key File     : %KEY_FILE%
echo Subject      : %SUBJ%
echo SAN          : %SAN%
echo -----------------------------------------------------
echo.
choice /c YN /m "Proceed with setup"
if errorlevel 2 exit /b 1

if not exist "%CRT_DIR%" mkdir "%CRT_DIR%"
if not exist "%KEY_DIR%" mkdir "%KEY_DIR%"

echo.
echo [1/8] Creating SSL certificate...
set "OPENSSL_CONF=%OPENSSL_CNF%"
"%OPENSSL_EXE%" req -x509 -nodes -sha256 -days %DAYS% -newkey rsa:2048 -keyout "%KEY_FILE%" -out "%CERT_FILE%" -subj "%SUBJ%" -addext "subjectAltName=%SAN%" -addext "keyUsage=digitalSignature,keyEncipherment" -addext "extendedKeyUsage=serverAuth" -addext "basicConstraints=critical,CA:FALSE"
if not "%errorlevel%"=="0" (
    echo [ERROR] Failed to create certificate.
    echo This OpenSSL build may not support -addext. Update OpenSSL/XAMPP if needed.
    pause
    exit /b 1
)

echo.
echo [2/8] Adding domain to hosts file (if missing)...
findstr /R /I /C:"^[ ]*127\.0\.0\.1[ ]\+%DOMAIN%$" "%HOSTS_FILE%" >nul 2>&1
if "%errorlevel%"=="0" (
    echo hosts entry already exists.
) else (
    >>"%HOSTS_FILE%" echo 127.0.0.1 %DOMAIN%
    echo hosts entry added.
)

echo.
echo [3/8] Ensuring SSL directives in httpd.conf...
powershell -NoProfile -ExecutionPolicy Bypass -Command ^
"$p='%HTTPD_CONF%'; $lines=Get-Content -Path $p; $list=New-Object 'System.Collections.Generic.List[string]'; $list.AddRange([string[]]$lines); ^
$required=@('LoadModule ssl_module modules/mod_ssl.so','LoadModule socache_shmcb_module modules/mod_socache_shmcb.so','Include conf/extra/httpd-ssl.conf','Include conf/extra/httpd-vhosts.conf'); ^
foreach($r in $required){ ^
  $has=$false; ^
    for($i=0;$i -lt $list.Count;$i++){ ^
        if($list[$i] -match ('^\s*#\s*' + [regex]::Escape($r) + '\s*$')){ $list[$i]=$r; $has=$true; break } ^
        if($list[$i] -match ('^\s*' + [regex]::Escape($r) + '\s*$')){ $has=$true; break } ^
  } ^
    if(-not $has){ $list.Add($r) } ^
}; ^
$idx = New-Object System.Collections.Generic.List[int]; ^
for($i=0;$i -lt $list.Count;$i++){ ^
        if($list[$i] -match '^\s*#?\s*Listen\s+(([^ \t]+:)?443)(\s+.*)?$'){ $idx.Add($i) } ^
} ^
for($j=$idx.Count-1; $j -ge 0; $j--){ $list.RemoveAt($idx[$j]) } ^
Set-Content -Path $p -Value $list -Encoding ASCII"
if not "%errorlevel%"=="0" (
    echo [ERROR] Failed updating httpd.conf.
    pause
    exit /b 1
)

echo.
echo [4/8] Writing VirtualHost config (replace old block for this domain)...
set "TMP_PS=%TEMP%\xampp_https_vhost_%RANDOM%.ps1"
>"%TMP_PS%" echo $vhostPath = "%VHOSTS_CONF%"
>>"%TMP_PS%" echo $domain = "%DOMAIN%"
>>"%TMP_PS%" echo $docroot = "%DOCROOT%" -replace '\\','/'
>>"%TMP_PS%" echo $crt = "%CERT_FILE%" -replace '\\','/'
>>"%TMP_PS%" echo $key = "%KEY_FILE%" -replace '\\','/'
>>"%TMP_PS%" echo $tag = ($domain -replace '[^a-zA-Z0-9]','_')
>>"%TMP_PS%" echo $begin = "# BEGIN AUTO_HTTPS_$tag"
>>"%TMP_PS%" echo $end = "# END AUTO_HTTPS_$tag"
>>"%TMP_PS%" echo $content = Get-Content -Raw -Path $vhostPath
>>"%TMP_PS%" echo $pattern = [regex]::Escape($begin) + '(?s).*?' + [regex]::Escape($end)
>>"%TMP_PS%" echo $block = @"
>>"%TMP_PS%" echo # BEGIN AUTO_HTTPS_$tag
>>"%TMP_PS%" echo ^<VirtualHost *:80^>
>>"%TMP_PS%" echo     ServerName $domain
>>"%TMP_PS%" echo     DocumentRoot "$docroot"
>>"%TMP_PS%" echo     Redirect "/" "https://$domain/"
>>"%TMP_PS%" echo     ^<Directory "$docroot"^>
>>"%TMP_PS%" echo         AllowOverride All
>>"%TMP_PS%" echo         Require all granted
>>"%TMP_PS%" echo     ^</Directory^>
>>"%TMP_PS%" echo ^</VirtualHost^>
>>"%TMP_PS%" echo(
>>"%TMP_PS%" echo ^<VirtualHost *:443^>
>>"%TMP_PS%" echo     ServerName $domain
>>"%TMP_PS%" echo     DocumentRoot "$docroot"
>>"%TMP_PS%" echo     SSLEngine on
>>"%TMP_PS%" echo     SSLCertificateFile "$crt"
>>"%TMP_PS%" echo     SSLCertificateKeyFile "$key"
>>"%TMP_PS%" echo     ^<Directory "$docroot"^>
>>"%TMP_PS%" echo         AllowOverride All
>>"%TMP_PS%" echo         Require all granted
>>"%TMP_PS%" echo     ^</Directory^>
>>"%TMP_PS%" echo ^</VirtualHost^>
>>"%TMP_PS%" echo # END AUTO_HTTPS_$tag
>>"%TMP_PS%" echo "@
>>"%TMP_PS%" echo if($content -match $pattern){
>>"%TMP_PS%" echo   $new = [regex]::Replace($content,$pattern,[System.Text.RegularExpressions.MatchEvaluator]{ param($m) $block })
>>"%TMP_PS%" echo } else {
>>"%TMP_PS%" echo   $new = $content.TrimEnd() + "`r`n`r`n" + $block + "`r`n"
>>"%TMP_PS%" echo }
>>"%TMP_PS%" echo $new = [regex]::Replace($new,'(?im)^\s*ECHO( is off\.)?\s*$','').TrimEnd() + "`r`n"
>>"%TMP_PS%" echo Set-Content -Path $vhostPath -Value $new -Encoding ASCII

powershell -NoProfile -ExecutionPolicy Bypass -File "%TMP_PS%"
del "%TMP_PS%" >nul 2>&1
if not "%errorlevel%"=="0" (
    echo [ERROR] Failed updating vhosts file.
    pause
    exit /b 1
)

echo.
echo [5/8] Validating Apache config...
"%HTTPD_EXE%" -t -f "%HTTPD_CONF%"
if not "%errorlevel%"=="0" (
    echo.
    echo [ERROR] Apache syntax check failed. Please review output above.
    pause
    exit /b 1
)

echo.
echo [6/8] Restarting Apache...
"%HTTPD_EXE%" -k restart -f "%HTTPD_CONF%" >nul 2>&1
if not "%errorlevel%"=="0" (
    echo Restart command failed. Trying start command...
    "%HTTPD_EXE%" -k start -f "%HTTPD_CONF%" >nul 2>&1
    if not "%errorlevel%"=="0" (
        echo [WARNING] Could not restart/start Apache automatically.
        echo Please restart Apache manually from XAMPP Control Panel.
    ) else (
        echo Apache started successfully.
    )
) else (
    echo Apache restarted successfully.
)

echo.
echo [7/8] Trust certificate in Windows certificate stores...
choice /c YN /m "Import certificate now (Machine+User TrustedPeople + Root)"
if errorlevel 2 (
    echo Skipped certificate trust import.
) else (
    call :ImportCertAllStores "%DOMAIN%" "%CERT_FILE%"
    if not "%errorlevel%"=="0" (
        echo [WARNING] Certificate import step did not complete successfully.
    )
)

echo.
echo [8/8] Configure Firefox to use Windows certificate store...
choice /c YN /m "Enable Firefox enterprise roots policy now"
if errorlevel 2 (
    echo Skipped Firefox policy setup.
) else (
    set "FF_DIST_DIR="
    if exist "%ProgramFiles%\Mozilla Firefox\distribution" set "FF_DIST_DIR=%ProgramFiles%\Mozilla Firefox\distribution"
    if "%FF_DIST_DIR%"=="" if exist "%ProgramFiles(x86)%\Mozilla Firefox\distribution" set "FF_DIST_DIR=%ProgramFiles(x86)%\Mozilla Firefox\distribution"

    if "%FF_DIST_DIR%"=="" (
        echo [WARNING] Firefox distribution folder not found.
        echo If Firefox is installed, set security.enterprise_roots.enabled=true in about:config manually.
    ) else (
        if not exist "%FF_DIST_DIR%" mkdir "%FF_DIST_DIR%"
        >"%FF_DIST_DIR%\policies.json" echo {
        >>"%FF_DIST_DIR%\policies.json" echo   "policies": {
        >>"%FF_DIST_DIR%\policies.json" echo     "Certificates": {
        >>"%FF_DIST_DIR%\policies.json" echo       "ImportEnterpriseRoots": true
        >>"%FF_DIST_DIR%\policies.json" echo     }
        >>"%FF_DIST_DIR%\policies.json" echo   }
        >>"%FF_DIST_DIR%\policies.json" echo }
        echo Firefox policy written: %FF_DIST_DIR%\policies.json
        echo Restart Firefox fully to apply this policy.
    )
)

echo.
echo ================================================================
echo Setup complete.
echo Domain: https://%DOMAIN%
echo.
echo Next:
echo 1) Open https://%DOMAIN%
echo 2) If browser is already open, close and reopen it
echo 3) In Firefox, verify URL starts with https:// and certificate warning is gone
echo ================================================================
echo.
pause
exit /b 0

:ImportCertAllStores
set "IMPORT_DOMAIN=%~1"
set "IMPORT_CERT=%~2"

where certutil >nul 2>&1
if not "%errorlevel%"=="0" (
    echo [WARNING] certutil is not available. Cannot import certificate automatically.
    exit /b 1
)

if not exist "%IMPORT_CERT%" (
    echo [WARNING] Certificate file not found: %IMPORT_CERT%
    exit /b 1
)

certutil -delstore TrustedPeople "%IMPORT_DOMAIN%" >nul 2>&1
certutil -delstore Root "%IMPORT_DOMAIN%" >nul 2>&1
certutil -user -delstore TrustedPeople "%IMPORT_DOMAIN%" >nul 2>&1
certutil -user -delstore Root "%IMPORT_DOMAIN%" >nul 2>&1

certutil -f -addstore TrustedPeople "%IMPORT_CERT%" >nul 2>&1
if not "%errorlevel%"=="0" exit /b 1
certutil -f -addstore Root "%IMPORT_CERT%" >nul 2>&1
certutil -user -f -addstore TrustedPeople "%IMPORT_CERT%" >nul 2>&1
certutil -user -f -addstore Root "%IMPORT_CERT%" >nul 2>&1

certutil -store TrustedPeople "%IMPORT_DOMAIN%" | findstr /I "Serial Thumbprint" >nul 2>&1
if not "%errorlevel%"=="0" exit /b 1

echo Certificate imported into Machine/User stores successfully.
exit /b 0
