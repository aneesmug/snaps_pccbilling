@echo off
setlocal EnableDelayedExpansion
cd /d "%~dp0"

echo.
echo ============================================================
echo   ZATCA CSR and Private Key Generator - SnapS Billing
echo ============================================================
echo.
echo  Please fill in your company details below.
echo  Press ENTER after each value.
echo.

REM ─── Field 1: VAT Number ────────────────────────────────────
echo [1/6] VAT / Organization Identifier
echo       Your 15-digit VAT registration number.
echo       Example: 399999999900003
set /p VAT="  Enter value: "
if "!VAT!"=="" (
    echo   ERROR: VAT number cannot be empty.
    goto :error
)
echo(!VAT!| findstr /r "^[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]$" >nul
if errorlevel 1 (
    echo   ERROR: VAT number must be exactly 15 digits.
    goto :error
)

REM ─── Field 2: Branch Name ───────────────────────────────────
echo.
echo [2/6] Organization Unit (Branch Name or Member TIN)
echo       Example: Hira Branch
set /p UNIT="  Enter value: "
if "!UNIT!"=="" (
    echo   ERROR: Branch name cannot be empty.
    goto :error
)

REM SDK expects organization.unit.name as 10-digit TIN for VAT group member onboarding.
set "UNIT_TIN=!UNIT!"
echo(!UNIT_TIN!| findstr /r "^[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]$" >nul
if errorlevel 1 (
    set "UNIT_TIN=!VAT:~0,10!"
    echo   NOTICE: Using first 10 digits of VAT as Organization Unit TIN for SDK: !UNIT_TIN!
)

REM ─── Auto-build Common Name and Serial from VAT + Branch ────
set "BRANCH_CLEAN=!UNIT: =!"
for /f %%G in ('powershell -NoProfile -Command "[guid]::NewGuid().ToString()"') do set "UUID=%%G"
set "CN=TST-!BRANCH_CLEAN!-!VAT!"
set "SERIAL=1-TST|2-!BRANCH_CLEAN!|3-!UUID!"
echo.
echo   Auto-built Common Name : !CN!
echo   Auto-built Serial      : !SERIAL!

REM ─── Field 3: Company Name ──────────────────────────────────
echo.
echo [3/6] Organization Name (Legal Company Name)
echo       Example: SnapS Production
set /p ORG="  Enter value: "
if "!ORG!"=="" (
    echo   ERROR: Company name cannot be empty.
    goto :error
)

REM ─── Build safe file names from company name ────────────────
set "COMPANY_FILE=!ORG!"
for %%C in (^" \ / : * ? ^< ^> ^|) do set "COMPANY_FILE=!COMPANY_FILE:%%C=_!"
set "COMPANY_FILE=!COMPANY_FILE:.=_!"
set "COMPANY_FILE=!COMPANY_FILE: = !"
for /f "tokens=* delims= " %%A in ("!COMPANY_FILE!") do set "COMPANY_FILE=%%A"
if "!COMPANY_FILE:~-1!"=="." set "COMPANY_FILE=!COMPANY_FILE:~0,-1!"
if "!COMPANY_FILE!"=="" set "COMPANY_FILE=company"
set "COMPANY_DIR_REL=Certificates\!COMPANY_FILE!"
set "COMPANY_DIR_ABS=%~dp0!COMPANY_DIR_REL!"
set "PRIVATE_KEY_FILE_ABS=!COMPANY_DIR_ABS!\private-key.pem"
set "CSR_FILE_ABS=!COMPANY_DIR_ABS!\request.csr"
set "SDK_PRIVATE_KEY_FILE=Data\Certificates\ec-secp256k1-priv-key.pem"
set "SDK_CSR_FILE=Data\Certificates\cert.pem"
set "SDK_PRIVATE_KEY_FILE_ABS=%~dp0!SDK_PRIVATE_KEY_FILE!"
set "SDK_CSR_FILE_ABS=%~dp0!SDK_CSR_FILE!"

REM ─── Field 4: Location ──────────────────────────────────────
echo.
echo [4/6] Location Address / Branch Code
echo       Example: Jeddah
set /p LOCATION="  Enter value: "
if "!LOCATION!"=="" (
    echo   ERROR: Location cannot be empty.
    goto :error
)

REM ─── Field 5: Business Category ─────────────────────────────
echo.
echo [5/6] Industry / Business Category
echo       Example: Production
set /p CATEGORY="  Enter value: "
if "!CATEGORY!"=="" (
    echo   ERROR: Business category cannot be empty.
    goto :error
)

REM ─── Field 6: Invoice Type ──────────────────────────────────
echo.
echo [6/6] Invoice Type Code
echo       1000 = Standard (B2B)
echo       0100 = Simplified (B2C / POS)
echo       1100 = Both Standard and Simplified
set /p INVTYPE="  Enter value [press Enter for default 1100]: "
if "!INVTYPE!"=="" set "INVTYPE=1100"

REM --- Summary ---
echo.
echo ============================================================
echo   Summary - Please review before generating
echo ============================================================
echo   Common Name         : !CN!
echo   Serial Number       : !SERIAL!
echo   VAT Number          : !VAT!
echo   Branch (Unit)       : !UNIT!
echo   SDK Org Unit TIN    : !UNIT_TIN!
echo   Company             : !ORG!
echo   Location            : !LOCATION!
echo   Business Category   : !CATEGORY!
echo   Invoice Type        : !INVTYPE!
echo ============================================================
echo.
set /p CONFIRM="  Generate PEM and CSR now? (Y/N): "
if /i "!CONFIRM!" neq "Y" (
    echo   Cancelled. No files were generated.
    goto :end
)

REM --- Write CSR config file (line by line to avoid pipe-break issue) ---
set "CONFIG_FILE=%~dp0Data\Input\snaps-csr-config.properties"
echo csr.common.name=!CN!> "!CONFIG_FILE!"
set "SERIAL_ESC=!SERIAL:|=^|!"
echo csr.serial.number=!SERIAL_ESC!>> "!CONFIG_FILE!"
echo csr.organization.identifier=!VAT!>> "!CONFIG_FILE!"
echo csr.organization.unit.name=!UNIT_TIN!>> "!CONFIG_FILE!"
echo csr.organization.name=!ORG!>> "!CONFIG_FILE!"
echo csr.country.name=SA>> "!CONFIG_FILE!"
echo csr.invoice.type=!INVTYPE!>> "!CONFIG_FILE!"
echo csr.location.address=!LOCATION!>> "!CONFIG_FILE!"
echo csr.industry.business.category=!CATEGORY!>> "!CONFIG_FILE!"

echo.
echo   Config saved to: Data\Input\snaps-csr-config.properties
echo   Generating PEM and CSR files...
echo.

REM --- Run SDK CSR generation ---
set "FATOORA_HOME=%~dp0Apps"
set "SDK_CONFIG=%~dp0Configuration\config.json"
set "PATH=%~dp0Apps;%PATH%"

if not exist "%~dp0Apps\jq.exe" (
    echo   ERROR: jq.exe not found at Apps\jq.exe
    goto :end
)

if not exist "%~dp0Apps\fatoora.bat" (
    echo   ERROR: fatoora.bat not found at Apps\fatoora.bat
    goto :end
)

if not exist "%~dp0Data\Certificates" mkdir "%~dp0Data\Certificates"
if not exist "%~dp0Certificates" mkdir "%~dp0Certificates"

if exist "!SDK_PRIVATE_KEY_FILE_ABS!" del /q "!SDK_PRIVATE_KEY_FILE_ABS!" >nul 2>nul
if exist "!SDK_CSR_FILE_ABS!" del /q "!SDK_CSR_FILE_ABS!" >nul 2>nul
if exist "%~dp0Data\Certificates\COMPANY_FILE" del /q "%~dp0Data\Certificates\COMPANY_FILE" >nul 2>nul

call "%~dp0Apps\fatoora.bat" -csr -pem -csrConfig "Data\Input\snaps-csr-config.properties" -privateKey "!SDK_PRIVATE_KEY_FILE!" -generatedCsr "!SDK_CSR_FILE!"
set "SDK_EXIT=%ERRORLEVEL%"
echo   SDK exit code: !SDK_EXIT!

echo.
if exist "!SDK_PRIVATE_KEY_FILE_ABS!" (
    if exist "!SDK_CSR_FILE_ABS!" (
        if not exist "!COMPANY_DIR_ABS!" mkdir "!COMPANY_DIR_ABS!"
        copy /Y "!SDK_PRIVATE_KEY_FILE_ABS!" "!PRIVATE_KEY_FILE_ABS!" >nul
        copy /Y "!SDK_CSR_FILE_ABS!" "!CSR_FILE_ABS!" >nul
        echo ============================================================
        echo   SUCCESS - Files generated:
        echo.
        echo   Private Key : !COMPANY_DIR_REL!\private-key.pem
        echo   CSR File    : !COMPANY_DIR_REL!\request.csr
        echo.
        echo   NEXT STEPS:
        echo   1. Submit the generated CSR file to ZATCA Fatoora portal
        echo   2. Paste the generated private key content into your
        echo      SnapS app ZATCA settings (zatca_private_key field^)
        echo ============================================================
    ) else (
        echo   ERROR: CSR file was not created. Check SDK output above.
    )
) else (
    echo   ERROR: Private key was not created. Check SDK output above.
)

goto :end

:error
echo.
echo   Generation cancelled due to missing required field.

:end
echo.
echo   This window will close automatically in 10 seconds...
ping 127.0.0.1 -n 11 >nul
endlocal
