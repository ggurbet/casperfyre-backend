<?php
/**
 *
 * POST /user/create-ip
 *
 * HEADER Authorization: Bearer
 *
 * @param ip  string
 *
 */
include_once('../../core.php');

global $db, $helper;

require_method('POST');
$auth = authenticate_session();
$guid = $auth['guid'] ?? '';
$params = get_params();
$ip = $params['ip'] ?? '';
$ip = preg_replace("/([^A-Fa-f0-9.\/])/", '', $ip);
$created_at = $helper->get_datetime();

$query = "
	INSERT INTO ips (
	guid,
	ip,
	created_at
	) VALUES (
	'$guid',
	'$ip',
	'$created_at'
	)
";

$result = $db->do_query($query);

if($result) {
	_exit(
		'success',
		'Successfully added IP to whitelist'
	);
}

_exit(
	'error',
	'Failed to add IP to whitelist',
	500,
	'Failed to add IP to whitelist'
);