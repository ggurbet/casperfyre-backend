<?php
/**
 *
 * POST /admin/create-apikey
 *
 * HEADER Authorization: Bearer
 *
 * @param string  guid
 */
include_once('../../core.php');

global $db, $helper;

require_method('POST');
$auth = authenticate_session(2);
$admin_guid = $auth['guid'] ?? '';
$params = get_params();
$user_guid = $params['guid'] ?? '';
$new_api_key = $helper->generate_apikey();
$created_at = $helper->get_datetime();

$check_query = "
	SELECT guid
	FROM users
	WHERE guid = '$user_guid'
";
$check = $db->do_select($check_query);

if(!$check) {
	_exit(
		'error',
		'User does not exist',
		400,
		'User does not exist'
	);
}

$query = "
	UPDATE api_keys
	SET active = 0
	WHERE guid = '$user_guid'
";

$db->do_query($query);

$query = "
	INSERT INTO api_keys (
	guid,
	api_key,
	created_at
	) VALUES (
	'$user_guid',
	'$new_api_key',
	'$created_at'
	)
";

$result = $db->do_query($query);

if($result) {
	_exit(
		'success',
		$new_api_key
	);
}

_exit(
	'error',
	'Failed to a create new api key',
	500,
	'Failed to a create new api key'
);