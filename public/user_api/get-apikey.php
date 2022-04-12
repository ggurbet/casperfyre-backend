<?php
/**
 *
 * GET /user/get-apikey
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
	SELECT api_key, active
	FROM api_keys
	WHERE guid = '$guid'
	AND active = 1
";

$selection = $db->do_select($query);
$selection = $selection[0]['api_key'] ?? null;

if($selection) {
	_exit(
		'success',
		$selection
	);
}

_exit(
	'error',
	'Could not retreive your api key',
	404,
	'Could not retreive api key'
);