<?php
/**
 * Pulls the latest commit from GitHub into a live checkout, then runs whatever the project
 * needs. Copy this one file into any project: plain PHP, Laravel, React/Vite, or a mix.
 *
 * Three ways in:
 *   GET  ?key=SECRET&action=status   - read-only preview (fetch + what would change + steps)
 *   GET  ?key=SECRET&action=deploy   - back up, sync to origin/<branch>, restore configs, run steps
 *   POST from GitHub webhook         - same as deploy, verified by X-Hub-Signature-256
 *   Add &force=1 to deploy to run every step even when nothing changed.
 *
 * Where to put it: anywhere a URL can reach, inside the repo (Laravel: public/) - the repo root
 * is found by walking up to .git. React served from a build folder: put it outside that folder
 * and set 'repo' + 'publish' below, because a build wipes its output folder.
 *
 * Secret, first one found (one line, 20+ chars, never committed):
 *   'secret_file' from the config, ~/.git_deploy_secret_<repo folder name>, ~/.git_deploy_secret,
 *   <repo>/.git_deploy_secret. One secret per project is safer: a leak then opens one project.
 *   A secret inside the web root needs a deny rule in .htaccess next to it, otherwise the script
 *   refuses to run (on Nginx keep it in the home folder - .htaccess does nothing there).
 *
 * Steps found from the target commit, for each folder in 'dirs':
 *   composer.json + vendor not committed  composer install (when composer files changed or vendor missing)
 *   package.json with a "build" script    npm / yarn / pnpm install (when package files changed or
 *                                         node_modules missing), then build (when anything changed)
 *   artisan                               down, migrate, storage:link, optimize, queue:restart, up
 * Plain PHP projects have no steps. Steps run only when the commit changed since the last good
 * deploy, so a failed step runs again on the next try.
 *
 * Optional git_deploy.config.php next to this script or in the repo root (safe in a web root, it
 * prints nothing). A config in the repo takes effect from the deploy after the one that adds it.
 *
 *   <?php return [
 *       'repo'        => '/home/me/repos/app',   // default: walk up from this script to .git
 *       'branch'      => 'production',           // default: main if origin has it, else current
 *       'dirs'        => ['backend', 'frontend'],// default: ['.'] - folders holding the projects
 *       'migrate'     => false,                  // default: true - Laravel migrate --force
 *       'steps'       => ['make deploy'],        // replaces every automatic step (cwd: repo root)
 *       'after'       => ['pm2 reload app'],     // extra commands after the steps
 *       'publish'     => [['from' => 'dist', 'to' => '/home/me/public_html']], // copy build output
 *       'path'        => '/opt/node/bin',        // prepended to PATH (web PHP has a short PATH)
 *       'php'         => 'php',                  // also: 'composer', 'npm', 'yarn', 'pnpm'
 *       'secret_file' => '/home/me/.app_secret',
 *   ];
 *
 * Why the backup/restore dance: per-server files (includes/config.ini etc.)
 * used to be tracked. Syncing to a commit that untracks them makes git delete
 * them. Every file git is about to delete or overwrite (including untracked
 * FTP uploads the repo now tracks) is copied to
 * ~/git_deploy_backups/<project>/<timestamp>/ first, and files that are now gitignored
 * (i.e. per-server) are copied straight back after the sync.
 */

header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');
@set_time_limit(1800);
// Never stop half-way through a sync because the caller hung up
ignore_user_abort(true);

function load_config(string $file): array
{
    $config = is_file($file) ? require $file : [];
    return is_array($config) ? $config : [];
}

// Walks up from this script to the folder holding .git (a file, in a worktree)
function find_repo(string $dir): ?string
{
    while (true) {
        if (file_exists($dir . '/.git')) {
            return $dir;
        }
        $parent = dirname($dir);
        if ($parent === $dir) {
            return null;
        }
        $dir = $parent;
    }
}

$config = load_config(__DIR__ . '/git_deploy.config.php');
$repo = rtrim((string) ($config['repo'] ?? find_repo(__DIR__) ?? ''), '/\\');
if ($repo === '' || !is_dir($repo) || !file_exists($repo . '/.git')) {
    http_response_code(503);
    exit("Deploy disabled: no git checkout found above this script. Set 'repo' in git_deploy.config.php.\n");
}
if ($repo !== __DIR__) {
    $config += load_config($repo . '/git_deploy.config.php'); // the one next to this script wins
}

