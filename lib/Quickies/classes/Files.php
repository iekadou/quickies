<?php
namespace Iekadou\Quickies;

class Files
{
    const _cn = "Iekadou\\Quickies\\Files";

    private static $allowedExtensions = array();
    private static $postScriptExtensions = array();
    private static $imageExtensions = array();
    private static $pdfExtensions = array();
    private static $jsonExtensions = array();
    private static $xmlExtensions = array();
    private static $filetypes = array();
    public function __construct() {

        $filetypes = array();
        $filetypes['image'] = 1;
        $filetypes['postscript'] = 2;
        $filetypes['pdf'] = 3;
        $filetypes['json'] = 4;
        $filetypes['xml'] = 4;
        Files::$filetypes = $filetypes;

        $ImageExtensions = array();
        $ImageExtensions['png']['mime'] = array('image/png', 'image/x-png');
        $ImageExtensions['png']['filetype'] = $filetypes['image'];
        $ImageExtensions['jpg']['mime'] = array('image/jpeg', 'image/pjpeg');
        $ImageExtensions['jpg']['filetype'] = $filetypes['image'];
        $ImageExtensions['jpeg']['mime'] = array('image/jpeg', 'image/pjpeg');
        $ImageExtensions['jpeg']['filetype'] = $filetypes['image'];
        $ImageExtensions['gif']['mime'] = array('image/gif');
        $ImageExtensions['gif']['filetype'] = $filetypes['image'];
        $ImageExtensions['gif']['mime'] = array('image/gif');
        $ImageExtensions['gif']['filetype']  = $filetypes['image'];

        $PostScriptExtensions = array();
        $PostScriptExtensions['ai']['mime'] = array('application/postscript');
        $PostScriptExtensions['ai']['filetype'] = $filetypes['postscript'];
        $PostScriptExtensions['eps']['mime'] = array('application/postscript');
        $PostScriptExtensions['eps']['filetype'] = $filetypes['postscript'];
        $PostScriptExtensions['ps']['mime'] = array('application/postscript');
        $PostScriptExtensions['ps']['filetype'] = $filetypes['postscript'];

        $PdfExtensions = array();
        $PdfExtensions['pdf']['mime'] = array('application/pdf');
        $PdfExtensions['pdf']['filetype'] = $filetypes['pdf'];

        $JsonExtensions = array();
        $JsonExtensions['json']['mime'] = array('application/json');
        $JsonExtensions['json']['filetype'] = $filetypes['json'];

        $XmlExtensions = array();
        $XmlExtensions['xml']['mime'] = array('application/xml');
        $XmlExtensions['xml']['filetype'] = $filetypes['xml'];

        Files::$imageExtensions = $ImageExtensions;
        Files::$postScriptExtensions = $PostScriptExtensions;
        Files::$pdfExtensions = $PdfExtensions;
        Files::$jsonExtensions = $JsonExtensions;
        Files::$xmlExtensions = $XmlExtensions;

        Files::$allowedExtensions = $ImageExtensions;
        Files::$allowedExtensions = array_merge(Files::$allowedExtensions, $PostScriptExtensions);
        Files::$allowedExtensions = array_merge(Files::$allowedExtensions, $PdfExtensions);
        Files::$allowedExtensions = array_merge(Files::$allowedExtensions, $JsonExtensions);
        Files::$allowedExtensions = array_merge(Files::$allowedExtensions, $XmlExtensions);

    }

    public static function get_image_extensions()
    {
        return Files::$imageExtensions;
    }

    public static function get_postscript_extensions()
    {
        return Files::$postScriptExtensions;
    }

    public static function get_pdf_extensions()
    {
        return Files::$pdfExtensions;
    }

    public static function get_json_extensions()
    {
        return Files::$jsonExtensions;
    }

    public static function get_xml_extensions()
    {
        return Files::$xmlExtensions;
    }

    public static function get_allowed_extensions()
    {
        return Files::$allowedExtensions;
    }
}
new Files();
