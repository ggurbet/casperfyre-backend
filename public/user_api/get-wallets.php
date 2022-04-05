<?php
include_once('../../core.php');

global $db, $helper;

require_method('GET');
$auth = authenticate_session();
$guid = $auth['guid'] ?? '';

$query = "
	SELECT address, active, created_at
	FROM wallets
	WHERE guid = '$guid'
";

$selection = $db->do_select($query);

_exit(
	'success',
	$selection
);
