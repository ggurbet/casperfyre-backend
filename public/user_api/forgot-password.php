<?php
include_once('../../core.php');

global $db, $helper;

require_method('POST');
$params = get_params();
$email = $params['email'] ?? '';

if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	_exit(
		'error',
		'Invalid email address',
		400,
		'Invalid email address'
	);
}

$query = "
	SELECT guid, email, confirmation_code
	FROM users
	WHERE email = '$email'
";
$selection = $db->do_select($query);
$selection = $selection[0];
$guid = $selection['guid'] ?? '';
$email = $selection['email'] ?? '';
$confirmation_code = $selection['confirmation_code'] ?? '';
$uri = $helper->aes_encrypt($guid.'::'.$confirmation_code.'::'.(string)time());

$subject = 'CasperFYRE - Forgot Password';
$body = 'You are receiving this email because we received a password reset request for your account. Please follow the link below to reset your password.';
$link = 'https://'.getenv('FRONTEND_URL').'/reset-password/'.$uri;

$helper->schedule_email(
	'forgot-password',
	$email,
	$subject,
	$body,
	$link
);

_exit(
	'success',
	'Please check you email for a reset link'
);
