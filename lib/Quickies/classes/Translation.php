<?php
namespace Iekadou\Quickies;

class Translation
{
    const _cn = "Iekadou\\Quickies\\Translation";

    private static $languageDict = array();
    public function __construct() {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        } else {
            $lang = "en";
        }
        switch ($lang) {
            case "de":
                include(PATH."inc/language_de.php");
                Translation::$languageDict = $_;
                unset($_);
                break;
            case "en":
                include(PATH."inc/language_en.php");
                Translation::$languageDict = $_;
                unset($_);
                break;
            default:
                include(PATH."inc/language_en.php");
                Translation::$languageDict = $_;
                unset($_);
                break;
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
