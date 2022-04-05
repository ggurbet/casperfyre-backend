<?php
include_once('../../core.php');

global $db, $helper;

require_method('POST');
$auth = authenticate_session(2);
$admin_guid = $auth['guid'] ?? '';
$params = get_params();
$user_guid = $params['guid'] ?? '';
$deny_reason = $params['deny_reason'] ?? '';
$created_at = $helper->get_datetime();

if($user_guid) {
	$query = "
		UPDATE users
		SET admin_approved = 2, deny_reason = '$deny_reason'
		WHERE guid = '$user_guid'
	";
	$db->do_query($query);

	_exit(
		'success',
		'Successfully denied user'
	);
}

_exit(
	'error',
	'Error denying user',
	500
);
