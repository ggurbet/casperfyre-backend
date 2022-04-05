<?php
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
		400
	);
}

if($guid) {
	$query = "
		UPDATE users
		SET email = '$new_email'
		WHERE guid = '$guid'
	";
	$db->do_query($query);

	_exit(
		'success',
		'Successfully updated email'
	);
}

_exit(
	'error',
	'Failed to update email',
	400
);
