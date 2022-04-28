<?php
include_once('../../core.php');
/**
 *
 * POST /user/confirm-registration
 *
 * HEADER Authorization: Bearer
 *
 * @param string $confirmation_code
 *
 */
class UserConfirmRegistration extends Endpoints {
	function __construct(
		$confirmation_code = ''
	) {
		global $db, $helper;

		require_method('POST');

		$auth = authenticate_session();
		$guid = $auth['guid'] ?? '';
		$confirmation_code = parent::$params['confirmation_code'] ?? '';

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

			/* send confirmation welcome email */
			$query = "
				SELECT email, first_name, last_name
				FROM users
				WHERE guid = '$guid'
			";
			$selection = $db->do_select($query);
			$first_name = $selection[0]['first_name'] ?? '';
			$user_email = $selection[0]['email'] ?? '';
			$subject = 'CasperFYRE Registration Confirmed';
			$body = 'Hello'.($first_name ? ', '.$first_name : '').'! You have successfully registered with CasperFYRE. From here, an admin will need to approved your account before you can start using its features. Please allow up to 24 hours for an update emailed to you. If you have any questions or concerns, you can contact '.getenv('ADMIN_EMAIL');

			if($user_email) {
				$helper->schedule_email(
					'welcome',
					$user_email,
					$subject,
					$body
				);
			}

			_exit(
				'success',
				'Successfully confirmed registration'
			);
		}

		_exit(
			'error',
			'Failed to register user',
			400,
			'Failed to register user'
		);
	}
}
new UserConfirmRegistration();