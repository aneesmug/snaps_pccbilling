<?php
class Validation extends Illuminate\Validation\Factory
{
    public static function init()
    {
        global $config;
        $language = $_SESSION['language'] ?? ($config['language'] ?? 'en');
        return new self(Translator::init($language), new Container());
    }
}
