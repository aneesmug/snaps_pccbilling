<?php

// IDE-only stubs for static analysis (not loaded by runtime bootstrap).
if (!function_exists('authenticate_admin')) {
    /**
     * @return mixed
     */
    function authenticate_admin()
    {
        return null;
    }
}

if (!function_exists('access_denied')) {
    /**
     * @return void
     */
    function access_denied()
    {
    }
}

if (!function_exists('error_404')) {
    /**
     * @return void
     */
    function error_404()
    {
    }
}

if (!class_exists('IntelephenseRequestStub')) {
    class IntelephenseRequestStub
    {
        /**
         * @return array<string, mixed>
         */
        public function all()
        {
            return [];
        }

        /**
         * @param string $key
         * @param mixed $default
         * @return mixed
         */
        public function input($key, $default = null)
        {
            return $default;
        }

        /**
         * @return string
         */
        public function ip()
        {
            return '127.0.0.1';
        }

        /**
         * @return string
         */
        public function userAgent()
        {
            return 'Intelephense';
        }
    }
}

if (!class_exists('IntelephenseAuthStub')) {
    class IntelephenseAuthStub
    {
        /**
         * @return int
         */
        public function id()
        {
            return 0;
        }
    }
}

if (!class_exists('IntelephenseNowStub')) {
    class IntelephenseNowStub
    {
        /**
         * @param string $format
         * @return string
         */
        public function format($format)
        {
            return date($format);
        }

        /**
         * @param mixed $date
         * @return int
         */
        public function diffInDays($date)
        {
            return 0;
        }
    }
}

if (!class_exists('IntelephenseResponseFactoryStub')) {
    class IntelephenseResponseFactoryStub
    {
        /**
         * @param mixed $data
         * @param int $status
         * @return array<string, mixed>
         */
        public function json($data = [], $status = 200)
        {
            return [
                'status' => $status,
                'data' => $data,
            ];
        }
    }
}

if (!class_exists('Controller')) {
    class Controller
    {
    }
}

if (!class_exists('Log')) {
    class Log
    {
        /**
         * @param mixed $message
         * @return void
         */
        public static function info($message)
        {
        }

        /**
         * @param mixed $message
         * @return void
         */
        public static function error($message)
        {
        }
    }
}

if (!function_exists('request')) {
    /**
     * @return IntelephenseRequestStub
     */
    function request()
    {
        return new IntelephenseRequestStub();
    }
}

if (!function_exists('response')) {
    /**
     * @return IntelephenseResponseFactoryStub
     */
    function response()
    {
        return new IntelephenseResponseFactoryStub();
    }
}

if (!function_exists('app')) {
    /**
     * @param string|null $name
     * @return mixed
     */
    function app($name = null)
    {
        return null;
    }
}

if (!function_exists('auth')) {
    /**
     * @return IntelephenseAuthStub
     */
    function auth()
    {
        return new IntelephenseAuthStub();
    }
}

if (!function_exists('now')) {
    /**
     * @return IntelephenseNowStub
     */
    function now()
    {
        return new IntelephenseNowStub();
    }
}

if (!class_exists('UserPreference')) {
    class UserPreference
    {
        /**
         * @param int|string $userId
         * @param string $key
         * @param mixed $value
         * @return bool
         */
        public static function setPreference($userId, $key, $value)
        {
            return true;
        }
    }
}

if (!function_exists('appFlashMessage')) {
    /**
     * @param string $message
     * @param string $type
     * @return void
     */
    function appFlashMessage($message, $type = 'success')
    {
    }
}

if (!function_exists('redirect_to')) {
    /**
     * @param string $path
     * @return void
     */
    function redirect_to($path)
    {
    }
}

if (!function_exists('redirect_back')) {
    /**
     * @return void
     */
    function redirect_back()
    {
    }
}

if (!defined('APP_STAGE')) {
    define('APP_STAGE', 'Live');
}

if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}

if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}

if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', '');
}

if (!defined('DB_NAME')) {
    define('DB_NAME', 'ibilling');
}

if (!defined('APP_SYSTEM_PATH')) {
    define('APP_SYSTEM_PATH', 'system');
}

