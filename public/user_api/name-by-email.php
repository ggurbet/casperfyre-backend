<?php
include_once('../../core.php');

global $db, $helper;

require_method('GET');
$email = _request('email');
elog($email);

if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	_exit(
		'error',
		'Invalid email address',
		400
	);
}

if(strlen($email) > 255) {
	_exit(
		'error',
		'Invalid email address',
		400
	);
}

$query = "
	SELECT first_name, last_name
	FROM users
	WHERE email = '$email'
";

$selection = $db->do_select($query);
$selection = $selection[0] ?? null;

if($selection) {
	_exit(
		'success',
		$selection
	);
}

_exit(
	'error',
	'User not found',
	404
);