<?php
/**
 *
 * PUT /admin/update-limits
 *
 * HEADER Authorization: Bearer
 *
 * @param string  guid
 * @param int     per_limit
 * @param int     day_limit
 * @param int     month_limit
 *
 * If a parameter is not specified, then it will not be affected.
 */
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

$query = "
	SELECT id
	FROM users
	WHERE guid = '$user_guid'
";
$check = $db->do_select($query);

if(!$check) {
	_exit(
		'error',
		'User does not exist',
		400,
		'User does not exist'
	);
}

if(gettype($per_limit) == 'integer') {
	$query = "
		UPDATE limits
		SET per_limit = $per_limit
		WHERE guid = '$user_guid'
	";
	$db->do_query($query);
	$message .= "Per request limit is now $per_limit. ";
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
