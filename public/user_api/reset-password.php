<?php
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
	strlen($new_password) < 8
) {
	_exit(
		'error',
		'Invalid new password. Must be at least 8 characters',
		400
	);
}

$uri = $helper->aes_decrypt($hash);
$guid = $uri[0];
$confirmation_code = $uri[1] ?? '';
$time = $uri[2] ?? '';

if($time) {
	$query = "
		SELECT guid, email, confirmation_code, password
		FROM users
		WHERE guid = '$guid'
	";
	$selection = $db->do_select($query);

	if($selection) {
		if($new_password_hash == $password) {
			_exit(
				'error',
				'Cannot use the same password as before',
				400
			);
		}

		if($confirmation_code != $selection[0]['confirmation_code']) {
			_exit(
				'error',
				'Error resetting password. Not authorized',
				401
			);
		}

		if($email != $selection[0]['email']) {
			_exit(
				'error',
				'Error resetting password. Not authorized',
				401
			);
		}

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
				500
			);
		}
	}
}

_exit(
	'error',
	'There was a problem resetting your password',
	400
);
