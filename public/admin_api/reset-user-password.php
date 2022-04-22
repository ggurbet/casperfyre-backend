<?php
/**
 *
 * POST /admin/reset-user-password
 *
 * HEADER Authorization: Bearer
 *
 * @param string  guid
 */
include_once('../../core.php');

global $db, $helper;

require_method('POST');
$auth = authenticate_session(2);
$admin_guid = $auth['guid'] ?? '';
$params = get_params();
$user_guid = $params['guid'] ?? '';

$query = "
	SELECT guid, email, confirmation_code
	FROM users
	WHERE guid = '$user_guid'
";
$selection = $db->do_select($query);
$selection = $selection[0] ?? null;
$guid = $selection['guid'] ?? null;
$email = $selection['email'] ?? null;
$reset_auth_code = $helper->generate_hash();

if(
	$selection &&
	$guid &&
	$email
) {
	// record auth code so we can de-auth after single use
	$query = "
		INSERT INTO password_resets (
			guid,
			code
		) VALUES (
			'$guid',
			'$reset_auth_code'
		)
	";
	$db->do_query($query);

	$confirmation_code = $selection['confirmation_code'] ?? '';
	$uri = $helper->aes_encrypt($guid.'::'.$confirmation_code.'::'.(string)time().'::'.$reset_auth_code.'::admin');

	$subject = 'CasperFYRE - Forgot Password';
	$body = 'You are receiving this email because an admin has issued a password reset request for your account. Please follow the link below to reset your password. This password reset link will expire in 24 hours.';
	$link = 'https://'.getenv('FRONTEND_URL').'/reset-password/'.$uri.'?email='.$email;

	$helper->schedule_email(
		'forgot-password',
		$email,
		$subject,
		$body,
		$link
	);
}

_exit(
	'success',
	'Reset password email sent to the user'
);
