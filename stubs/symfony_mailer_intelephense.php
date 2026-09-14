<?php

namespace Symfony\Component\Mailer;

if (!class_exists('Symfony\\Component\\Mailer\\Transport')) {
    class Transport
    {
        /**
         * @param string $dsn
         * @return mixed
         */
        public static function fromDsn($dsn)
        {
            return null;
        }
    }
}

if (!class_exists('Symfony\\Component\\Mailer\\Mailer')) {
    class Mailer
    {
        /**
         * @param mixed $transport
         */
        public function __construct($transport)
        {
        }

        /**
         * @param mixed $message
         * @return void
         */
        public function send($message)
        {
        }
    }
}
