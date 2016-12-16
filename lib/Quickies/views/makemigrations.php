<?php
namespace Iekadou\Quickies;

require_once("../../../../../../inc/include.php");

foreach(Utils::getSubclassesOf("Iekadou\\Quickies\\BaseModel") as $class) {
    if ($UserClass == $class || $class != "Iekadou\\Quickies\\User") {
        _i($class)->makemigrations();
    }
}