// Account home (/home/<user>), wherever the docroot sits under it
$home = function_exists('posix_getpwuid') ? (posix_getpwuid(posix_geteuid())['dir'] ?? '') : '';
if ($home === '' && preg_match('#^(/home\d*/[^/]+)/#', $repo . '/', $m)) {
    $home = $m[1];
}
if ($home === '') {
    $home = dirname(__DIR__, 2);
}

putenv('HOME=' . $home);
chdir($repo);

// Web PHP has a short PATH. The running PHP's own folder comes first, so `php` and composer
// use this PHP version and not the system default (PHP_BINARY can be php-fpm / lsphp).
$pathParts = [(string) getenv('PATH')];
if (is_file(PHP_BINDIR . '/php') || is_file(PHP_BINDIR . '/php.exe')) {
    array_unshift($pathParts, PHP_BINDIR);
}
if (!empty($config['path'])) {
    array_unshift($pathParts, (string) $config['path']);
}
putenv('PATH=' . implode(PATH_SEPARATOR, $pathParts));

// Secret, this project's own first. Home is outside the web root; the repo one needs .htaccess
$secret = '';
$secretFiles = array_filter([
    $config['secret_file'] ?? null,
    $home . '/.git_deploy_secret_' . basename($repo),
    $home . '/.git_deploy_secret',
    $repo . '/.git_deploy_secret',
]);
foreach ($secretFiles as $secretFile) {
    if (is_readable($secretFile)) {
        $secret = trim((string) file_get_contents($secretFile));
        break;
    }
}
if (strlen($secret) < 20) {
    http_response_code(503);
    exit("Deploy disabled: secret file missing, unreadable or under 20 chars.\n"
        . "Looked for:\n  " . implode("\n  ", $secretFiles) . "\n");
}
$docRoot = realpath((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));
$secretPath = realpath($secretFile);
if ($docRoot && $secretPath && strpos($secretPath, $docRoot . DIRECTORY_SEPARATOR) === 0) {
    $htaccess = dirname($secretPath) . '/.htaccess';
    if (!is_readable($htaccess) || strpos((string) file_get_contents($htaccess), 'git_deploy_secret') === false) {
        http_response_code(503);
        exit("Deploy disabled: the secret file is inside the web root with no .htaccess rule denying it.\n"
            . "Anyone could download it. Move it to $home or add the deny rule.\n");
    }
}

// Default branch: main if origin has it, otherwise whatever this checkout is on
function git_run(string $args, ?int &$rc = null): string
{
    $out = [];
    exec('git ' . $args . ' 2>&1', $out, $rc);
    return implode("\n", $out);
}

$branch = (string) ($config['branch'] ?? '');
if ($branch === '') {
    git_run('rev-parse --verify --quiet refs/remotes/origin/main', $hasMain);
    $current = trim(git_run('rev-parse --abbrev-ref HEAD'));
    $branch = ($hasMain === 0 || $current === 'HEAD' || $current === '') ? 'main' : $current;
}
if (!preg_match('#^[\w./-]+$#', $branch)) {
    http_response_code(503);
    exit("Deploy disabled: bad branch name in the config.\n");
}

// ---- Auth: GitHub webhook signature, or manual key ----
$isWebhook = $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_HUB_SIGNATURE_256']);
$force = false;
if ($isWebhook) {
    $body = (string) file_get_contents('php://input');
    $expected = 'sha256=' . hash_hmac('sha256', $body, $secret);
    if (!hash_equals($expected, (string) $_SERVER['HTTP_X_HUB_SIGNATURE_256'])) {
        http_response_code(403);
        exit("Bad signature.\n");
    }
    $event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '';
    if ($event === 'ping') {
        exit("pong\n");
    }
    // Webhook content type may be application/json or form-encoded (payload=<json>)
    $payload = json_decode($body, true);
    if (!is_array($payload) && isset($_POST['payload'])) {
        $payload = json_decode((string) $_POST['payload'], true);
    }
    if ($event !== 'push' || ($payload['ref'] ?? '') !== "refs/heads/$branch") {
        exit("Ignored: not a push to $branch.\n");
    }
    $action = 'deploy';
} else {
    if (!hash_equals($secret, (string) ($_GET['key'] ?? ''))) {
        http_response_code(403);
        exit("Forbidden.\n");
    }
    $action = $_GET['action'] ?? 'status';
    $force = !empty($_GET['force']);
}

