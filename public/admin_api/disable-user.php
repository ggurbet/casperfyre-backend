<?php
/**
 *
 * POST /admin/disable-user
 *
 * HEADER Authorization: Bearer
 *
 * @param string  guid
 */
include_once('../../core.php');

global $db, $helper;

require_method('POST');
$auth = authenticate_session(2);
$params = get_params();
$guid = $params['guid'] ?? '';

if($guid) {
	/* auth check */
	$query = "
		SELECT role, email
		FROM users
		WHERE guid = '$guid'
	";
	$check = $db->do_select($query);
	$role = $check[0]['role'] ?? '';
	$email = $check[0]['email'] ?? '';

	if(!$check) {
		_exit(
			'error',
			'Invalid guid',
			400,
			'Invalid guid'
		);
	}

	if(
		$role == 'admin' ||
		$role == 'sub-admin'
	) {
		// require clearance level 3 if altering an admin role
		$auth = authenticate_session(3);
	}

	/* prevent banning self */
	if($guid == $auth['role']) {
		_exit(
			'error',
			'Cannot disable yourself',
			400,
			'Admin cannot disable oneself'
		);
	}

	$query = "
		UPDATE users
		SET admin_approved = 0
		WHERE guid = '$guid'
	";
	$db->do_query($query);

	_exit(
		'success',
		'You disabled '.$email
	);
}

_exit(
	'error',
	'Please provide guid of the user to disable',
	400
);
