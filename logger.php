<?php
include_once 'config.php';
function log_error($text) {
    if(DEBUG) {
        error_log($text);
    }
    else {
        error_log($text . PHP_EOL, 3, LOG_PATH . "/onepace_error.log");
    }
}
function log_info($text) {
    if(DEBUG) {
        error_log($text);
    }
    else {
        error_log($text . PHP_EOL, 3, LOG_PATH . "/onepace_info.log");
    }
}
?>
