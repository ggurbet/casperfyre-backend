<?php
/**
 *
 * POST /user/reset-password
 *
 * @param email         string
 * @param hash          string  - Hash from email link
 * @param new_password  string
 *
 */
include_once('../../core.php');

global $db, $helper;

require_method('POST');
$params = get_params();
$hash = $params['hash'] ?? '';
$email = $params['email'] ?? '';
$new_password = $params['new_password'] ?? '';
$new_password_hash = hash('sha256', $new_password);

if(
	!$new_password ||
	strlen($new_password) < 8 ||
	!preg_match('/[\'\/~`\!@#\$%\^&\*\(\)_\-\+=\{\}\[\]\|;:"\<\>,\.\?\\\]/', $new_password) ||
	!preg_match('/[0-9]/', $new_password)
) {
	_exit(
		'error',
		'Invalid new password. Must be at least 8 characters long, contain at least one (1) special character, and one (1) number',
		400,
		'Invalid new password. Failed complexity requirements'
	);
}

$uri = $helper->aes_decrypt($hash);
$uri = explode('::', $uri);
$guid = $uri[0];
$confirmation_code = $uri[1] ?? '';
$time = (int)($uri[2] ?? 0);
$reset_auth_code = $uri[3] ?? null;
$from_admin = $uri[4] ?? null;

// check reset auth code
$query = "
	SELECT guid, code
	FROM password_resets
	WHERE code = '$reset_auth_code'
";
$auth_code_check = $db->do_select($query);

if(!$auth_code_check) {
	_exit(
		'error',
		'Password reset code is expired. Please try again',
		401,
		'Password reset code is expired'
	);
}

/* check for expired password reset token */
$expire_time = 600; // 10 minutes for user reset

if($from_admin && $from_admin == 'admin') {
	$expire_time = 86400;  // 24 hours for admin reset
}

if($time < (int)time() - $expire_time) {
	_exit(
		'error',
		'Password reset token is expired. Please try again',
		401,
		'Password reset token is expired'
	);
}

/* final confirmation check and reset */
$query = "
	SELECT guid, email, confirmation_code, password
	FROM users
	WHERE guid = '$guid'
";
$selection = $db->do_select($query);

if($selection) {
	$fetched_password_hash = $selection[0]['password'] ?? '';
	$fetched_confirmation_code = $selection[0]['confirmation_code'] ?? '';
	$fetched_email = $selection[0]['email'] ?? '';

	if($new_password_hash == $fetched_password_hash) {
		_exit(
			'error',
			'Cannot use the same password as before',
			400,
			'Cannot use the same password as before'
		);
	}

	if($confirmation_code != $fetched_confirmation_code) {
		_exit(
			'error',
			'Error resetting password. Not authorized',
			401,
			'Error resetting password. Not authorized'
		);
	}

	if($email != $fetched_email) {
		_exit(
			'error',
			'Error resetting password. Not authorized',
			401,
			'Error resetting password. Not authorized'
		);
	}

	// clear reset code
	$query = "
		DELETE FROM password_resets
		WHERE guid = '$guid'
	";
	$db->do_query($query);

	// clear sessions
	$query = "
		DELETE FROM sessions
		WHERE guid = '$guid'
	";
	$db->do_query($query);

	$query = "
		UPDATE users
		SET password = '$new_password_hash'
		WHERE guid = '$guid'
		AND confirmation_code = '$confirmation_code'
	";
	$success = $db->do_query($query);

	if($success) {
		_exit(
			'success',
			'Successfully reset your password',
			200
		);
	} else {
		_exit(
			'error',
			'Error resetting password',
			500,
			'Error resetting password'
		);
	}
}

_exit(
	'error',
	'There was a problem resetting your password',
	400,
	'There was a problem resetting user password. Perhaps invalid reset link hash'
);
