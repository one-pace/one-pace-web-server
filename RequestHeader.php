<?php
header('Content-Type: application/json; charset=utf-8;');
header('Vary: Origin', true);
// allowed origins
$allowed_http_origins = array(
	'https://onepace.net',
	'https://www.onepace.net',
	'http://onepace.net',
	'http://www.onepace.net',
	'http://127.0.0.1',
	'http://localhost',
);
// get Origin sendend by the client if any
$http_origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : FALSE;
// check if the Origin is a allowed one
if ($http_origin !== FALSE && in_array($http_origin, $allowed_http_origins))
{
	// set the Access-Control-Allow-Origin to allow the requested origin
	header('Access-Control-Allow-Origin: ' . $http_origin, true);
}
