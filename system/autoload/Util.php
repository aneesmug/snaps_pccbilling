<?php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Util
{
    public static function parse_size($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }

        return round($size);
    }

    public static function file_upload_max_size()
    {
        static $max_size = -1;

        if ($max_size < 0) {
            // Start with post_max_size.
            $post_max_size = self::parse_size(ini_get('post_max_size'));
            if ($post_max_size > 0) {
                $max_size = $post_max_size;
            }

            // If upload_max_size is less, then reduce. Except if upload_max_size is
            // zero, which indicates no limit.
            $upload_max = self::parse_size(ini_get('upload_max_filesize'));
            if ($upload_max > 0 && $upload_max < $max_size) {
                $max_size = $upload_max;
            }
        }
        return $max_size;
    }

    /**
     * @return array
     */
    public static function systemInfo()
    {
        global $config;

        if (class_exists('ZipArchive')) {
            $zip_info = '✅';
        } else {
            $zip_info =
                '❌ php ZipArchive class does not exist, backup and automatic updates will not work';
        }

        $writable = is_writable('./') ? '✅' : '❌ System can not write file in the disk.';

        $mbstring = extension_loaded('mbstring') ? '✅' : '❌ mbstring is not enabled, PDF will not work.';

        $gd = extension_loaded('gd') ? '✅' : '❌ mbstring is not enabled, PDF will not work.';

        $curl = function_exists('curl_version') ? '✅' : '❌ curl is not enabled. api based service will not work.';

        return [
            'app_name' => $config['CompanyName'],
            'app_build' => $config['build'],
            'php_version' => phpversion(),
            'os' => PHP_OS,
            'modules' => [
                'zip' => $zip_info,
                'mbstring' => $mbstring,
                'gd' => $gd,
                'curl' => $curl,
            ],
            'write_permission' => $writable,
            'file_public_key' => $config['file_public_key'],
        ];
    }

    /**
     * @param $taskId
     * @return array
     */
    public static function backupFiles($taskId = false)
    {
        $logger = false;

        try {
            $backup_path = $taskId ? 'tmp/' . $taskId : 'storage/backups/app';

            if (!file_exists($backup_path)) {
                mkdir($backup_path);
                $info = 'Backup folder created: ' . $taskId;
            } else {
                $info = 'Existing backup folder found: ' . $taskId;
            }

            $logger = new Logger('clx');
            $logger->pushHandler(
                new StreamHandler($backup_path . '/task.log', Logger::DEBUG)
            );

            $logger->info($info);

            $logger->info('storage folder...');
            ExtendedZip::zipTree(
                'storage',
                $backup_path . '/storage.zip',
                ZipArchive::CREATE
            );

            $logger->info('system folder...');
            ExtendedZip::zipTree(
                'system',
                $backup_path . '/system.zip',
                ZipArchive::CREATE
            );

            $logger->info('ui folder...');
            ExtendedZip::zipTree(
                'ui',
                $backup_path . '/ui.zip',
                ZipArchive::CREATE
            );

            $logger->info('=============== Done !');

            return [
                'success' => true,
                'message' => 'Files backup completed',
            ];
        } catch (Exception $e) {
            if ($logger) {
                $logger->error($e->getMessage());
            }
            return [
                'success' => false,
                'errors' => [
                    'message' => $e->getMessage(),
                ],
            ];
        }
    }

    /**
     * @param $taskId
     * @return array
     */
    public static function backupDatabase($taskId = false)
    {


// Get connection object and set the charset
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $conn->set_charset("utf8");


// Get All Table Names From the Database
        $tables = [];
        $sql = "SHOW TABLES";
        $result = mysqli_query($conn, $sql);

        while ($row = mysqli_fetch_row($result)) {
            $tables[] = $row[0];
        }

        $sqlScript = "";
        foreach ($tables as $table) {

            // Prepare SQLscript for creating table structure
            $query = "SHOW CREATE TABLE $table";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_row($result);

            $sqlScript .= "\n\n" . $row[1] . ";\n\n";


            $query = "SELECT * FROM $table";
            $result = mysqli_query($conn, $query);

            $columnCount = mysqli_num_fields($result);

            // Prepare SQLscript for dumping data for each table
            for ($i = 0; $i < $columnCount; $i ++) {
                while ($row = mysqli_fetch_row($result)) {
                    $sqlScript .= "INSERT INTO $table VALUES(";
                    for ($j = 0; $j < $columnCount; $j ++) {
                        $row[$j] = $row[$j];

                        if (isset($row[$j])) {
                            $sqlScript .= '"' . $row[$j] . '"';
                        } else {
                            $sqlScript .= '""';
                        }
                        if ($j < ($columnCount - 1)) {
                            $sqlScript .= ',';
                        }
                    }
                    $sqlScript .= ");\n";
                }
            }

            $sqlScript .= "\n";
        }

        $backup_name = 'db_backup_'.'_'.\Illuminate\Support\Str::random().'_' . date('Y-m-d_H-i-s') . '.sql';
        $path = 'storage/backups/db/' . $backup_name;

        ($myfile = fopen($path, "w")) || die("Unable to open file!");

        fwrite($myfile, $sqlScript);
        fclose($myfile);


    }

    /**
     * @param $taskId
     * @return array
     */
    public static function getTaskLog($taskId)
    {
        $log_path = 'storage/' . $taskId . '/task.log';
        if (file_exists($log_path)) {
            return [
                'success' => true,
                'contents' => file_get_contents($log_path),
            ];
        }

        return [
            'success' => false,
        ];
    }

    /**
     * @return array
     */
    public static function fileUpload()
    {
        $uploader = new Uploader();
        $uploader->setDir('./');
        $uploader->sameName(true);
        $uploader->setExtensions(['zip']); //allowed extensions list//
        if ($uploader->uploadFile('file')) {
            //txtFile is the filebrowse element name //
            $uploaded = $uploader->getUploadName(); //get uploaded file name, renames on upload//
            return [
                'success' => true,
                'uploaded' => $uploaded,
            ];
        }

        //upload failed
        return [
            'success' => false,
            'errors' => [
                'file' => $uploader->getMessage(),
            ],
        ];
    }

    public static function extractFile($name, $deleteAfterUpload = true)
    {
        $msg = '';
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();

            $res = $zip->open('./' . $name);
            if ($res === true) {
                $zip->extractTo('./');

                if ($deleteAfterUpload && $zip->close()) {
                    unlink('./' . $name);
                }
                //
            } else {
                $msg .=
                    $name .
                    ' - Invalid archive Or An error occured while unzipping the file! ';
            }
        } else {
            $msg .= 'PHP ZipArchive Class is not Available! ';
        }

        if ($msg !== '') {
            return [
                'success' => false,
                'errors' => [
                    'zip' => $msg,
                ],
            ];
        }

        return [
            'success' => true,
        ];
    }

    public static function downloadFile($file_url, $file_name)
    {
        $h = new IBilling_Http();

        try {
            $r = $h
                ->open($file_url)
                ->setFileName($file_name)
                ->then('download');

            return [
                'success' => true,
                'message' => 'File copied from remote! - ' . $file_name,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
