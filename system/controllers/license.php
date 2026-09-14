<?php
if (!defined('APP_RUN')) {
    exit('No direct access allowed');
}

global $ui, $_L, $config, $routes;

$action = $routes[1] ?? 'locked';

$user = getLoggedInUser();
$is_system_admin = $user && (int) $user->roleid === 0;

$renderLocked = function ($errorMessage = null) use ($ui, $config, $is_system_admin) {
    $record = class_exists('License') ? License::record() : null;

    $ui->assign('_title', 'System Locked - ' . ($config['CompanyName'] ?? ''));
    $ui->assign('license_status', $record->status ?? 'not_configured');
    $ui->assign('license_message', $record->message ?? '');
    $ui->assign('license_serial_key', $record->serial_key ?? '');
    $ui->assign('license_verify_url', $record->verify_url ?? '');
    $ui->assign('license_error', $errorMessage);
    $ui->assign('is_system_admin', $is_system_admin);

    \view('license_locked');
};

switch ($action) {
    case 'verify':
        _auth();

        if (!$is_system_admin) {
            r2(U . 'license/locked/', 'e', 'Only a system administrator can activate the license.');
        }

        $serial_key = _post('serial_key');
        $verify_url = _post('verify_url');

        if ($serial_key === '' || $verify_url === '') {
            $renderLocked('Serial key and Verify URL are required.');
            break;
        }

        $domain = $_SERVER['HTTP_HOST'] ?? parse_url(APP_URL, PHP_URL_HOST);
        $result = License::saveAndVerify($serial_key, $verify_url, $domain);

        if (is_array($result) && !empty($result['valid'])) {
            r2(U, 's', 'License activated successfully.');
        }

        $message = is_array($result)
            ? ($result['message'] ?? 'License verification failed.')
            : 'Could not reach the license server. Check the key and try again.';

        $renderLocked($message);

        break;

    case 'locked':
    default:
        $renderLocked();

        break;
}
