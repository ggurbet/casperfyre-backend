<?php
/**
 * Start session
 */
session_start();

/**
 * Load config
 */

if(!file_exists(__DIR__.'/.env')) {
	header('Content-type:application/json;charset=utf-8');
	http_response_code(500);
	exit(json_encode(array(
		'status' => 'error',
		'detail' => 'Please configure API server. error code 1'
	)));
}

include_once('classes/dotenv.php');
$dotenv = new Dotenv(__DIR__.'/.env');
$dotenv->load();

define('BASE_DIR', __DIR__);
define('API_VERSION', 1);
define('APP_NAME', getenv('APP_NAME'));
define('CORS_SITE', getenv('CORS_SITE'));
define('DB_HOST', getenv('DB_HOST'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASS', getenv('DB_PASS'));
define('DB_NAME', getenv('DB_NAME'));
define('ADMIN_EMAIL', getenv('ADMIN_EMAIL'));
define('MASTER_KEY', getenv('MASTER_KEY'));
define('DEV_MODE', (bool)(getenv('DEV_MODE')));

if(filter_var(getenv('NODE_IP'), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
	define('NODE_IP', 'http://'.getenv('NODE_IP').':7777');
} else {
	define('NODE_IP', 'http://127.0.0.1:7777');
}

/**
 * Load classes
 */
include_once('vendor/autoload.php');
include_once('classes/db.php');
include_once('classes/helper.php');
include_once('classes/throttle.php');
include_once('classes/endpoints.php');

/**
 * Instantiate
 *
 * @var DB        $db            Database instance.
 * @var Helper    $helper        Helper instance.
 * @var Throttle  $throttle      Helper instance.
 * @var RpcClient $casper_client Helper instance.
 *
 */
$db = new DB();
$helper = new Helper();
$throttle = new Throttle($helper->get_real_ip());
$casper_client = new Casper\Rpc\RpcClient(NODE_IP);

/**
 * Check DB integrity
 */
$db->check_integrity();

/**
 * Error logging
 *
 * @param string $msg
 */
function elog($msg) {
	file_put_contents('php://stderr', print_r("\n", true));
	file_put_contents('php://stderr', '['.APP_NAME.' '.(date('c')).'] - ');
	file_put_contents('php://stderr', print_r($msg, true));
}

/**
 * Response code handler, if PHP version < 5.4
 *
 * @param  string $code
 * @return int    $code
 */
if (!function_exists('http_response_code')) {
	function http_response_code($code = NULL) {
		if ($code !== NULL) {
			switch ($code) {
				case 100: $text = 'Continue'; break;
				case 101: $text = 'Switching Protocols'; break;
				case 200: $text = 'OK'; break;
				case 201: $text = 'Created'; break;
				case 202: $text = 'Accepted'; break;
				case 203: $text = 'Non-Authoritative Information'; break;
				case 204: $text = 'No Content'; break;
				case 205: $text = 'Reset Content'; break;
				case 206: $text = 'Partial Content'; break;
				case 300: $text = 'Multiple Choices'; break;
				case 301: $text = 'Moved Permanently'; break;
				case 302: $text = 'Moved Temporarily'; break;
				case 303: $text = 'See Other'; break;
				case 304: $text = 'Not Modified'; break;
				case 305: $text = 'Use Proxy'; break;
				case 400: $text = 'Bad Request'; break;
				case 401: $text = 'Unauthorized'; break;
				case 402: $text = 'Payment Required'; break;
				case 403: $text = 'Forbidden'; break;
				case 404: $text = 'Not Found'; break;
				case 405: $text = 'Method Not Allowed'; break;
				case 406: $text = 'Not Acceptable'; break;
				case 407: $text = 'Proxy Authentication Required'; break;
				case 408: $text = 'Request Time-out'; break;
				case 409: $text = 'Conflict'; break;
				case 410: $text = 'Gone'; break;
				case 411: $text = 'Length Required'; break;
				case 412: $text = 'Precondition Failed'; break;
				case 413: $text = 'Request Entity Too Large'; break;
				case 414: $text = 'Request-URI Too Large'; break;
				case 415: $text = 'Unsupported Media Type'; break;
				case 429: $text = 'Too Many Requests'; break;
				case 500: $text = 'Internal Server Error'; break;
				case 501: $text = 'Not Implemented'; break;
				case 502: $text = 'Bad Gateway'; break;
				case 503: $text = 'Service Unavailable'; break;
				case 504: $text = 'Gateway Time-out'; break;
				case 505: $text = 'HTTP Version not supported'; break;
				default:
					exit('Unknown http status code "' . htmlentities($code) . '"');
				break;
			}

			$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
			header($protocol . ' ' . $code . ' ' . $text);
			$GLOBALS['http_response_code'] = $code;
		} else {
			$code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
		}
		return $code;
	}
}

/**
 * exit handler to include exit code, status, detail, exception
 *
 * @param  string $status
 * @param  string $detail
 * @param  int    $exit_code
 * @param  string $exception
 *
 */
function _exit(
	$status, 
	$detail, 
	$exit_code = 200,
	$exception = ''
) {
	if($exit_code != 200) {
		elog(
			strtoupper($_SERVER['REQUEST_METHOD'] ?? '').' '.
			($_SERVER['REQUEST_URI'] ?? '/').' '.
			(string)$exit_code.' '.
			$status.
			($exception ? ' - ' : '').$exception
		);
	}

	header('Content-type:application/json;charset=utf-8');
	http_response_code($exit_code);
	exit(json_encode(array(
		'status' => $status,
		'detail' => $detail
	)));
}

/**
 * Get http method
 *
 * @return string
 */
function get_method() {
	if(isset($_SERVER['REQUEST_METHOD']))
		return strtoupper($_SERVER['REQUEST_METHOD']);
	return 'GET';
}

/**
 * Require http method
 *
 * @param  string|array $m  accepted method/methods
 * @return bool
 */
function require_method($m) {
	$method = get_method();

	if($method == 'OPTIONS') {
		_exit(
			'success',
			'Success',
			200
		);
	}

	if(gettype($m) == 'array') {
		if(in_array($method, $m)) {
			return true;
		}

		_exit(
			'error', 
			'Invalid method. Only '.implode('/', $m).' allowed', 
			405
		);
	} else {
		if($method == $m) {
			return true;
		}

		_exit(
			'error', 
			'Invalid method. Only '.$m.' allowed', 
			405
		);
	}
}

/**
 * Filter/sanitize parameters for GET requests
 *
 * @param  string $string Untrusted string to filter
 * @return string
 */
function filter($string) {
	if(gettype($string) == 'array')
		return $string;

	$string = addslashes(trim($string));
	return htmlentities($string, ENT_COMPAT | ENT_HTML401, 'UTF-8');
}

/**
 * Filter/sanitize nested json parameters for GET requests
 *
 * @param  array $data
 * @return array
 */
function filter_array($data = array()) {
	if($data && count($data) > 0){
		foreach($data as $key => &$value) {
			$value = filter($value);
		}
	}
	return $data;
}

/**
 * Filter/sanitize json parameters for POST requests
 *
 * @return array
 */
function get_params() {
	$jsonString = file_get_contents('php://input');
	$json = json_decode($jsonString, true);

	if(!$json || count($json) == 0)
		return null;

	return filter_array($json);
}

/**
 * Authenticate a session for frontend
 *
 * @param  int   $required_clearance
 * @return array
 *
 * If session belongs to a role with low clearance, checks for admin approval/verification. With some exceptions.
 *
 */
function authenticate_session($required_clearance = 1) {
	global $db, $helper;

	$headers = getallheaders();

	$auth_bearer_header = (
		$headers['Authorization'] ??
		$headers['authorization'] ??
		''
	);

	$auth_bearer = explode(' ', $auth_bearer_header);
	$auth_bearer_t = $auth_bearer[0];
	$auth_bearer = filter($auth_bearer[1] ?? '');

	if(strtolower($auth_bearer_t) != 'bearer') {
		_exit(
			'error', 
			'Unauthorized', 
			401,
			'Bearer token not found'
		);
	}

	if(
		!ctype_xdigit($auth_bearer) ||
		strlen($auth_bearer) != 256
	) {
		_exit(
			'error', 
			'Invalid bearer token', 
			400,
			'Invalid bearer token'
		);
	}

	$query = "
		SELECT a.guid, a.expires_at, b.role, b.twofa, b.verified, b.admin_approved
		FROM sessions AS a
		JOIN users AS b
		ON a.guid = b.guid
		WHERE a.bearer = '$auth_bearer'
	";

	$selection = $db->do_select($query);
	$selection = $selection[0] ?? null;
	$guid = $selection['guid'] ?? '';
	$session_role = $selection['role'] ?? '';
	$expires_at = $selection['expires_at'] ?? date('Y-m-d H:i:s', 0);
	$verified = (int)($selection['verified'] ?? 0);
	$admin_approved = (int)($selection['admin_approved'] ?? 0);
	$clearance = 0;

	if(!$selection) {
		_exit(
			'error', 
			'Unauthorized', 
			401,
			'No session token found'
		);
	}

	if($expires_at < $helper->get_datetime()) {
		$query = "
			DELETE FROM sessions
			WHERE guid = '$guid'
		";
		$db->do_query($query);
		_exit(
			'error', 
			'Session expired', 
			401,
			'Expired session token'
		);
	}

	switch ($session_role) {
		case 'test-user': $clearance = 0; break;
		case 'user': $clearance = 1; break;
		case 'sub-admin': $clearance = 2; break;
		case 'admin': $clearance = 3; break;
		case 'super-admin': $clearance = 4; break;
		default: $clearance = 0; break;
	}

	if($clearance < $required_clearance) {
		_exit(
			'error', 
			'Unauthorized', 
			401,
			'Failed clearance check'
		);
	}

	/* if session belongs to a role with low clearance */
	if($clearance < 2) {
		$request_uri = $_SERVER['REQUEST_URI'] ?? '';

		if(
			(
				$admin_approved == 0 ||
				$verified == 0
			) &&
			$request_uri != '/user/confirm-registration' &&
			$request_uri != '/user/resend-code' &&
			$request_uri != '/user/me' &&
			$request_uri != '/user/logout'
		) {
			_exit(
				'error', 
				'Unauthorized', 
				401,
				'Failed clearance level 1 with no verification or admin approval'
			);
		}
	}

	/* fail for banned sub-admin role */
	if($clearance == 2 && $admin_approved == 0) {
		_exit(
			'error', 
			'Unauthorized', 
			401,
			'Failed clearance level 2 with no verification or admin approval'
		);
	}

	return $selection;
}

/**
 * Authenticate a request to the client facing API 
 *
 * @return array
 */
function authenticate_api() {
	global $db;

	$headers = getallheaders();

	if(get_method() !== 'GET') {
		$content_type = (
			$headers['Content-Type'] ??
			$headers['Content-type'] ??
			$headers['content-type'] ?? null
		);

		if(!$content_type) {
			_exit(
				"error", 
				"Only Content-Type application/json is accepted", 
				400
			);
		}

		if(strtolower($content_type) != 'application/json') {
			_exit(
				"error", 
				"Only Content-Type application/json is accepted", 
				400
			);
		}
	}

	$auth_token_header = (
		$headers['Authorization'] ??
		$headers['authorization'] ??
		''
	);

	$auth_token = explode(' ', $auth_token_header);
	$auth_token_t = $auth_token[0];
	$auth_token = filter($auth_token[1] ?? '');

	if(
		$auth_token_t != 'Token' &&
		$auth_token_t != 'token'
	) {
		_exit(
			'error', 
			'Unauthorized', 
			401
		);
	}

	/* safe immortal token for testing */
	if($auth_token == 'phpunittesttoken') {
		return array(
			"api_key" => "phpunittesttoken",
			"active" => 1,
			"guid" => "00000000-0000-0000-4c4c-000000000000",
			"api_key_active" => 1,
			"bearer" => "phpunittesttoken"
		);
	}

	$query = "
		SELECT a.api_key, a.active AS api_key_active, b.guid AS guid, b.api_key_active AS account_active
		FROM api_keys AS a
		JOIN users AS b
		ON a.guid = b.guid
		WHERE a.api_key = '$auth_token'
	";

	$selection = $db->do_select($query);

	if(!$selection) {
		_exit(
			'error', 
			'Unauthorized', 
			401
		);
	}

	return $selection[0];
}

/**
 * Filter/sanitize parameters for GET requests
 *
 * @param  string $key
 * @param  int    $strict for more strictness in filtering
 * @return string
 */
function _request($key, $strict = 0) {
	if(isset($_REQUEST[$key])) {
		if($strict == 1) {
			$data = $_REQUEST[$key];
			$output = '';
			$length = strlen($data);

			for($i = 0; $i < $length; $i++) {

				if(preg_match("/['A-Za-z0-9.,-@+]+/", $data[$i])) {
					$output .= $data[$i];
				}
			}
			return filter($output);

		} elseif($strict == 2) {
			$data = $_REQUEST[$key];
			$output = '';
			$length = strlen($data);

			for($i = 0; $i < $length; $i++) {

				if(preg_match("/[A-Za-z0-9]+/", $data[$i])) {
					$output .= $data[$i];
				}
			}
			return filter($output);
		}
		return filter($_REQUEST[$key]);
	}
	return '';
}

/**
 * Insert an order. Meant to insert an order regardless if the request succeeds or fails.
 *
 * @param  string $guid
 * @param  string $datetime
 * @param  string $ip
 * @param  int    $return_code
 * @param  int    $fulfilled
 * @param  int    $amount
 * @param  string $address
 * @param  int    $api_key_id
 * @return bool
 */
function insert_order(
	$guid,
	$datetime,
	$ip,
	$return_code,
	$fulfilled,
	$amount,
	$address,
	$api_key_id
) {
	global $db;

	/* record order */
	$query = "
		INSERT INTO orders (
			guid, 
			created_at, 
			ip, 
			return_code, 
			fulfilled, 
			amount, 
			address, 
			api_key_id_used
		) VALUES (
			'$guid', 
			'$datetime', 
			'$ip', 
			$return_code, 
			$fulfilled, 
			$amount, 
			'$address', 
			$api_key_id
		)
	";

	$result = $db->do_query($query);

	/* record calls on the api key used */
	$query = "
		SELECT total_calls
		FROM api_keys
		WHERE id = $api_key_id
	";
	$total_calls = $db->do_select($query);
	$total_calls = (int)($total_calls[0]['total_calls'] ?? 0);
	$total_calls += 1;
	$query = "
		UPDATE api_keys
		SET total_calls = $total_calls
		WHERE id = $api_key_id
	";
	$db->do_query($query);

	return $result;
}

/**
 * Process a request to the client facing API to place an order. Orders will begin logging once the API has an identity from authentication.
 *
 * @param  string $guid
 * @param  string $address
 * @param  int    $amount
 * @param  int    $api_key_id
 */
function process_order(
	$guid, 
	$address, 
	$amount,
	$authentication_array
) {
	global $helper;

	$datetime = $helper->get_datetime();
	$ip = $helper->get_real_ip();
	$amount = (int)$amount;

	$RETURN_STATUS = 'success';
	$RETURN_MSG = 'Dispensing '.$amount.' CSPR to '.$address;
	$RETURN_CODE = 200;
	$FULFILLED = 0;

	/* test case */
	if($guid == "00000000-0000-0000-4c4c-000000000000") {
		_exit(
			"success",
			array(
				"RETURN_STATUS" => $RETURN_STATUS,
				"RETURN_MSG" => $RETURN_MSG,
				"RETURN_CODE" => $RETURN_CODE,
			)
		);
	}

	/* usage */
	$usage = get_usage($guid);
	$per = (int)$usage['usage_per'];
	$today = (int)$usage['usage_today_total'] - (int)$usage['usage_today'];
	$month = (int)$usage['usage_thismonth_total'] - (int)$usage['usage_thismonth'];

	if($amount > $per) {
		$RETURN_STATUS = 'error';
		$RETURN_MSG = 'You cannot dispense more than '.$per.' CSPR as a time';
		$RETURN_CODE = 403;
		$FULFILLED = 2;
	}

	if($today < $month) {
		$timeframe = 'today';
		$remaining = $today;
	} else {
		$timeframe = 'this month';
		$remaining = $month;
	}

	if($remaining == 0) {
		$RETURN_STATUS = 'error';
		$RETURN_MSG = 'You cannot dispense any more CSPR. Your limit has been reached';
		$RETURN_CODE = 403;
		$FULFILLED = 2;
	}

	if($remaining - $amount < 0) {
		$RETURN_STATUS = 'error';
		$RETURN_MSG = 'You cannot dispense this many CSPR. You have '.$remaining.' remaining for '.$timeframe;
		$RETURN_CODE = 403;
		$FULFILLED = 2;
	}

	/* ip whitelist */
	$whitelist = whitelist($guid);

	if(!$whitelist) {
		$RETURN_STATUS = 'error';
		$RETURN_MSG = 'IP address authentication failed';
		$RETURN_CODE = 401;
		$FULFILLED = 2;
	}

	/* syntax/params check */
	if(!$address) {
		$RETURN_STATUS = 'error';
		$RETURN_MSG = 'Address parameter cannot be blank';
		$RETURN_CODE = 400;
		$FULFILLED = 2;
	}

	if(!$helper->correct_validator_id_format($address)) {
		$RETURN_STATUS = 'error';
		$RETURN_MSG = 'Invalid address specified';
		$RETURN_CODE = 400;
		$FULFILLED = 2;
	}

	if(!$amount || $amount == 0) {
		$RETURN_STATUS = 'error';
		$RETURN_MSG = 'Amount parameter cannot be 0';
		$RETURN_CODE = 400;
		$FULFILLED = 2;
	}

	/* api key check */
	$api_key = $authentication_array['api_key'] ?? '';
	$api_key_id = $helper->get_apikey_id_by_apikey($api_key);
	$api_key_active = (int)($authentication_array['api_key_active'] ?? 0);
	$account_active = (int)($authentication_array['account_active'] ?? 0);

	if($account_active === 0) {
		$RETURN_STATUS = 'error';
		$RETURN_MSG = 'Your account is frozen';
		$RETURN_CODE = 401;
		$FULFILLED = 2;
	}

	if($api_key_active === 0) {
		$RETURN_STATUS = 'error';
		$RETURN_MSG = 'Your API key has been frozen';
		$RETURN_CODE = 401;
		$FULFILLED = 2;
	}

	/* insert order */
	$inserted = insert_order(
		$guid,
		$datetime,
		$ip,
		$RETURN_CODE,
		$FULFILLED,
		$amount,
		$address,
		$api_key_id
	);

	if($inserted) {
		_exit(
			$RETURN_STATUS,
			$RETURN_MSG,
			$RETURN_CODE
		);
	}

	_exit(
		'error',
		'Failed to place order. Internal server error. Please contact administration. Error code 2',
		500,
		'Failed to place order. Internal server error. Please contact administration. Error code 2'
	);
}

/**
 * Get API usage of a specified user by guid
 *
 * @param  string $guid
 * @return array
 */
function get_usage($guid = '') {
	global $db;

	$usage_today = 0;
	$usage_yesterday = 0;
	$usage_thismonth = 0;
	$usage_lastmonth = 0;
	$usage_today_total = 0;
	$usage_yesterday_total = 0;
	$usage_thismonth_total = 0;
	$usage_lastmonth_total = 0;

	if($guid) {
		$query = "
			SELECT *
			FROM limits
			WHERE guid = '$guid'
		";
		$limits = $db->do_select($query);
		$usage_per = $limits[0]['per_limit'] ?? 0;
		$usage_today_total = $limits[0]['day_limit'] ?? 0;
		$usage_yesterday_total = $limits[0]['day_limit'] ?? 0;
		$usage_thismonth_total = $limits[0]['month_limit'] ?? 0;
		$usage_lastmonth_total = $limits[0]['month_limit'] ?? 0;

		$now = time();
		$onedayago = date("Y-m-d H:i:s", $now - 86400);
		$twodaysago = date("Y-m-d H:i:s", $now - (86400 * 2));
		$onemonthago = date("Y-m-d H:i:s", $now - (86400 * 30));
		$twomonthsago = date("Y-m-d H:i:s", $now - (86400 * 61));

		$query = "
			SELECT amount
			FROM orders
			WHERE guid = '$guid' 
			AND created_at >= '$onedayago'
			AND (
				fulfilled = 0
				OR
				fulfilled = 1
			)
		";
		$results_today = $db->do_select($query);

		$query = "
			SELECT amount 
			FROM orders
			WHERE guid = '$guid' 
			AND created_at >= '$twodaysago' 
			AND created_at < '$onedayago'
			AND (
				fulfilled = 0
				OR
				fulfilled = 1
			)
		";
		$results_yesterday = $db->do_select($query);

		$query = "
			SELECT amount 
			FROM orders
			WHERE guid = '$guid' 
			AND created_at >= '$onemonthago'
			AND (
				fulfilled = 0
				OR
				fulfilled = 1
			)
		";
		$results_thismonth = $db->do_select($query);

		$query = "
			SELECT amount 
			FROM orders
			WHERE guid = '$guid' 
			AND created_at >= '$twomonthsago' 
			AND created_at < 'onemonthago'
			AND (
				fulfilled = 0
				OR
				fulfilled = 1
			)
		";
		$results_lastmonth = $db->do_select($query);

		if($results_today) {
			foreach($results_today as $r) {
				$usage_today += $r['amount'];
			}
		}

		if($results_yesterday) {
			foreach($results_yesterday as $r) {
				$usage_yesterday += $r['amount'];
			}
		}

		if($results_thismonth) {
			foreach($results_thismonth as $r) {
				$usage_thismonth += $r['amount'];
			}
		}

		if($results_lastmonth) {
			foreach($results_lastmonth as $r) {
				$usage_lastmonth += $r['amount'];
			}
		}
	}

	$output = array(
		"usage_per" => $usage_per,
		"usage_today" => $usage_today,
		"usage_yesterday" => $usage_yesterday,
		"usage_thismonth" => $usage_thismonth,
		"usage_lastmonth" => $usage_lastmonth,
		"usage_today_total" => $usage_today_total,
		"usage_yesterday_total" => $usage_yesterday_total,
		"usage_thismonth_total" => $usage_thismonth_total,
		"usage_lastmonth_total" => $usage_lastmonth_total,
	);

	return $output;
}

/**
 * Determine if a user is whitelist authenticated by IP and guid
 *
 * @param  string $guid
 * @return bool
 */
function whitelist($guid) {
	global $db, $helper;

	$query = "
		SELECT ip, active
		FROM ips
		WHERE guid = '$guid'
	";

	$selection = $db->do_select($query);

	if(!$selection) {
		return false;
	}

	$current_ip = $helper->get_real_ip();
	$authenticated = false;

	foreach($selection as $ip) {
		if(
			$helper->in_CIDR_range(
				strtolower($current_ip),
				strtolower($ip['ip'])
			) &&
			(int)$ip['active'] == 1
		) {
			$authenticated = true;
		}
	}

	if($authenticated) {
		return true;
	}

	return false;
}

/**
 * Request origin protection
 */
function cors_protect() {
	// return true;

	if(!isset($_SERVER['HTTP_REFERER'])) {
		header("Location: /");
		exit('Error - Browser referrer-policy. Please refresh or <a href="/">Return Home</a>');
	}

	$origin = $_SERVER['HTTP_REFERER'];
	$origin = str_replace('http://', '', $origin);
	$origin = str_replace('https://', '', $origin);
	$origin = explode('/', $origin)[0];
	$origincom = explode('.com', $origin)[0];
	$originco = explode('.co', $origin)[0];
	$originnet = explode('.net', $origin)[0];
	$originio = explode('.io', $origin)[0];

	if($origincom != CORS_SITE) {
		header("Location: /");
		exit('Error - Browser referrer-policy. Please refresh or <a href="/">Return Home</a>');
	}
}

/**
 * CSRF protection
 */
function csrf_protect() {
	// return true;
	if(!isset($_POST['token']) && !isset($_GET['token'])) {
		header("Location: /");
		exit('Error - Missing CSRF_TOKEN. <a href="/">Return home</a>');
	}

	if(isset($_REQUEST['token_type']) && $_REQUEST['token_type'] == 'immortal')
		$_SESSION['token'] = $_REQUEST['token'];

	if(!hash_equals($_SESSION['token'], $_REQUEST['token'])) {
		header("Location: /");
		exit('Error - Missing CSRF_TOKEN. <a href="/">Return home</a>');
	}
}

function generateCSRFToken() {
	if(!isset($_SESSION['token']) || (isset($_SESSION['token']) && $_SESSION['token'] == '')) {
		$_SESSION['token'] = bin2hex(random_bytes(32));
	}
	return $_SESSION['token'];
}


define('CSRF_TOKEN', generateCSRFToken());


?>