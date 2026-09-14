<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{__('System Locked')}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0b0b0d;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            padding: 24px;
        }
        .lock-card {
            width: 100%;
            max-width: 400px;
            background: #17171a;
            border: 1px solid #26262b;
            border-radius: 16px;
            padding: 36px 32px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,.5);
        }
        .lock-card h1 {
            color: #fff;
            font-size: 1.4rem;
            font-weight: 700;
            margin: 0 0 8px;
        }
        .lock-card .subtitle {
            color: #9a9aa2;
            font-size: .92rem;
            margin: 0 0 22px;
            line-height: 1.4;
        }
        .lock-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 24px;
            border-radius: 50%;
            border: 2px solid #e5484d;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .lock-icon svg { width: 24px; height: 24px; stroke: #e5484d; }
        .field {
            text-align: left;
            margin-bottom: 16px;
        }
        .field label {
            display: block;
            color: #c8c8ce;
            font-size: .8rem;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .field input {
            width: 100%;
            background: #0f0f11;
            border: 1px solid #2c2c32;
            border-radius: 10px;
            padding: 11px 14px;
            color: #e8e8ea;
            font-size: .9rem;
            outline: none;
        }
        .field input:focus { border-color: #e5484d; }
        .error-text {
            color: #f16065;
            font-size: .85rem;
            text-align: left;
            margin: -4px 0 18px;
            line-height: 1.4;
        }
        .btn-activate {
            width: 100%;
            background: #f0883e;
            color: #1a1006;
            border: none;
            border-radius: 10px;
            padding: 13px 14px;
            font-size: .95rem;
            font-weight: 700;
            cursor: pointer;
        }
        .btn-activate:hover { background: #f39a5c; }
        .contact-text { color: #c8c8ce; font-size: .9rem; margin: 0; }
    </style>
</head>
<body>
    <div class="lock-card">
        <h1>{__('System Locked')}</h1>
        <p class="subtitle">
            {if $license_message}{$license_message}{else}{__('This installation is not licensed or the license could not be verified.')}{/if}
        </p>

        <div class="lock-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="4" y="11" width="16" height="10" rx="2"></rect>
                <path d="M8 11V7a4 4 0 0 1 8 0v4"></path>
            </svg>
        </div>

        {if $is_system_admin}
            <form method="post" action="{$_url}license/verify">
                <div class="field">
                    <label for="serial_key">{__('Serial Key')}</label>
                    <input type="text" id="serial_key" name="serial_key" value="{$license_serial_key|default: ''}" autocomplete="off">
                </div>

                <div class="field">
                    <label for="verify_url">{__('Verify URL')}</label>
                    <input type="text" id="verify_url" name="verify_url" value="{$license_verify_url|default: ''}" placeholder="https://license-server.example.com/api/check.php?token=XXXX" autocomplete="off">
                </div>

                {if $license_error}
                    <div class="error-text">{$license_error}</div>
                {/if}

                <button type="submit" class="btn-activate">{__('Verify & Activate')}</button>
            </form>
        {else}
            <p class="contact-text">{__('Please contact your system administrator.')}</p>
        {/if}
    </div>
</body>
</html>
