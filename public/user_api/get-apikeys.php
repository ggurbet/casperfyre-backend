<?php
/**
 *
 * GET /user/get-apikeys
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
	SELECT api_key, active, total_calls, created_at
	FROM api_keys
	WHERE guid = '$guid'
";

$selection = $db->do_select($query);

_exit(
	'success',
	$selection
);
