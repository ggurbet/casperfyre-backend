<?php
include_once('../../core.php');

global $db, $helper;

require_method('GET');
$auth = authenticate_session(2);
$admin_guid = $auth['guid'] ?? '';
$user_guid = _request('guid');

$query = "
	SELECT id, ip, active, created_at
	FROM ips
	WHERE guid = '$user_guid'
";
$selection = $db->do_select($query);

_exit(
	'success',
	$selection
);
