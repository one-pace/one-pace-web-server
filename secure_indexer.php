<?php
function scr_value($array, $index) {
    if(isset($array[$index])) {
        return $array[$index];
    } else {
        return null;
    }
}
?>