function git_lines(string $args): array
{
    $text = git_run($args, $rc);
    return $rc === 0 && $text !== '' ? explode("\n", $text) : [];
}

// Backups, log and last-good marker per project, so projects on one account never mix
$project = preg_replace('/[^\w.-]/', '_', basename($repo)) . '_' . substr(md5($repo), 0, 6);
$stateDir = $home . '/git_deploy_backups/' . $project;
$logFile = $stateDir . '/last_deploy.log';
$goodFile = $stateDir . '/last_good_commit';
@mkdir($stateDir, 0700, true);

if ($isWebhook) {
    // GitHub gives up after 10s - answer now, sync after the connection closes, output goes to the log
    echo "Accepted: deploying in background. Log: $logFile\n";
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    } elseif (function_exists('litespeed_finish_request')) {
        litespeed_finish_request();
    } else {
        flush();
    }
} elseif (is_readable($logFile)) {
    echo "==== Last deploy ====\n" . file_get_contents($logFile) . "\n==== Current state ====\n";
}

// One deploy at a time - webhook pushes queue behind a running sync, manual calls bail out
$lock = fopen(sys_get_temp_dir() . '/git_deploy_' . md5($repo) . '.lock', 'c');
if (!$lock || !flock($lock, $isWebhook ? LOCK_EX : LOCK_EX | LOCK_NB)) {
    @http_response_code(409);
    exit("Another deploy is running.\n");
}

// Every deploy goes to the log too: a long build outlasts proxy timeouts (Cloudflare: 100s)
if ($action === 'deploy') {
    file_put_contents($logFile, date('Y-m-d H:i:s') . "\n");
    ob_start(function ($chunk) use ($logFile, $isWebhook) {
        file_put_contents($logFile, $chunk, FILE_APPEND);
        return $isWebhook ? '' : $chunk;
    }, 1);
}

$fetch = git_run("fetch --prune origin $branch", $rc);
if ($rc !== 0) {
    @http_response_code(500);
    exit("git fetch failed:\n$fetch\n");
}

$head   = trim(git_run('rev-parse --short HEAD'));
$target = trim(git_run("rev-parse --short origin/$branch"));
$targetFull = trim(git_run("rev-parse origin/$branch"));

// Tracked files git will delete (in HEAD, gone from target) or overwrite (edited on server)
$toDelete = git_lines("diff --name-only --diff-filter=D HEAD origin/$branch");
$modified = git_lines('diff --name-only HEAD');
$untracked = git_lines('ls-files --others --exclude-standard');
// Untracked files on the server (e.g. uploaded by FTP) that the target now tracks get replaced
$targetFiles = git_lines("ls-tree -r --name-only origin/$branch");
$replaced = array_values(array_intersect($untracked, $targetFiles));

// ---- Steps: decided from the target commit, against the last deploy that fully worked ----
$lastGood = is_readable($goodFile) ? trim((string) file_get_contents($goodFile)) : '';
$runAll = $force || !preg_match('/^[0-9a-f]{7,64}$/', $lastGood);
if (!$runAll) {
    git_run('cat-file -e ' . escapeshellarg("$lastGood^{commit}"), $goodExists);
    $runAll = $goodExists !== 0;
}
$changed = $runAll ? [] : git_lines("diff --name-only $lastGood origin/$branch");

/** Files of the target commit changed under $prefix; every file when everything must run. */
function changed_in(string $prefix, array $names = []): bool
{
    global $changed, $runAll;
    if ($runAll) {
        return true;
    }
    foreach ($changed as $file) {
        if ($prefix !== '' && strpos($file, $prefix) !== 0) {
            continue;
        }
        if (!$names || in_array(substr($file, strlen($prefix)), $names, true)) {
            return true;
        }
    }
    return false;
}