if (!function_exists('__')) {
    /**
     * @param string $text
     * @return string
     */
    function __($text)
    {
        return (string) $text;
    }
}

if (!function_exists('appArrayFindById')) {
    /**
     * @param array<int, mixed> $items
     * @param mixed $id
     * @return mixed|null
     */
    function appArrayFindById($items, $id)
    {
        return null;
    }
}

if (!function_exists('create_alert_message')) {
    /**
     * @param string $message
     * @return string
     */
    function create_alert_message($message)
    {
        return (string) $message;
    }
}

if (!function_exists('sp_purify_data')) {
    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    function sp_purify_data($data)
    {
        return is_array($data) ? $data : [];
    }
}

if (!function_exists('response_with_error_message')) {
    /**
     * @param mixed $errors
     * @return string
     */
    function response_with_error_message($errors)
    {
        return '';
    }
}

if (!function_exists('updateOption')) {
    /**
     * @param string $key
     * @param mixed $value
     * @param bool $autoload
     * @return bool
     */
    function updateOption($key, $value, $autoload = false)
    {
        return true;
    }
}

if (!function_exists('removeOption')) {
    /**
     * @param string $key
     * @return bool
     */
    function removeOption($key)
    {
        return true;
    }
}

if (!function_exists('remove_option')) {
    /**
     * @param string $key
     * @return bool
     */
    function remove_option($key)
    {
        return true;
    }
}

if (!function_exists('updateCheck')) {
    /**
     * @param string $purchaseKey
     * @return array<string, mixed>
     */
    function updateCheck($purchaseKey)
    {
        return [];
    }
}

if (!function_exists('homeCurrency')) {
    /**
     * @return string
     */
    function homeCurrency()
    {
        return 'USD';
    }
}

if (!function_exists('responseWithError')) {
    /**
     * @param string $message
     * @return void
     */
    function responseWithError($message)
    {
    }
}

if (!function_exists('ray')) {
    /**
     * @param mixed ...$arguments
     * @return void
     */
    function ray(...$arguments)
    {
    }
}

if (!function_exists('abort')) {
    /**
     * @param int $code
     * @return void
     */
    function abort($code)
    {
    }
}

if (!class_exists('IntelephenseModelStub')) {
    /**
     * @property mixed $id
     * @property mixed $rname
     * @property mixed $name
     * @property mixed $type
     * @property mixed $source_id
     * @property mixed $target_id
     * @property mixed $tpl
     * @property mixed $sms
     * @property mixed $method
     * @property mixed $username
     * @property mixed $password
     * @property mixed $host
     * @property mixed $port
     * @property mixed $secure
     */
    class IntelephenseModelStub
    {
        /** @return static */
        public static function where($column = null, $operator = null, $value = null)
        {
            return new static();
        }

        /** @return static */
        public static function orderBy($column = null, $direction = 'asc')
        {
            return new static();
        }

        /** @return static */
        public static function find($id = null)
        {
            return new static();
        }

        /** @return static */
        public static function first()
        {
            return new static();
        }

        /** @return IntelephenseCollectionStub */
        public static function all()
        {
            return new IntelephenseCollectionStub();
        }

        /** @return IntelephenseCollectionStub */
        public function get()
        {
            return new IntelephenseCollectionStub();
        }

        /** @return static */
        public function keyBy($key)
        {
            return $this;
        }

        /** @return array<int|string, mixed> */
        public function allItems()
        {
            return [];
        }

        /** @return array<int, mixed> */
        public function allArray()
        {
            return [];
        }

        /** @return mixed */
        public function __call($name, $arguments)
        {
            if ($name === 'all') {
                return new IntelephenseCollectionStub();
            }

            return $this;
        }

        /** @return mixed */
        public function __get($name)
        {
            return null;
        }

        /** @param mixed $value */
        public function __set($name, $value)
        {
        }

        /** @return mixed */
        public static function __callStatic($name, $arguments)
        {
            return new static();
        }
    }
}

