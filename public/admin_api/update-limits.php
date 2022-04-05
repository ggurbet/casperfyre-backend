<?php
include_once('../../core.php');

global $db, $helper;

require_method('PUT');
$auth = authenticate_session(2);
$admin_guid = $auth['guid'] ?? '';
$params = get_params();
$user_guid = $params['guid'] ?? '';
$per_limit = $params['per_limit'] ? (int)$params['per_limit'] : null;
$day_limit = $params['day_limit'] ? (int)$params['day_limit'] : null;
$month_limit = $params['month_limit'] ? (int)$params['month_limit'] : null;
$message = '';

if(gettype($per_limit) == 'integer') {
	$query = "
		UPDATE limits
		SET per_limit = $per_limit
		WHERE guid = '$user_guid'
	";
	$db->do_query($query);
	$message .= "Daily limit is now $per_limit. ";
}

if(gettype($day_limit) == 'integer') {
	$query = "
		UPDATE limits
		SET day_limit = $day_limit
		WHERE guid = '$user_guid'
	";
	$db->do_query($query);
	$message .= "Daily limit is now $day_limit. ";
}

if(gettype($month_limit) == 'integer') {
	$query = "
		UPDATE limits
		SET month_limit = $month_limit
		WHERE guid = '$user_guid'
	";
	$db->do_query($query);
	$message .= "Monthly limit is now $month_limit. ";
}

_exit(
	'success',
	'Updated cspr limits of this user. '.$message
);
