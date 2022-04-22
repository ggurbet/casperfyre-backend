<?php
/**
 *
 * GET /admin/get-users
 *
 * HEADER Authorization: Bearer
 *
 */
include_once('../../core.php');

global $db, $helper;

require_method('GET');
$auth = authenticate_session(2);
$admin_guid = $auth['guid'] ?? '';

$query = "
	SELECT *
	FROM users
";
$selection = $db->do_select($query);

_exit(
	'success',
	$selection
);
