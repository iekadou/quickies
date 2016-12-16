<?php

namespace Iekadou\Quickies;

use Lare_Team\TwigLare\Twig_Lare_Extension;
use Twig_Loader_Filesystem;
use Twig_Environment;
use Twig_SimpleFilter;

class Renderable
{
    protected $id = "";
    protected $name = "";
    protected $template;
    protected $start_time;
    protected $template_name = '';
    public $template_vars = array();

    public function __construct($id = '', $name = '', $template = '')
    {
        $this->start_time = microtime(true);

        $this->id = $id;
        $this->name = $name;

        // template
        if (!empty($template)) {
            $this->template_name = $template;
        } else {
            $this->template_name = $id.'.html';
        }

        $loader = new Twig_Loader_Filesystem(PATH.'templates');
        if (TEMPLATE_CACHING) {
            $this->template = new Twig_Environment($loader, array(
                'cache' => PATH.'cached_templates',
            ));
        } else {
            $this->template = new Twig_Environment($loader, array());
        }
        $this->_post_construct();
    }

    public function _post_construct() {
    }

    public function get_template() {
        return $this->template;
    }

    public function set_template_var($var_name, $value) {
        $this->template_vars[$var_name] = $value;
    }

    public function get_template_var($var_name) {
        if (isset($this->template_vars[$var_name])) {
            return $this->template_vars[$var_name];
        }
        return false;
    }

    public function get_template_vars() {
        return $this->template_vars;
    }

    public function pre_render() {
        if (TEMPLATE_CACHING) {
            header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
        }
        $this->template_vars += Globals::get_vars();
        $this->set_template_var('Account', Globals::get_var('Account'));
        $this->template->addExtension(new Twig_Lare_Extension());
        $this->template->addTokenParser(new Twig_Url_TokenParser());
        $this->template->addTokenParser(new Twig_Trans_TokenParser());
        $this->template->addTokenParser(new Twig_Time_TokenParser());

        $filter = new Twig_SimpleFilter('utf8_encode', function ($string) {
            if (!mb_detect_encoding($string, 'UTF-8', true)) {
                return utf8_encode($string);
            }
            return $string;
        });

        $this->template->addFilter($filter);
    }

    public function render($display=true) {
        $this->pre_render();
        if ($display) {
            echo $this->template->render($this->template_name, $this->get_template_vars());
        } else {
            return $this->template->render($this->template_name, $this->get_template_vars());
        }
    }
}
