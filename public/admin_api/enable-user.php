<?php
include_once('../../core.php');
/**
 *
 * POST /admin/enable-user
 *
 * HEADER Authorization: Bearer
 *
 * @param string  $guid
 */
class AdminEnableUser extends Endpoints {
	function __construct(
		$guid = ''
	) {
		global $db, $helper;

		require_method('POST');

		$auth = authenticate_session(2);
		$guid = parent::$params['guid'] ?? '';

		if($guid) {
			/* auth check */
			$query = "
				SELECT role, email, first_name, admin_approved
				FROM users
				WHERE guid = '$guid'
			";
			$check = $db->do_select($query);
			$role = $check[0]['role'] ?? '';
			$email = $check[0]['email'] ?? '';
			$first_name = $check[0]['first_name'] ?? '';
			$admin_approved = (int)($check[0]['admin_approved'] ?? 0);

			if(!$check) {
				_exit(
					'error',
					'Invalid guid',
					400,
					'Invalid guid'
				);
			}

			if(
				$role == 'admin' ||
				$role == 'sub-admin'
			) {
				// require clearance level 3 if altering an admin role
				$auth = authenticate_session(3);
			}

			if($admin_approved == 1) {
				_exit(
					'success',
					'User is already enabled'
				);
			}

			$query = "
				UPDATE users
				SET admin_approved = 1
				WHERE guid = '$guid'
			";
			$db->do_query($query);

			/* send email to re-activated user */
			$subject = APP_NAME.' Access Status';
			$body = 'Hello, '.$first_name.'. Your account has been <b>enabled</b> and granted dashboard access.<br><br>';

			if($email) {
				$helper->schedule_email(
					'approved',
					$email,
					$subject,
					$body,
					getenv('FRONTEND_URL').'/auth/login'
				);
			}

			_exit(
				'success',
				'You re-activated '.$email
			);
		}

		_exit(
			'error',
			'Please provide guid of the user to enable',
			400
		);
	}
}
new AdminEnableUser();