if (!class_exists('IntelephenseCollectionStub')) {
    class IntelephenseCollectionStub
    {
        /** @return $this */
        public function keyBy($key)
        {
            return $this;
        }

        /** @return array<int|string, mixed> */
        public function all()
        {
            return [];
        }

        /** @return int */
        public function count()
        {
            return 0;
        }

        /** @return mixed */
        public function __call($name, $arguments)
        {
            if ($name === 'all') {
                return [];
            }

            return $this;
        }
    }
}

if (!class_exists('Status')) {
    class Status extends IntelephenseModelStub
    {
    }
}

if (!class_exists('Relation')) {
    class Relation extends IntelephenseModelStub
    {
        /** @return array<int, mixed> */
        public static function staffDepartmentsAll()
        {
            return [];
        }
    }
}

if (!class_exists('TicketDepartment')) {
    class TicketDepartment extends IntelephenseModelStub
    {
    }
}

if (!class_exists('Role')) {
    class Role extends IntelephenseModelStub
    {
    }
}

if (!class_exists('Localization')) {
    class Localization
    {
        /** @return array<int, mixed> */
        public static function getLanguages()
        {
            return [];
        }
    }
}

if (!class_exists('Validation')) {
    class Validation
    {
        /** @return static */
        public static function init()
        {
            return new static();
        }

        /** @return IntelephenseValidatorStub */
        public function make($data, $rules)
        {
            return new IntelephenseValidatorStub();
        }

        /** @return IntelephenseValidatorStub */
        public function validate($data, $rules)
        {
            return new IntelephenseValidatorStub();
        }
    }
}

if (!class_exists('Validator')) {
    class Validator
    {
        /** @return IntelephenseValidatorStub */
        public function validate($data, $rules)
        {
            return new IntelephenseValidatorStub();
        }
    }
}

if (!class_exists('IntelephenseValidatorStub')) {
    class IntelephenseValidatorStub
    {
        /** @return bool */
        public function fails()
        {
            return false;
        }

        /** @return IntelephenseValidationErrorsStub */
        public function errors()
        {
            return new IntelephenseValidationErrorsStub();
        }
    }
}

if (!class_exists('IntelephenseValidationErrorsStub')) {
    class IntelephenseValidationErrorsStub
    {
        /** @return array<int, string> */
        public function all()
        {
            return [];
        }
    }
}

if (!class_exists('EmailConfig')) {
    class EmailConfig extends IntelephenseModelStub
    {
    }
}

if (!class_exists('InvoiceGroup')) {
    class InvoiceGroup extends IntelephenseModelStub
    {
    }
}

if (!class_exists('SMSTemplate')) {
    class SMSTemplate extends IntelephenseModelStub
    {
    }
}

if (!class_exists('Permission')) {
    class Permission extends IntelephenseModelStub
    {
    }
}

if (!class_exists('Filesystem')) {
    class Filesystem
    {
        /** @return bool */
        public function delete($path)
        {
            return true;
        }
    }
}

if (!class_exists('Image')) {
    class Image
    {
        /** @return IntelephenseImageStub */
        public static function make($path)
        {
            return new IntelephenseImageStub();
        }
    }
}

if (!class_exists('IntelephenseImageStub')) {
    class IntelephenseImageStub
    {
        /** @return $this */
        public function resize($width, $height)
        {
            return $this;
        }

        /** @return bool */
        public function save($path)
        {
            return true;
        }
    }
}

if (!class_exists('Countries')) {
    class Countries
    {
        /** @return string */
        public static function full2short($name)
        {
            return 'SA';
        }
    }
}

if (!class_exists('ExpenseType')) {
    class ExpenseType extends IntelephenseModelStub
    {
    }
}

if (!class_exists('Tax')) {
    class Tax extends IntelephenseModelStub
    {
    }
}

if (!class_exists('Customer')) {
    class Customer extends IntelephenseModelStub
    {
    }
}

if (!class_exists('Vendor')) {
    class Vendor extends IntelephenseModelStub
    {
    }
}

if (!class_exists('Widget')) {
    class Widget extends IntelephenseModelStub
    {
    }
}

if (!class_exists('Http')) {
    class Http extends IntelephenseModelStub
    {
    }
}

