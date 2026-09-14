<?php

// IDE-only stubs for VS Code static analysis. This file is not loaded by the app bootstrap.

namespace {
    if (!defined('APP_BASE_PATH')) {
        define('APP_BASE_PATH', dirname(__DIR__));
    }

    if (!defined('APP_SYSTEM_PATH')) {
        define('APP_SYSTEM_PATH', __DIR__);
    }

    if (!class_exists('IntelephenseCollectionStub', false)) {
        class IntelephenseCollectionStub
        {
            /** @return $this */
            public function keyBy($key)
            {
                return $this;
            }

            /** @return array<int|string, mixed> */
            public function toArray()
            {
                return [];
            }

            /** @return int */
            public function count()
            {
                return 0;
            }

            /** @return mixed */
            public function first()
            {
                return null;
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

    if (!class_exists('IntelephenseModelStub', false)) {
        class IntelephenseModelStub
        {
            /** @return static */
            public static function where($column = null, $operator = null, $value = null)
            {
                return new static();
            }

            /** @return static */
            public static function select($columns = ['*'])
            {
                return new static();
            }

            /** @return static */
            public static function orderBy($column = null, $direction = 'asc')
            {
                return new static();
            }

            /** @return static */
            public static function limit($value = null)
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

            /** @return mixed */
            public function __call($name, $arguments)
            {
                if ($name === 'get') {
                    return new IntelephenseCollectionStub();
                }

                if ($name === 'first') {
                    return new static();
                }

                return $this;
            }

            /** @return mixed */
            public static function __callStatic($name, $arguments)
            {
                if ($name === 'all') {
                    return new IntelephenseCollectionStub();
                }

                return new static();
            }

            /** @return mixed */
            public function __get($name)
            {
                return null;
            }

            public function __set($name, $value)
            {
            }
        }
    }

    if (!class_exists('IntelephenseSchemaBuilderStub', false)) {
        class IntelephenseSchemaBuilderStub
        {
            /** @return bool */
            public function table($name, $callback)
            {
                return true;
            }

            /** @return bool */
            public function create($name, $callback)
            {
                return true;
            }
        }
    }
}

namespace Illuminate\Database\Eloquent {
    if (!class_exists(Model::class, false)) {
        class Model extends \IntelephenseModelStub
        {
        }
    }
}

namespace Illuminate\Database\Capsule {
    if (!class_exists(Manager::class, false)) {
        class Manager
        {
            /** @return bool */
            public static function statement($statement)
            {
                return true;
            }

            /** @return bool */
            public static function unprepared($sql)
            {
                return true;
            }

            /** @return \IntelephenseSchemaBuilderStub */
            public static function schema()
            {
                return new \IntelephenseSchemaBuilderStub();
            }
        }
    }
}

namespace Illuminate\Support {
    if (!class_exists(Str::class, false)) {
        class Str
        {
            /** @return string */
            public static function uuid()
            {
                return '00000000-0000-0000-0000-000000000000';
            }

            /** @return string */
            public static function random($length = 16)
            {
                return str_repeat('a', (int) $length);
            }
        }
    }
}

namespace Illuminate\Http\Client {
    if (!class_exists(Response::class, false)) {
        class Response
        {
        }
    }

    if (!class_exists(Factory::class, false)) {
        class Factory
        {
            /** @return $this */
            public function withOptions(array $options)
            {
                return $this;
            }

            /** @return $this */
            public function asForm()
            {
                return $this;
            }

            /** @return Response */
            public function post($url, array $data = [])
            {
                return new Response();
            }
        }
    }
}

namespace Composer\Semver {
    if (!class_exists(Comparator::class, false)) {
        class Comparator
        {
            /** @return bool */
            public static function lessThan($version1, $version2)
            {
                return version_compare((string) $version1, (string) $version2, '<');
            }
        }
    }
}