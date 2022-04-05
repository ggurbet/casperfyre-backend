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
		400
	);
}

if(!$password) {
	_exit(
		'error',
		'Please provide a password',
		400
	);
}

$query = "
	SELECT guid, email, password
	FROM users
	WHERE email = '$email'
";

$result = $db->do_select($query);
$guid = $result[0]['guid'] ?? '';
$fetched_password_hash = $result[0]['password'] ?? '';
$password_hash = hash('sha256', $password);

if(!hash_equals($fetched_password_hash, $password_hash)) {
	_exit(
		'error',
		'Invalid email or password',
		401
	);
}

$bearer = $helper->generate_session_token();
$created_at = $helper->get_datetime();
$expires_at = $helper->get_datetime(86400); // one day from now

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
