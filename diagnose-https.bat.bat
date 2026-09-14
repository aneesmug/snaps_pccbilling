@echo off
setlocal EnableExtensions EnableDelayedExpansion

title HTTPS Diagnosis Tool

echo ================================================================
echo                  HTTPS Configuration Diagnosis
echo ================================================================
echo.

:: Get XAMPP path
set "XAMPP_PATH=D:\xampp"
if exist "%XAMPP_PATH%\apache\conf\httpd.conf" goto xampp_found
echo ERROR: XAMPP not found at D:\xampp
pause <con
exit /b 1

:xampp_found
set "VHOSTS_CONF=%XAMPP_PATH%\apache\conf\extra\httpd-vhosts.conf"
set "HTTPD_CONF=%XAMPP_PATH%\apache\conf\httpd.conf"
set "SSL_CONF=%XAMPP_PATH%\apache\conf\extra\httpd-ssl.conf"
set "ERROR_LOG=%XAMPP_PATH%\apache\logs\error.log"
set "DOMAIN=inv.snapspro.local"
set "CERT_DIR=%XAMPP_PATH%\apache\conf\ssl.crt"
set "KEY_DIR=%XAMPP_PATH%\apache\conf\ssl.key"
set "CERT_FILE=%CERT_DIR%\%DOMAIN%.crt"
set "KEY_FILE=%KEY_DIR%\%DOMAIN%.key"

echo [TEST 1] Checking certificate files exist...
if exist "%CERT_FILE%" (
    echo ✓ Certificate file found: %CERT_FILE%
) else (
    echo ✗ MISSING: %CERT_FILE%
    dir "%CERT_DIR%" 2>nul | findstr /I ".crt" || echo   (Directory empty)
)
echo.

if exist "%KEY_FILE%" (
    echo ✓ Key file found: %KEY_FILE%
) else (
    echo ✗ MISSING: %KEY_FILE%
    dir "%KEY_DIR%" 2>nul | findstr /I ".key" || echo   (Directory empty)
)
echo.

echo [TEST 2] Checking VirtualHost block...
findstr /I "%DOMAIN%" "%VHOSTS_CONF%" >nul 2>&1
if "%errorlevel%"=="0" (
    echo ✓ VirtualHost for %DOMAIN% found in httpd-vhosts.conf
    echo.
    echo   --- Relevant lines from vhosts config: ---
    findstr /I /C:"%DOMAIN%" /C:"SSLCertificateFile" /C:"SSLCertificateKeyFile" "%VHOSTS_CONF%" | findstr /N "."
    echo   --- End excerpt ---
    echo.
) else (
    echo ✗ VirtualHost for %DOMAIN% NOT found in vhosts config
)
echo.

echo [TEST 3] Checking SSL module loaded in httpd.conf...
findstr /I "LoadModule ssl_module" "%HTTPD_CONF%" | findstr /V "^#" >nul 2>&1
if "%errorlevel%"=="0" (
    echo ✓ SSL module is loaded
) else (
    echo ✗ SSL module NOT active (commented or missing)
)
echo.

echo [TEST 4] Checking httpd-ssl.conf is included...
findstr /I "Include.*httpd-ssl.conf" "%HTTPD_CONF%" | findstr /V "^#" >nul 2>&1
if "%errorlevel%"=="0" (
    echo ✓ httpd-ssl.conf is included
) else (
    echo ✗ httpd-ssl.conf NOT included (commented or missing)
)
echo.

echo [TEST 5] Checking httpd-vhosts.conf is included...
findstr /I "Include.*httpd-vhosts.conf" "%HTTPD_CONF%" | findstr /V "^#" >nul 2>&1
if "%errorlevel%"=="0" (
    echo ✓ httpd-vhosts.conf is included
) else (
    echo ✗ httpd-vhosts.conf NOT included (commented or missing)
)
echo.

echo [TEST 6] Checking Listen 443 is active...
findstr /I "^Listen 443" "%SSL_CONF%" >nul 2>&1
if "%errorlevel%"=="0" (
    echo ✓ Listen 443 is active in httpd-ssl.conf
) else (
    echo ✗ Listen 443 NOT active (commented or missing)
    echo   Checking if it exists commented...
    findstr /I "Listen 443" "%SSL_CONF%" | head -3
)
echo.

echo [TEST 7] Apache config validation...
"%XAMPP_PATH%\apache\bin\httpd.exe" -t -f "%HTTPD_CONF%" >"%TEMP%\httpd_test.log" 2>&1
if "%errorlevel%"=="0" (
    echo ✓ Apache config syntax is valid
) else (
    echo ✗ Apache config has ERRORS:
    type "%TEMP%\httpd_test.log"
)
echo.

echo [TEST 8] Checking if port 443 is listening...
netstat -anb 2>nul | findstr /I ":443" | findstr /I "httpd\|LISTENING" >nul 2>&1
if "%errorlevel%"=="0" (
    echo ✓ Port 443 appears to be listening
    netstat -ano 2>nul | findstr ":443 "
) else (
    echo ⚠ Port 443 may not be listening yet
    echo   (This is OK if Apache just started)
)
echo.

echo [TEST 9] Checking Apache error log (last 30 lines)...
if exist "%ERROR_LOG%" (
    echo --- Last 30 lines of error.log ---
    powershell -NoProfile -Command "Get-Content '%ERROR_LOG%' -Tail 30"
    echo --- End error.log ---
) else (
    echo ERROR_LOG not found: %ERROR_LOG%
)
echo.

echo [TEST 10] Certificate validity check...
if exist "%CERT_FILE%" (
    echo Checking certificate details...
    "%XAMPP_PATH%\apache\bin\openssl.exe" x509 -in "%CERT_FILE%" -text -noout | findstr /I "Subject:\|Issuer:\|Not Before\|Not After"
) else (
    echo Skipped (cert file missing)
)
echo.

echo ================================================================
echo Diagnosis complete.
echo.
echo If you see ✗ marks above, those are issues to fix.
echo ================================================================
echo.
pause <con
