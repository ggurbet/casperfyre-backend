<?php
include_once('../../core.php');
/**
 *
 * POST /user/resend-code
 *
 * HEADER Authorization: Bearer
 *
 */
class UserResendCode extends Endpoints {
	function __construct() {
		global $db, $helper;

		require_method('POST');
		$auth = authenticate_session();
		$guid = $auth['guid'] ?? '';

		$query = "
			SELECT email, verified, confirmation_code
			FROM users
			WHERE guid = '$guid'
		";

		$selection = $db->do_select($query);
		$already_verified = (int)($selection[0]['verified'] ?? 0);
		$fetched_confirmation_code = $selection[0]['confirmation_code'] ?? null;
		$email = $selection[0]['email'] ?? null;

		if($already_verified === 1) {
			_exit(
				'success',
				'Already confirmed registration'
			);
		}

		if($fetched_confirmation_code) {
			$recipient = $email;
			$subject = 'Welcome to CasperFyre';
			$body = 'Welcome to CasperFyre. Your registration code is below:<br><br>';
			$link = $fetched_confirmation_code; 

			$helper->schedule_email(
				'register',
				$recipient,
				$subject,
				$body,
				$link
			);

			_exit(
				'success',
				'Successfully sent account confirmation code'
			);
		}

		_exit(
			'error',
			'Failed to re-send confirmation code',
			400,
			'Failed to re-send confirmation code'
		);
	}
}
new UserResendCode();