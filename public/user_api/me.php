<?php
include_once('../../core.php');

global $db, $helper;

require_method('GET');
$auth = authenticate_session();
$guid = $auth['guid'] ?? '';

$query = "
	SELECT guid, role, email, verified, first_name, last_name, api_key_active, created_at
	FROM users
	WHERE guid = '$guid'
";

$me = $db->do_select($query);
$me = $me[0] ?? null;

if($me) {
	_exit(
		'success',
		$me
	);
}

_exit(
	'error',
	'Unauthorized',
	401,
	'Unauthorized'
);