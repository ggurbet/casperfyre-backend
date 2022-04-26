<?php
/**
 *
 * POST /user/register
 *
 * @param email             string
 * @param first_name        string
 * @param last_name         string
 * @param password          string
 * @param company           string
 * @param description       string
 * @param cspr_expectation  int
 *
 */
include_once('../../core.php');

global $db, $helper;

require_method('POST');
$params = get_params();
$email = $params['email'] ?? '';
$first_name = $params['first_name'] ?? '';
$last_name = $params['last_name'] ?? '';
$password = $params['password'] ?? '';
$company = $params['company'] ?? '';
$description = $params['description'] ?? '';
$cspr_expectation = isset($params['cspr_expectation']) ? (int)$params['cspr_expectation'] : 0;

/* For live tests */
$phpunittesttoken = $params['phpunittesttoken'] ?? '';

if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	_exit(
		'error',
		'Invalid email address',
		400,
		'Invalid email address'
	);
}

if(!$first_name || $first_name == '') {
	_exit(
		'error',
		'Please provide first name',
		400,
		'Failed to provide first name'
	);
}

if(!$last_name || $last_name == '') {
	_exit(
		'error',
		'Please provide last name',
		400,
		'Failed to provide last name'
	);
}

if(
	strlen($first_name) > 32 ||
	strlen($last_name) > 32 ||
	preg_match('/[\/~`\!@#\$%\^&\*\(\)_\+=\{\}\[\]\|;:"\<\>,\.\?\\\]/', $first_name) ||
	preg_match('/[\/~`\!@#\$%\^&\*\(\)_\+=\{\}\[\]\|;:"\<\>,\.\?\\\]/', $last_name)
) {
	_exit(
		'error',
		'There are invalid characters in your name',
		400,
		'Invalid characters in registration first_name/last_name'
	);
}

if(strlen($description) > 2048) {
	_exit(
		'error',
		'Description is too long. Limit 2000 characters',
		400,
		'Description is too long. Limit 2000 characters'
	);
}

if(!$password || $password == '') {
	_exit(
		'error',
		'Please provide a valid password',
		400,
		'Failed to provide password'
	);
}

if(
	strlen($password) < 8 ||
	!preg_match('/[\'\/~`\!@#\$%\^&\*\(\)_\-\+=\{\}\[\]\|;:"\<\>,\.\?\\\]/', $password) ||
	!preg_match('/[0-9]/', $password)
) {
	_exit(
		'error',
		'Password must be at least 8 characters long, contain at least one (1) number, and one (1) special character',
		400,
		'Invalid password. Does not meet complexity requirements'
	);
}

/* check pre-existing email */
$query = "
	SELECT guid
	FROM users
	WHERE email = '$email'
";
$check = $db->do_select($query);

if($check) {
	_exit(
		'error',
		'An account with this email address already exists',
		400,
		'An account with this email address already exists'
	);
}

if(
	$phpunittesttoken &&
	$phpunittesttoken == 'phpunittesttoken'
) {
	$guid = '00000000-0000-0000-4c4c-000000000000';
	$role = 'test-user';
	$do_email = false;
} else {
	$guid = $helper->generate_guid();
	$role = 'user';
	$do_email = true;
}

$created_at = $helper->get_datetime();
$confirmation_code = $helper->generate_hash(6);
$password_hash = hash('sha256', $password);
$registration_ip = $helper->get_real_ip();

$query_users = "
	INSERT INTO users (
	guid, 
	role,
	email, 
	first_name, 
	last_name, 
	password, 
	created_at, 
	confirmation_code,
	last_ip,
	company,
	description,
	cspr_expectation
	) VALUES (
	'$guid',
	'$role',
	'$email',
	'$first_name',
	'$last_name',
	'$password_hash',
	'$created_at',
	'$confirmation_code',
	'$registration_ip',
	'$company',
	'$description',
	$cspr_expectation
	)
";

/* create default api limits */
$query_limits = "
	INSERT INTO limits (
	guid,
	day_limit,
	week_limit,
	month_limit
	) VALUES (
	'$guid',
	0,
	0,
	0
	)
";

/* log first ip into whitelist */
$query_ips = "
	INSERT INTO ips (
	guid,
	ip,
	created_at
	) VALUES (
	'$guid',
	'$registration_ip',
	'$created_at'
	)
";

/* create session */
$bearer = $helper->generate_session_token();
$expires_at = $helper->get_datetime(86400);
$query_sessions = "
	INSERT INTO sessions (
	guid,
	bearer,
	created_at,
	expires_at
	) VALUES (
	'$guid',
	'$bearer',
	'$created_at',
	'$expires_at'
	)
";

/* execute queries with failsafe */
$result_users = $db->do_query($query_users);
$result_limits = $db->do_query($query_limits);
$result_ips = $db->do_query($query_ips);
$result_sessions = $db->do_query($query_sessions);

if(
	!$result_users ||
	!$result_limits ||
	!$result_ips ||
	!$result_sessions
) {
	$query = "
		DELETE FROM users
		WHERE guid = '$guid'
	";
	$db->do_query($query);
	$query = "
		DELETE FROM wallets
		WHERE guid = '$guid'
	";
	$db->do_query($query);
	$query = "
		DELETE FROM limits
		WHERE guid = '$guid'
	";
	$db->do_query($query);
	$query = "
		DELETE FROM ips
		WHERE guid = '$guid'
	";
	$db->do_query($query);
	$query = "
		DELETE FROM sessions
		WHERE guid = '$guid'
	";
	$db->do_query($query);

	_exit(
		'error',
		'Failed to register user. Please contact administration',
		500,
		'Failed to register user',
	);
}

/* get new user */
$selection = "
	SELECT
	a.guid, a.role, a.email, a.verified, a.first_name, a.last_name,
	a.company, a.description, a.cspr_expectation, a.admin_approved,
	a.api_key_active, a.created_at, a.last_ip, b.bearer
	FROM users AS a
	LEFT JOIN sessions AS b
	ON a.guid = b.guid
	WHERE a.guid = '$guid'
";

$me = $db->do_select($selection);
$me = $me[0] ?? null;

if($me) {
	$recipient = $email;
	$subject = 'Welcome to CasperFyre';
	$body = 'Welcome to CasperFyre. Your registration code is below:<br><br>';
	$link = $confirmation_code; 

	if($do_email) {
		$helper->schedule_email(
			'register',
			$recipient,
			$subject,
			$body,
			$link
		);
	}

	_exit(
		'success',
		$me
	);
}

_exit(
	'error',
	'Failed to register user',
	500,
	'Failed to register user'
);
