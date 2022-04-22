<?php
include_once('../../core.php');

global $db, $helper;

require_method('GET');
$auth = authenticate_session(2);
$admin_guid = $auth['guid'] ?? '';
$params = get_params();
$user_guid = _request('guid');

$query = "
	SELECT guid, active, created_at, inactive_at, address, balance
	FROM wallets
	WHERE guid = '$user_guid'
	AND active = 1
";
$selection = $db->do_select($query);
$selection = $selection[0] ?? array();

_exit(
	'success',
	$selection
);
