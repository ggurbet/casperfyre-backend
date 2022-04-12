<?php
/**
 *
 * GET /user/get-ips
 *
 * HEADER Authorization: Bearer
 *
 */
include_once('../../core.php');

global $db, $helper;

require_method('GET');
$auth = authenticate_session();
$guid = $auth['guid'] ?? '';

$query = "
	SELECT id, ip, created_at
	FROM ips
	WHERE guid = '$guid'
";

$selection = $db->do_select($query);

_exit(
	'success',
	$selection
);
