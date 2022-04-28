<?php
include_once('../../core.php');
/**
 *
 * PUT /admin/update-email
 *
 * HEADER Authorization: Bearer
 *
 * @param string  $new_email
 * @param string  $mfa_code
 *
 */
class AdminUpdateEmail extends Endpoints {
	function __construct(
		$new_email = '',
		$mfa_code = ''
	) {
		global $db, $helper;

		require_method('PUT');

		$auth = authenticate_session(2);
		$guid = $auth['guid'] ?? '';
		$twofa_on = (int)($auth['twofa'] ?? 0);
		$new_email = parent::$params['new_email'] ?? '';
		$mfa_code = parent::$params['mfa_code'] ?? '';

		if(!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
			_exit(
				'error',
				'Invalid email address',
				400,
				'Invalid email address'
			);
		}

		// check new email
		$check_query = "
			SELECT email
			FROM users
			WHERE email = '$new_email'
		";
		$check = $db->do_select($check_query);

		if($check) {
			_exit(
				'error',
				'New email address specified is already in use',
				400,
				'New email address specified is already in use'
			);
		}

		// check 2fa code, if on
		if($twofa_on == 1) {
			if(!$mfa_code) {
				_exit(
					'error',
					'MFA code required for changing email address. Please try again',
					400,
					'MFA code missing from request'
				);
			}

			$verified = $helper->verify_mfa($guid, $mfa_code);

			if($verified == 'expired') {
				_exit(
					'error',
					'MFA code expired. Please try updating your settings again',
					400,
					'MFA code expired'
				);
			}

			if($verified == 'incorrect') {
				_exit(
					'error',
					'MFA code incorrect',
					400,
					'MFA code incorrect'
				);
			}
		}

		$query = "
			UPDATE users
			SET email = '$new_email'
			WHERE guid = '$guid'
		";
		$db->do_query($query);

		//// kill all reset tokens and session tokens associated with old email

		_exit(
			'success',
			'Successfully updated email'
		);
	}
}
new AdminUpdateEmail();