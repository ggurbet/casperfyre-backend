<?php
include_once('../../core.php');
/**
 *
 * POST /user/submit-mfa
 *
 * @param string $mfa_code
 * @param string $guid
 *
 */
class UserSubmitMfa extends Endpoints {
	function __construct(
		$mfa_code = '',
		$guid = ''
	) {
		global $db, $helper;

		require_method('POST');

		$mfa_code = parent::$params['mfa_code'] ?? null;
		$guid = parent::$params['guid'] ?? null;

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

				/* get the rest of the user array */
				$query = "
					SELECT guid, email, first_name, last_name, role, password, twofa, verified, last_ip, company, admin_approved, deny_reason
					FROM users
					WHERE guid = '$guid'
				";
				$result = $db->do_select($query);
				$result = $result[0] ?? null;

				_exit(
					'success',
					array(
						'bearer' => $bearer,
						'guid' => $guid,
						'user' => $result
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
	}
}
new UserSubmitMfa();