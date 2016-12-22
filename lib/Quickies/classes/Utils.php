<?php
namespace Iekadou\Quickies;

class Utils {
    const _cn = "Iekadou\\Quickies\\Utils";

    public static function raise404()
    {
        global $INIT_LOADED;
        header("HTTP/1.0 404 Not Found");
        include(PATH . "views/_errors/404.php");
    }

    public static function raise404_api()
    {
        global $INIT_LOADED;
        header("HTTP/1.0 404 Not Found");
        echo json_encode(array('detail' => '404 - Not Found'));
    }

    public static function mysql_unescape_string($string) {
        $characters = array('x00', 'n', 'r', '\\', '\'', '"','x1a');
        $o_chars = array("\x00", "\n", "\r", "\\", "'", "\"", "\x1a");
        for ($i = 0; $i < strlen($string); $i++) {
            if (substr($string, $i, 1) == '\\') {
                foreach ($characters as $index => $char) {
                    if ($i <= strlen($string) - strlen($char) && substr($string, $i + 1, strlen($char)) == $char) {
                        $string = substr_replace($string, $o_chars[$index], $i, strlen($char) + 1);
                        break;
                    }
                }
            }
        }
        return $string;
    }

    public static function getMimeType($filename)
    {
        if(@is_array(getimagesize($filename))){
            return 'image';
        }
        return 'file';
    }

    public static function fixOrientation($img) {
        $exif = @exif_read_data($img);
        if (isset($exif['Orientation'])) {
            $orientation = $exif['Orientation'];
            switch($orientation) {
                case 6: // rotate 90 degrees CW
                    $degrees = -90;
                break;
                case 8: // rotate 90 degrees CCW
                   $degrees = 90;
                break;

            }
            if (isset($degrees)) {
                $source = imagecreatefromjpeg($img);
                $rotate = imagerotate($source, $degrees, 0);

                imagejpeg($rotate, $img);
                imagedestroy($source);
                imagedestroy($rotate);
            }
        }
    }

    public static function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }

    public static function endsWith($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }

    public static function liveExecuteCommand($cmd, $live=true)
    {

        while (@ ob_end_flush()); // end all output buffers if any

        $proc = popen("$cmd 2>&1 ; echo Exit status : $?", 'r');

        $live_output     = "";
        $complete_output = "";

        while (!feof($proc))
        {
            $live_output     = fread($proc, 512);
            $complete_output = $complete_output . $live_output;
            if ($live) {
                echo nl2br("$live_output");
            }
            @ flush();
        }

        pclose($proc);

        // get exit status
        preg_match('/[0-9]+$/', $complete_output, $matches);

        // return exit status and intended output
        return array (
            'exit_status'  => $matches[0],
            'output'       => str_replace("Exit status : " . $matches[0], '', $complete_output)
        );
    }

    public static function getSubclassesOf($parent) {
        $result = array();
        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, $parent))
                $result[] = $class;
        }
        return $result;
    }
}

function _i($x) {
    return new $x();
}