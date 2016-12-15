<?php
namespace Iekadou\Quickies;

use Lare_Team\Lare\Lare as Lare;

class Globals {
    private static $vars = array();

    public function __construct() {
    }

    public static function set_var($name, $value) {
        self::$vars[$name] = $value;
    }
    public static function get_var($name) {
        return self::$vars[$name];
    }
    public static function get_vars() {
        if (DISPLAY_CURRENT_TIME) {
            Globals::set_var('display_current_time', true);
        }
        if (DISPLAY_DEBUG_INFORMATION) {
            Globals::set_var('display_debug_information', true);
        }
        Globals::set_var('current_time', date('d.m.Y - H:i:s', time()));
        Globals::set_var('Lare', Lare);
        Globals::set_var('version', "1.0.0a");
        return self::$vars;
    }
}
new Globals();