if (!class_exists('SemverComparator')) {
    class SemverComparator
    {
        /** @return bool */
        public static function greaterThan($left, $right)
        {
            return false;
        }
    }
}

if (!class_exists('Update')) {
    class Update
    {
        /** @return array<string, mixed> */
        public static function downloadTheLatestVersion($config, $manifest, $user)
        {
            return ['success' => false, 'message' => ''];
        }

        /** @return array<string, mixed> */
        public static function extractTheLatestVersion($config)
        {
            return ['success' => false, 'message' => ''];
        }

        public static function databaseSchema($config)
        {
        }

        public static function cleanup($config)
        {
        }
    }
}

if (!class_exists('PrivacyPolicy')) {
    class PrivacyPolicy extends IntelephenseModelStub
    {
    }
}

if (!class_exists('Terms')) {
    class Terms extends IntelephenseModelStub
    {
    }
}

if (!class_exists('CookiePolicy')) {
    class CookiePolicy extends IntelephenseModelStub
    {
    }
}

if (!class_exists('ContactSection')) {
    class ContactSection extends IntelephenseModelStub
    {
    }
}

if (!class_exists('LandingPage')) {
    class LandingPage extends IntelephenseModelStub
    {
    }
}

// ─── Global helper functions used by contacts.php and other controllers ───────

if (!function_exists('predict_next_serial')) {
    /**
     * @param array<string, mixed> $config
     * @param string $type
     * @return string
     */
    function predict_next_serial($config, $type)
    {
        return '';
    }
}

if (!function_exists('current_number_would_be')) {
    /**
     * @param string $code
     * @return string|int
     */
    function current_number_would_be($code)
    {
        return 0;
    }
}

if (!function_exists('getOwners')) {
    /**
     * @param mixed $user
     * @return array<int, mixed>
     */
    function getOwners($user)
    {
        return [];
    }
}

if (!function_exists('getJsonParams')) {
    /**
     * @return object
     */
    function getJsonParams()
    {
        return new stdClass();
    }
}

if (!function_exists('getContactFormattedAddress')) {
    /**
     * @param array<string, mixed> $config
     * @param mixed $contact
     * @param bool $html
     * @return string
     */
    function getContactFormattedAddress($config, $contact, $html = true)
    {
        return '';
    }
}

if (!function_exists('sp_get_contact_image')) {
    /**
     * @param mixed $contact
     * @param string $color
     * @return string
     */
    function sp_get_contact_image($contact, $color = '#3979FF')
    {
        return '';
    }
}

if (!function_exists('formatCurrency')) {
    /**
     * @param mixed $amount
     * @param mixed $currency
     * @return string
     */
    function formatCurrency($amount, $currency = null)
    {
        return (string) $amount;
    }
}

// ─── Global classes used by contacts.php and other controllers ────────────────

if (!class_exists('Misc')) {
    class Misc
    {
        /** @return string */
        public static function random_string($length = 12)
        {
            return '';
        }
    }
}

if (!class_exists('Email')) {
    class Email
    {
        /** @return void */
        public static function send_client_welcome_email(...$args)
        {
        }

        /** @return void */
        public static function sendEmail(...$args)
        {
        }

        /** @return void */
        public static function _log(...$args)
        {
        }
    }
}

if (!class_exists('Purchase')) {
    class Purchase extends IntelephenseModelStub
    {
        /** @return static */
        public function paid()
        {
            return $this;
        }

        /** @return static */
        public function unpaid()
        {
            return $this;
        }

        /** @return float */
        public function sum($column)
        {
            return 0.0;
        }
    }
}

if (!class_exists('Colors')) {
    class Colors
    {
        /** @return array<string, string> */
        public static function colorNames()
        {
            return [];
        }
    }
}

if (!class_exists('PasswordManager')) {
    class PasswordManager extends IntelephenseModelStub
    {
    }
}

if (!class_exists('CreditCard')) {
    class CreditCard extends IntelephenseModelStub
    {
    }
}

if (!class_exists('Document')) {
    class Document extends IntelephenseModelStub
    {
    }
}
