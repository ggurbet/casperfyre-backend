<?php
include_once('../../core.php');
/**
 *
 * POST /user/submit-mfa
 *
 * MFA for guest users. eg. Login
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

		$mfa_code = (string)(parent::$params['mfa_code'] ?? '');
		$guid = parent::$params['guid'] ?? null;
		$AUTHENTICATED = false;

		// get mfa type first
		$query = "
			SELECT totp
			FROM users
			WHERE guid = '$guid'
		";
		$totp_on = $db->do_select($query);
		$totp_on = (int)($totp_on[0]['totp'] ?? 0);

		// totp mfa type
		if($totp_on == 1) {
			$query = "
				SELECT *
				FROM totp_logins
				WHERE guid = '$guid'
			";
			$totp = $db->do_select($query);
			$expires_at = $totp[0]['expires_at'] ?? 0;

			if((int)time() > $expires_at) {
				$query = "
					DELETE FROM totp_logins
					WHERE guid = '$guid'
				";
				$db->do_query($query);

				_exit(
					'error',
					'Authenticator code expired. Please try logging back in',
					400,
					'Login Authenticator code expired.'
				);
			}

			$verified = Totp::check_code($guid, $mfa_code);

			if($verified) {
				$query = "
					DELETE FROM totp_logins
					WHERE guid = '$guid'
				";
				$db->do_query($query);
				$AUTHENTICATED = true;
			}
		}

		// email mfa type
		else {
			$query = "
				SELECT code, created_at
				FROM twofa
				WHERE guid = '$guid'
				AND code = '$mfa_code'
			";
			$selection = $db->do_select($query);
			$fetched_code = $selection[0]['code'] ?? '';
			$created_at = $selection[0]['created_at'] ?? 0;
			$expire_time = $helper->get_datetime(-300); // 5 minutes ago

			if($selection && $mfa_code == $fetched_code) {
				if($expire_time < $created_at) {
					$query = "
						DELETE FROM twofa
						WHERE guid = '$guid'
					";
					$db->do_query($query);

					$AUTHENTICATED = true;
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
						'Login MFA code expired'
					);
				}
			}
		}

		if($AUTHENTICATED) {
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
				SELECT guid, email, first_name, last_name, role, password, twofa, totp, verified, last_ip, company, admin_approved, deny_reason
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