<?php

namespace Iekadou\Quickies;

use Lare_Team\Lare\Lare as Lare;
use Lare_Team\TwigLare\Twig_Lare_Extension;
use Twig_Loader_Filesystem;
use Twig_Environment;
use Twig_SimpleFilter;

class View
{
    static private $id = "";
    static private $name = "";
    static private $template;
    static private $start_time;
    static private $template_name = '';
    static public $template_vars = array();

    public function __construct($id = '', $name = '', $template = '')
    {
        self::$start_time = microtime(true);

        self::$id = $id;
        self::$name = $name;

        // Lare
        Lare::set_current_namespace(LARE_PREFIX.'.'.$id);
        self::set_template_var('lare_matching', Lare::get_matching_count());
        self::set_template_var('lare_current_namespace', Lare::get_current_namespace());
        self::set_template_var('title', SITE_NAME.' - '.$name);
        self::set_template_var('LARE_PREFIX', LARE_PREFIX);
        // template
        if (!empty($template)) {
            self::$template_name = $template;
        } else {
            self::$template_name = $id.'.html';
        }

        $loader = new Twig_Loader_Filesystem(PATH.'templates');
        if (TEMPLATE_CACHING) {
            self::$template = new Twig_Environment($loader, array(
                'cache' => PATH.'cached_templates',
            ));
        } else {
            self::$template = new Twig_Environment($loader, array());
        }
    }

    public static function get_template() {
        return self::$template;
    }

    public static function set_template_var($var_name, $value) {
        self::$template_vars[$var_name] = $value;
    }

    public static function get_template_var($var_name) {
        if (isset(self::$template_vars[$var_name])) {
            return self::$template_vars[$var_name];
        }
        return false;
    }

    public static function get_template_vars() {
        return self::$template_vars;
    }

    public static function render($display=true) {
        if (TEMPLATE_CACHING) {
            header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
        }
        self::$template_vars += Globals::get_vars();
        self::set_template_var('Account', Globals::get_var('Account'));
        self::$template->addExtension(new Twig_Lare_Extension());
        self::$template->addTokenParser(new Twig_Url_TokenParser());
        self::$template->addTokenParser(new Twig_Trans_TokenParser());
        self::$template->addTokenParser(new Twig_Time_TokenParser());

        $filter = new Twig_SimpleFilter('utf8_encode', function ($string) {
            if (!mb_detect_encoding($string, 'UTF-8', true)) {
                return utf8_encode($string);
            }
            return $string;
        });

        self::$template->addFilter($filter);
        if ($display) {
            echo self::$template->render(self::$template_name, self::get_template_vars());
        } else {
            return self::$template->render(self::$template_name, self::get_template_vars());
        }
    }
}
