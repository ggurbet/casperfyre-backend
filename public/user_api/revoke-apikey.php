<?php
/**
 *
 * POST /user/revoke-apikey
 *
 * HEADER Authorization: Bearer
 *
 * @param api_key   string
 *
 */
include_once('../../core.php');

global $db, $helper;

require_method('POST');
$auth = authenticate_session();
$guid = $auth['guid'] ?? '';
$params = get_params();
$provided_api_key = $params['api_key'] ?? '';

$query = "
	UPDATE api_keys
	SET active = 0
	WHERE guid = '$guid'
	AND api_key = '$provided_api_key'
";

$result = $db->do_query($query);

if($result) {
	_exit(
		'success',
		'Your api key has been frozen'
	);
}

_exit(
	'error',
	'Failed to revoke your api key. Please verify your request',
	400,
	'Failed to revoke an api key'
);