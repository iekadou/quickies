<?php
namespace Iekadou\Quickies;

class Files
{
    private static $allowedExtensions = array();
    private static $filetypes = array();
    public function __construct() {

        $filetypes = array();
        $filetypes['image'] = 1;
        Files::$filetypes = $filetypes;

        $allowedExtensions = array();
        $allowedExtensions['png']['mime'] = array('image/png', 'image/x-png');
        $allowedExtensions['png']['filetype'] = $filetypes['image'];
        $allowedExtensions['jpg']['mime'] = array('image/jpeg', 'image/pjpeg');
        $allowedExtensions['jpg']['filetype'] = $filetypes['image'];
        $allowedExtensions['jpeg']['mime'] = array('image/jpeg', 'image/pjpeg');
        $allowedExtensions['jpeg']['filetype'] = $filetypes['image'];
        $allowedExtensions['gif']['mime'] = array('image/gif');
        $allowedExtensions['gif']['filetype'] = $filetypes['image'];
        $allowedExtensions['gif']['mime'] = array('image/gif');
        $allowedExtensions['gif']['filetype']  = $filetypes['image'];
        Files::$allowedExtensions = $allowedExtensions;

    }

    public static function get_allowed_extensions()
    {
        return Files::$allowedExtensions;
    }
}
new Files();