/** Each step: [folder, command, must it work?]. Laravel folders also come back to go down / up. */
function auto_steps(string $repo, array $config, array $targetFiles, string $branch): array
{
    $php = (string) ($config['php'] ?? 'php');
    $steps = [];
    $laravel = [];
    foreach ((array) ($config['dirs'] ?? ['.']) as $dir) {
        $prefix = ($dir === '.' || $dir === '') ? '' : rtrim(str_replace('\\', '/', $dir), '/') . '/';
        $cwd = $prefix === '' ? $repo : $repo . '/' . rtrim($prefix, '/');
        $has = function ($file) use ($targetFiles, $prefix) {
            return in_array($prefix . $file, $targetFiles, true);
        };

        // Composer, unless vendor is committed (then the repo already carries it)
        if ($has('composer.json') && !$has('vendor/autoload.php')
            && (changed_in($prefix, ['composer.json', 'composer.lock']) || !is_file("$cwd/vendor/autoload.php"))) {
            $composer = (string) ($config['composer'] ?? 'composer');
            $steps[] = [$cwd, "$composer install --no-dev --optimize-autoloader --no-interaction", true];
        }

        // Front-end build
        if ($has('package.json')) {
            $package = json_decode(git_run('show ' . escapeshellarg("origin/$branch:{$prefix}package.json")), true);
            if (isset($package['scripts']['build'])) {
                if ($has('pnpm-lock.yaml')) {
                    $tool = (string) ($config['pnpm'] ?? 'pnpm');
                    $install = "$tool install --frozen-lockfile --prod=false";
                    $lockName = 'pnpm-lock.yaml';
                } elseif ($has('yarn.lock')) {
                    $tool = (string) ($config['yarn'] ?? 'yarn');
                    $install = "$tool install --frozen-lockfile --production=false";
                    $lockName = 'yarn.lock';
                } else {
                    $tool = (string) ($config['npm'] ?? 'npm');
                    // --include=dev: the build tools are dev packages, even under NODE_ENV=production
                    $install = $tool . ($has('package-lock.json') ? ' ci' : ' install') . ' --include=dev';
                    $lockName = 'package-lock.json';
                }
                $hasPackages = !empty($package['dependencies']) || !empty($package['devDependencies']);
                if (changed_in($prefix, ['package.json', $lockName]) || ($hasPackages && !is_dir("$cwd/node_modules"))) {
                    $steps[] = [$cwd, $install, true];
                }
                if (changed_in($prefix)) {
                    $steps[] = [$cwd, "$tool run build", true];
                }
            }
        }

        // Laravel
        if ($has('artisan') && changed_in($prefix)) {
            $laravel[] = $cwd;
            if (($config['migrate'] ?? true) !== false) {
                $steps[] = [$cwd, "$php artisan migrate --force", true];
            }
            if (!file_exists("$cwd/public/storage")) {
                $steps[] = [$cwd, "$php artisan storage:link", false]; // some hosts forbid symlinks
            }
            $steps[] = [$cwd, "$php artisan optimize", true];
            $steps[] = [$cwd, "$php artisan queue:restart", false];
        }
    }
    return [$steps, $laravel];
}

if (isset($config['steps'])) {
    $steps = $runAll || $changed ? array_map(function ($cmd) use ($repo) { return [$repo, (string) $cmd, true]; }, (array) $config['steps']) : [];
    $laravelDirs = [];
} else {
    [$steps, $laravelDirs] = auto_steps($repo, $config, $targetFiles, $branch);
}
$anyChange = $runAll || $changed;
$publish = $anyChange ? (array) ($config['publish'] ?? []) : [];
if ($anyChange) {
    foreach ((array) ($config['after'] ?? []) as $cmd) {
        $steps[] = [$repo, (string) $cmd, true];
    }
}

echo "Repo:    $repo\n";
echo "HEAD:    $head\n";
echo "Target:  $target (origin/$branch)\n";
echo "Behind:  " . trim(git_run("rev-list --count HEAD..origin/$branch")) . " commits\n\n";

echo "Edited on server, will be overwritten (" . count($modified) . "):\n";
foreach (array_slice($modified, 0, 100) as $f) echo "  M $f\n";
if (count($modified) > 100) echo "  ...\n";

echo "\nTracked here, removed in target (" . count($toDelete) . "):\n";
foreach ($toDelete as $f) echo "  D $f\n";

echo "\nUntracked on server, replaced by repo version (" . count($replaced) . "):\n";
foreach (array_slice($replaced, 0, 100) as $f) echo "  R $f\n";
if (count($replaced) > 100) echo "  ...\n";

echo "\nUntracked on server, left alone: " . (count($untracked) - count($replaced)) . " files\n";

echo "\nSteps after the sync (" . count($steps) . "):\n";
foreach ($laravelDirs as $dir) echo "  [$dir] maintenance mode on during the deploy\n";
foreach ($steps as [$cwd, $cmd]) echo "  [$cwd] $cmd\n";
foreach ($publish as $p) echo "  copy {$p['from']} to {$p['to']}\n";

