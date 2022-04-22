<?php
/**
 *
 * GET /admin/history
 *
 * HEADER Authorization: Bearer
 *
 * @param string  guid
 */
include_once('../../core.php');

global $helper, $db;

require_method('GET');
$auth = authenticate_session(2);
$admin_guid = $auth['guid'] ?? '';
$user_guid = _request('guid');

$query = "
	SELECT * 
	FROM orders
	WHERE guid = '$user_guid'
";
$results = $db->do_select($query);

_exit(
	'success',
	$results
);
