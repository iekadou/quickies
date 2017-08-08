<?php
function _i($x) {
    if (class_exists($x)) {
        return new $x();
    } else {
        return null;
    }
}