if ($action !== 'deploy') {
    exit("\nPreview only. Add &action=deploy to sync.\n");
}

/** Runs one command in a folder. Prints the end of its output, returns its exit code. */
function run_step(string $cwd, string $cmd): int
{
    global $repo;
    echo "\n\$ [$cwd] $cmd\n";
    if (!is_dir($cwd)) {
        echo "Folder not found.\n";
        return 1;
    }
    $tool = strtok($cmd, ' ');
    $check = PHP_OS_FAMILY === 'Windows' ? 'where ' : 'command -v ';
    exec($check . escapeshellarg($tool) . ' 2>&1', $ignored, $found);
    if ($found !== 0 && !is_file($cwd . '/' . $tool)) {
        echo "'$tool' not found on this server. Install it, or set 'path' / '$tool' in git_deploy.config.php.\n";
        return 127;
    }
    chdir($cwd);
    $out = [];
    exec($cmd . ' 2>&1', $out, $rc);
    chdir($repo);
    echo implode("\n", array_slice($out, -40)) . "\n";
    return $rc;
}

/** Copies a build output over a folder. Never deletes, so this script and uploads there stay. */
function copy_tree(string $from, string $to): bool
{
    if (!is_dir($to) && !@mkdir($to, 0755, true)) {
        return false;
    }
    $ok = true;
    foreach (scandir($from) as $name) {
        if ($name === '.' || $name === '..') {
            continue;
        }
        $ok = (is_dir("$from/$name") ? copy_tree("$from/$name", "$to/$name") : copy("$from/$name", "$to/$name")) && $ok;
    }
    return $ok;
}

$php = (string) ($config['php'] ?? 'php');
$wentDown = [];
foreach ($laravelDirs as $dir) {
    if (is_file("$dir/artisan") && is_file("$dir/vendor/autoload.php") && run_step($dir, "$php artisan down --retry=30") === 0) {
        $wentDown[] = $dir;
    }
}

/** Stops the deploy after the sync started. The site always comes back up. */
function deploy_failed(string $what): void
{
    global $wentDown, $php;
    foreach ($wentDown as $dir) {
        run_step($dir, "$php artisan up");
    }
    @http_response_code(500);
    exit("\nDEPLOY FAILED: $what\nDeploy again after fixing it: the steps run again.\n");
}

// ---- Back up everything that is about to change ----
$backupDir = $stateDir . '/' . date('Ymd_His') . "_$head";
$saved = [];
foreach (array_unique(array_merge($toDelete, $modified, $replaced)) as $f) {
    if (!is_file("$repo/$f")) continue;
    $dest = "$backupDir/$f";
    @mkdir(dirname($dest), 0700, true);
    if (copy("$repo/$f", $dest)) $saved[] = $f;
}
echo "\nBacked up " . count($saved) . " files to $backupDir\n";

$reset = git_run("reset --hard origin/$branch", $rc);
echo "\n$reset\n";
if ($rc !== 0) {
    deploy_failed("git reset failed - nothing restored, backup is in $backupDir");
}

// Per-server files (now gitignored) go straight back
foreach ($toDelete as $f) {
    if (!in_array($f, $saved, true)) continue;
    git_run('check-ignore -q ' . escapeshellarg($f), $ignored);
    if ($ignored === 0 && !file_exists("$repo/$f")) {
        @mkdir(dirname("$repo/$f"), 0755, true);
        copy("$backupDir/$f", "$repo/$f");
        echo "Restored per-server file: $f\n";
    }
}

// ---- Project steps ----
foreach ($steps as [$cwd, $cmd, $required]) {
    $code = run_step($cwd, $cmd);
    if ($code !== 0) {
        if ($required) {
            deploy_failed("$cmd (exit code $code). The new code is in place, but this step did not finish.");
        }
        echo "(not required, carrying on)\n";
    }
}
foreach ($publish as $p) {
    $from = $repo . '/' . ltrim((string) $p['from'], '/\\');
    if (!is_dir($from) || !copy_tree($from, (string) $p['to'])) {
        deploy_failed("could not copy {$p['from']} to {$p['to']}");
    }
    echo "\nCopied {$p['from']} to {$p['to']}\n";
}
foreach ($wentDown as $dir) {
    run_step($dir, "$php artisan up");
}

file_put_contents($goodFile, $targetFull);
echo "\nDeployed " . trim(git_run('log -1 --format="%h %s"')) . "\n";
