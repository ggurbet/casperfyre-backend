<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/../core.php');
/**
 *
 * POST /admin/disable-user
 *
 * HEADER Authorization: Bearer
 *
 * @param string  $guid
 */
class AdminDisableUser extends Endpoints {
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
			$admin_html = 'user ';

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
				$admin_html = 'admin ';
			}

			if($admin_approved == 0) {
				_exit(
					'success',
					'User is already disabled'
				);
			}

			/* prevent banning self */
			if($guid == $auth['guid']) {
				_exit(
					'error',
					'Cannot disable yourself',
					400,
					'Admin cannot disable oneself'
				);
			}

			$query = "
				UPDATE users
				SET admin_approved = 0
				WHERE guid = '$guid'
			";
			$db->do_query($query);

			_exit(
				'success',
				'You disabled '.$admin_html.$email
			);
		}

		_exit(
			'error',
			'Please provide guid of the user to disable',
			400
		);
	}
}
new AdminDisableUser();