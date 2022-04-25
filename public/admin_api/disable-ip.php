<?php
/**
 *
 * POST /admin/disable-ip
 *
 * HEADER Authorization: Bearer
 *
 * @param int ip_id
 */
include_once('../../core.php');

global $db, $helper;

require_method('POST');
$auth = authenticate_session(2);
$admin_guid = $auth['guid'] ?? '';
$params = get_params();
$ip_id = (int)($params['ip_id'] ?? 0);

$query = "
	UPDATE ips
	SET active = 0
	WHERE id = $ip_id
";
$result = $db->do_query($query);

if($result) {
	_exit(
		'success',
		'Successfully disabled IP address'
	);
}

_exit(
	'error',
	'Failed to disable IP address',
	400,
	'Failed to disable IP address'
);