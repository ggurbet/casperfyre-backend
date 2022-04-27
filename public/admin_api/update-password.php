<?php
/**
 *
 * PUT /admin/update-password
 *
 * HEADER Authorization: Bearer
 *
 * @param string  new_password
 * @param string  mfa_code
 *
 */
include_once('../../core.php');

global $db, $helper;

require_method('PUT');
$auth = authenticate_session(2);
$guid = $auth['guid'] ?? '';
$twofa_on = (int)($auth['twofa'] ?? 0);
$params = get_params();
$new_password = $params['new_password'] ?? '';
$mfa_code = $params['mfa_code'] ?? '';

if(
	strlen($new_password) < 8 ||
	!preg_match('/[\'\/~`\!@#\$%\^&\*\(\)_\-\+=\{\}\[\]\|;:"\<\>,\.\?\\\]/', $new_password) ||
	!preg_match('/[0-9]/', $new_password)
) {
	_exit(
		'error',
		'Password must be at least 8 characters long, contain at least one (1) number, and one (1) special character',
		400,
		'Invalid password. Does not meet complexity requirements'
	);
}

// check 2fa code, if on
if($twofa_on == 1) {
	if(!$mfa_code) {
		_exit(
			'error',
			'MFA code required for changing password. Please try again',
			400,
			'MFA code missing from request'
		);
	}

	$verified = $helper->verify_mfa($guid, $mfa_code);

	if($verified == 'expired') {
		_exit(
			'error',
			'MFA code expired. Please try updating your settings again',
			400,
			'MFA code expired'
		);
	}

	if($verified == 'incorrect') {
		_exit(
			'error',
			'MFA code incorrect',
			400,
			'MFA code incorrect'
		);
	}
}

$new_password_hash = hash('sha256', $new_password);

// check existing
$query = "
	SELECT password
	FROM users
	WHERE guid = '$guid'
";
$check = $db->do_select($query);
$check = $check[0] ?? null;
$fetched_password_hash = $check[0]['password'] ?? '';

if($new_password_hash == $fetched_password_hash) {
	_exit(
		'error',
		'Cannot use the same password as before',
		400,
		'Cannot use the same password as before'
	);
}

// update
$query = "
	UPDATE users
	SET password = '$new_password_hash'
	WHERE guid = '$guid'
";
$db->do_query($query);

_exit(
	'success',
	'Successfully updated password'
);
