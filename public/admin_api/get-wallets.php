<?php
/**
 *
 * GET /admin/get-wallets
 *
 * HEADER Authorization: Bearer
 *
 * @param string  guid
 */
include_once('../../core.php');

global $db, $helper;

require_method('GET');
$auth = authenticate_session(2);
$admin_guid = $auth['guid'] ?? '';
$user_guid = _request('guid');

$query = "
	SELECT guid, active, created_at, inactive_at, address, balance
	FROM wallets
	WHERE guid = '$user_guid'
";
$selection = $db->do_select($query);

_exit(
	'success',
	$selection
);
