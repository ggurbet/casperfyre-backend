<?php
include_once('../../core.php');

global $db, $helper;

require_method('POST');
$auth = authenticate_session();
$guid = $auth['guid'] ?? '';
$params = get_params();
$confirmation_code = $params['confirmation_code'] ?? '';

$query = "
	SELECT verified, confirmation_code
	FROM users
	WHERE guid = '$guid'
";

$selection = $db->do_select($query);
$already_verified = $selection[0]['verified'] ?? null;
$fetched_confirmation_code = $selection[0]['confirmation_code'] ?? null;

if($already_verified === 1) {
	_exit(
		'success',
		'Already confirmed registration'
	);
}

if(
	$confirmation_code &&
	$confirmation_code == $fetched_confirmation_code
) {
	$query = "
		UPDATE users
		SET verified = 1
		WHERE guid = '$guid'
	";
	$db->do_query($query);

	_exit(
		'success',
		'Successfully confirmed registration'
	);
}

_exit(
	'error',
	'Failed to register user',
	400
);
