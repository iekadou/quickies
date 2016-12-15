<?php
namespace Iekadou\Quickies;

class UrlsPy {
    private static $patterns = array();
    public function __construct() {
        $lines = file(PATH.".htaccess");
        $activated = false;
        foreach($lines as $line)
        {
            if (Utils::startsWith($line, '#URLS_PY START#'))
            {
                $activated = true;
            } elseif (Utils::startsWith($line, '#URLS_PY END#')) {
                $activated = false;
            }
            if ($activated && $line != '' && Utils::startsWith($line, 'RewriteRule ^')) {
                $string_before_url = '\^';
                $string_after_url = '\$';
                $regex_url= "/".$string_before_url."(.*?)".$string_after_url."/";

                preg_match_all($regex_url, $line, $matches_url);
                $match_url = $matches_url[1][0];
                $match_url = '/'.str_replace('([^/\.]+)/?', '%s/', $match_url);


                $string_before_namespace = '###';
                $string_after_namespace = '###';
                $regex_namespace= "/".$string_before_namespace."(.*?)".$string_after_namespace."/";

                preg_match_all($regex_namespace, $line, $matches_namespace);
                $match_namespace = $matches_namespace[1][0];

                if (isset(UrlsPy::$patterns[$match_namespace])) {
                    if (is_array(UrlsPy::$patterns[$match_namespace])) {
                        UrlsPy::$patterns[$match_namespace][] = $match_url;
                    } else {
                        UrlsPy::$patterns[$match_namespace] = array(UrlsPy::$patterns[$match_namespace], $match_url);
                    }
                } else {
                    UrlsPy::$patterns[$match_namespace] = $match_url;
                }
            }
        }

    }

    public static function get_url($name, $args=array()) {
        $arg_count = sizeof($args);
        if (isset(UrlsPy::$patterns[$name])) {
            if (is_array(UrlsPy::$patterns[$name])) {
                foreach(UrlsPy::$patterns[$name] as $url) {
                    if (preg_match_all('/%s/', $url, $matches) == $arg_count) {
                        return vsprintf($url, $args);
                    }
                }
            } else {
                if (preg_match_all('/%s/', UrlsPy::$patterns[$name], $matches) == $arg_count) {
                    return vsprintf(UrlsPy::$patterns[$name], $args);
                }
            }
        }
        throw new \Exception(vsprintf("Url '%s' not found with %s args!", array($name, sizeof($args))));
    }
}

new UrlsPy();
