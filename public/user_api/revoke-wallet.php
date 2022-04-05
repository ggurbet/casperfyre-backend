<?php
include_once('../../core.php');

global $db, $helper;

require_method('POST');
$auth = authenticate_session();
$guid = $auth['guid'] ?? '';
$params = get_params();
$address = $params['address'] ?? '';

$query = "
	UPDATE wallets
	SET active = 0
	WHERE address = '$address'
	AND guid = '$guid'
";

$result = $db->do_query($query);

if($result) {
	_exit(
		'success',
		'Successfully revoked wallet address'
	);
}

_exit(
	'error',
	'Failed to revoke this wallet',
	500
);