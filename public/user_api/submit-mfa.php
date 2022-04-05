<?php
include_once('../../core.php');

global $db, $helper;

require_method('POST');
$params = get_params();
$mfa_code = $params['mfa_code'] ?? null;
$guid = $params['guid'] ?? null;

$query = "
	SELECT code
	FROM twofa
	WHERE guid = '$guid'
	AND code = '$mfa_code'
";
$selection = $db->do_select($query);
$selection = $selection[0]['code'];

if($mfa_code == $selection) {
	$query = "
		DELETE FROM twofa
		WHERE guid = '$guid'
	";
	$db->do_query($query);

	$bearer = $helper->generate_session_token();
	$created_at = $helper->get_datetime();
	$expires_at = $helper->get_datetime(86400); // one day from now

	$query1 = "
		DELETE FROM sessions
		WHERE guid = '$guid'
	";

	$query2 = "
		INSERT INTO sessions (
		guid,
		bearer,
		created_at,
		expires_at
		) VALUES (
		'$guid',
		'$bearer',
		'$created_at',
		'$expires_at'
		)
	";

	$db->do_query($query1);
	$db->do_query($query2);

	_exit(
		'success',
		$bearer
	);
}

_exit(
	'error',
	'MFA code incorrect. Please re-enter your code'
);