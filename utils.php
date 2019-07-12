<?php
include_once 'config.php';
include_once 'db_context.php';
class Utils {
	static function post() {
		return json_decode(file_get_contents('php://input'), true);
	}
	static function log_error($text) {
		if(DEBUG) {
			error_log($text);
		} else {
			error_log($text . PHP_EOL, 3, LOG_PATH . "/onepace_error.log");
		}
	}
	static function log_info($text) {
		if(DEBUG) {
			error_log($text);
		} else {
			error_log($text . PHP_EOL, 3, LOG_PATH . "/onepace_info.log");
		}
	}
	static function echo_json($data) {
		header('Content-Type: application/json; charset=utf-8;');
		echo json_encode($data);
	}
	static function authenticate($context, $token, $role, &$user) {
		$user = null;
		if($token == null || strlen($token) == 0) {
			return false;
		}
		$context->connect();
		$stmt = $context->prepare("select * from users where token = ?;");
		$stmt->bind_param("s", $token);
		$rows = $context->get_result($stmt);
		$context->disconnect();
		if(sizeof($rows) > 0) {
			$user = $rows[0];
		}
		return sizeof($rows) > 0 && $rows[0]["role"] >= $role;
	}
	static function verify(&$context, $token, $role) {
		$context = new db_context();
		$user = null;
		if(!Utils::authenticate($context, $token, $role, $user)) {
			http_response_code(400);
			exit;
		}
		return $user;
	}
	static function verify_password($context, $token, $password) {
		$context->connect();
		$stmt = $context->prepare("select * from users where token = ? and password = ?;");
		$stmt->bind_param("ss", $token, $password);
		$rows = $context->get_result($stmt);
		$context->disconnect();
		return sizeof($rows) > 0;
	}
	static function upload($file) {
		
	}
}
?>
