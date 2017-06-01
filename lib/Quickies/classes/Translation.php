<?php
namespace Iekadou\Quickies;

class Translation
{
    const _cn = "Iekadou\\Quickies\\Translation";

    private static $languageDict = array();
    public static $activateLanguage = DEFAULT_LANGUAGE;
    public function __construct() {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            Translation::$activateLanguage = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        }
        try {
            try {
                include(PATH . "inc/language_" . Translation::$activateLanguage . ".php");
                Translation::$languageDict = $_;
                unset($_);
            } catch (\Exception $e) {
                // user language is not available, fallback to default
                Translation::$activateLanguage = DEFAULT_LANGUAGE;
                include(PATH."inc/language_" . Translation::$activateLanguage . ".php");
                Translation::$languageDict = $_;
                unset($_);
            }
        } catch (\Exception $e) {
            // user and default language was found.
        }
    }

    public static function translate($string, $array=array())
    {
        if (isset(Translation::$languageDict[$string])) {
            $tranlsation = Translation::$languageDict[$string];
        } else {
            $tranlsation = $string;
        }
        return strtr($tranlsation, $array);
    }
}
new Translation();
