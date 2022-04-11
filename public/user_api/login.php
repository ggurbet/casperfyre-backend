<?php
include_once('../../core.php');

global $db, $helper;

require_method('POST');
$params = get_params();
$email = $params['email'] ?? null;
$password = $params['password'] ?? null;

if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	_exit(
		'error',
		'Invalid email address',
		400,
		'Invalid email address'
	);
}

if(!$password) {
	_exit(
		'error',
		'Please provide a password',
		400,
		'No password provided'
	);
}

$query = "
	SELECT guid, email, password, twofa
	FROM users
	WHERE email = '$email'
";

$result = $db->do_select($query);
$guid = $result[0]['guid'] ?? '';
$twofa = (int)($result[0]['twofa'] ?? 0);
$fetched_password_hash = $result[0]['password'] ?? '';
$password_hash = hash('sha256', $password);
$created_at = $helper->get_datetime();
$expires_at = $helper->get_datetime(86400); // one day from now

if(!hash_equals($fetched_password_hash, $password_hash)) {
	_exit(
		'error',
		'Invalid email or password',
		401,
		'Invalid email or password'
	);
}

/* check 2fa */
if($twofa == 1) {
	$code = $helper->generate_hash(6);

	$helper->schedule_email(
		'twofa',
		$email,
		'Two factor authentication',
		'Please find you 2fa code below to login to CasperFYRE. This code expires in 10 minutes.',
		$code
	);

	$query = "
		DELETE FROM twofa
		WHERE guid = '$guid'
	";
	$db->do_query($query);

	$query = "
		INSERT INTO twofa (
			guid,
			created_at,
			code
		) VALUES (
			'$guid',
			'$created_at',
			'$code'
		)
	";
	$db->do_query($query);

	_exit(
		'success',
		$guid
	);
}

$bearer = $helper->generate_session_token();

$query1 = "
	DELETE FROM sessions
	WHERE guid = '$guid'
";

$query2 = "
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

$db->do_query($query1);
$db->do_query($query2);

_exit(
	'success',
	$bearer
);
