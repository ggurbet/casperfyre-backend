<?php
/**
 *
 * POST /user/revoke-ip
 *
 * HEADER Authorization: Bearer
 *
 * @param ipid   int  - ID of the CIDR. Returned when /user/get-ips is called.
 *
 */
include_once('../../core.php');

global $db, $helper;

require_method('POST');
$auth = authenticate_session();
$guid = $auth['guid'] ?? '';
$params = get_params();
$ipid = (int)($params['ipid'] ?? 0);

$query = "
	DELETE FROM ips
	WHERE id = $ipid
	AND guid = '$guid'
";

$result = $db->do_query($query);

if($result) {
	_exit(
		'success',
		'Successfully removed IP range from whitelist'
	);
}

_exit(
	'error',
	'Failed to remove IP from whitelist',
	500,
	'Failed to remove IP from whitelist'
);