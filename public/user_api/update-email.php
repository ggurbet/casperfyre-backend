<?php
/**
 *
 * POST /user/update-email
 *
 * HEADER Authorization: Bearer
 *
 * @param new_email  string
 *
 */
include_once('../../core.php');

global $db, $helper;

require_method('POST');
$auth = authenticate_session();
$guid = $auth['guid'] ?? '';
$params = get_params();
$new_email = $params['new_email'] ?? '';

if(!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
	_exit(
		'error',
		'Invalid email address',
		400,
		'Invalid email address'
	);
}

if($guid) {
	// check new email
	$check_query = "
		SELECT email
		FROM users
		WHERE email = '$new_email'
	";
	$check = $db->do_select($check_query);

	if($check) {
		_exit(
			'error',
			'New email address specified is already in use',
			400,
			'New email address specified is already in use'
		);
	}

	$query = "
		UPDATE users
		SET email = '$new_email'
		WHERE guid = '$guid'
	";
	$db->do_query($query);

	//// kill all reset tokens and session tokens associated with old email

	_exit(
		'success',
		'Successfully updated email'
	);
}

_exit(
	'error',
	'Failed to update email',
	400,
	'Failed to update email'
);
