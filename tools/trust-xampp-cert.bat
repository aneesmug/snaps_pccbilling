@echo off
setlocal EnableExtensions EnableDelayedExpansion

:: Trust a local XAMPP-generated certificate in Windows Trusted Root
:: Run this file as Administrator.

title Trust Local HTTPS Certificate

net session >nul 2>&1
if not "%errorlevel%"=="0" (
    echo.
    echo [ERROR] Please run this script as Administrator.
    echo Right click the .bat file and choose "Run as administrator".
    echo.
    pause
    exit /b 1
)

where certutil >nul 2>&1
if not "%errorlevel%"=="0" (
    echo.
    echo [ERROR] certutil is not available on this system.
    pause
    exit /b 1
)

echo ================================================================
echo            Local Certificate Trust Wizard (Windows)
echo ================================================================
echo.
echo This script will import your .crt into:
echo Local Computer and Current User ^> Trusted People
echo Local Computer and Current User ^> Trusted Root Certification Authorities
echo.

set "DEFAULT_XAMPP=D:\xampp"
set /p "XAMPP_PATH=Enter XAMPP path [D:\xampp]: "
if "%XAMPP_PATH%"=="" set "XAMPP_PATH=%DEFAULT_XAMPP%"

set /p "DOMAIN=Enter domain (example: inv.almutlak.local): "
if "%DOMAIN%"=="" (
    echo.
    echo [ERROR] Domain is required.
    pause
    exit /b 1
)

set "DEFAULT_CERT=%XAMPP_PATH%\apache\conf\ssl.crt\%DOMAIN%.crt"
set /p "CERT_PATH=Enter certificate path [%DEFAULT_CERT%]: "
if "%CERT_PATH%"=="" set "CERT_PATH=%DEFAULT_CERT%"

if not exist "%CERT_PATH%" (
    echo.
    echo [ERROR] Certificate file not found:
    echo %CERT_PATH%
    pause
    exit /b 1
)

echo.
echo ---------------------- Summary ----------------------
echo Domain     : %DOMAIN%
echo Cert Path  : %CERT_PATH%
echo Store      : Machine/User TrustedPeople and Machine/User Root
echo -----------------------------------------------------
echo.
choice /c YN /m "Proceed with certificate trust setup"
if errorlevel 2 exit /b 1

echo.
echo [1/2] Removing old certs with same domain (if any)...
certutil -delstore TrustedPeople "%DOMAIN%" >nul 2>&1
certutil -delstore Root "%DOMAIN%" >nul 2>&1
certutil -user -delstore TrustedPeople "%DOMAIN%" >nul 2>&1
certutil -user -delstore Root "%DOMAIN%" >nul 2>&1
echo Done.

echo.
echo [2/2] Importing certificate into stores...
certutil -f -addstore TrustedPeople "%CERT_PATH%" >nul 2>&1
certutil -f -addstore Root "%CERT_PATH%" >nul 2>&1
certutil -user -f -addstore TrustedPeople "%CERT_PATH%" >nul 2>&1
certutil -user -f -addstore Root "%CERT_PATH%" >nul 2>&1

certutil -store TrustedPeople "%DOMAIN%" | findstr /I "Serial Thumbprint" >nul 2>&1
if not "%errorlevel%"=="0" (
    echo.
    echo [ERROR] Failed to verify certificate import.
    pause
    exit /b 1
)

echo.
echo ================================================================
echo Certificate imported successfully.
echo You can now open: https://%DOMAIN%
echo If browser was open, close and reopen it.
echo ================================================================
echo.
pause
exit /b 0
