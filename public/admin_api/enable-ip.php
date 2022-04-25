<?php
/**
 *
 * POST /admin/enable-ip
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
	SET active = 1
	WHERE id = $ip_id
";
$result = $db->do_query($query);

if($result) {
	_exit(
		'success',
		'Successfully enabled IP address'
	);
}

_exit(
	'error',
	'Failed to enable IP address',
	400,
	'Failed to enable IP address'
);