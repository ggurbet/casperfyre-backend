<?php
/**
 *
 * POST /user/submit-mfa
 *
 * @param mfa_code  string
 * @param guid      string
 *
 */
include_once('../../core.php');

global $db, $helper;

require_method('POST');
$params = get_params();
$mfa_code = $params['mfa_code'] ?? null;
$guid = $params['guid'] ?? null;

$query = "
	SELECT code, created_at
	FROM twofa
	WHERE guid = '$guid'
	AND code = '$mfa_code'
";
$selection = $db->do_select($query);
$fetched_code = $selection[0]['code'] ?? '';
$created_at = $selection[0]['created_at'] ?? 0;
$expire_time = $helper->get_datetime(-600); // 10 minutes ago

if($selection && $mfa_code == $fetched_code) {
	if($expire_time < $created_at) {
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
			array(
				'bearer' => $bearer
				'guid' => $guid
			)
		);
	} else {
		$query = "
			DELETE FROM twofa
			WHERE guid = '$guid'
		";
		$db->do_query($query);

		_exit(
			'error',
			'MFA code expired. Please try logging back in',
			400,
			'MFA code expired'
		);
	}
}

_exit(
	'error',
	'MFA code incorrect. Please re-enter your code',
	400,
	'MFA code incorrect'
);