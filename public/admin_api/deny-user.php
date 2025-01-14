<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/../core.php');
/**
 *
 * POST /admin/deny-user
 *
 * HEADER Authorization: Bearer
 *
 * @param string $guid
 * @param string $deny_reason
 *
 */
class AdminDenyUser extends Endpoints {
	function __construct(
		$guid = '',
		$deny_reason = ''
	) {
		global $db, $helper;

		require_method('POST');

		$auth = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';
		$user_guid = parent::$params['guid'] ?? '';
		$deny_reason = parent::$params['deny_reason'] ?? '';
		$created_at = $helper->get_datetime();

		if($user_guid) {
			$query = "
				UPDATE users
				SET admin_approved = 2, deny_reason = '$deny_reason'
				WHERE guid = '$user_guid'
			";
			$db->do_query($query);

			/* send email to denied user */
			$query = "
				SELECT email, first_name
				FROM users
				WHERE guid = '$user_guid'
			";
			$selection = $db->do_select($query);
			$user_email = $selection[0]['email'] ?? '';
			$first_name = $selection[0]['first_name'] ?? '';
			$subject = APP_NAME.' Application Status';
			$body = 'Unfortunate news, '.$first_name.'. You have been <b>denied</b> access to your dashboard.<br><br>'.$deny_reason;

			if($user_email) {
				$helper->schedule_email(
					'denied',
					$user_email,
					$subject,
					$body
				);
			}

			_exit(
				'success',
				'Successfully denied user'
			);
		}

		_exit(
			'error',
			'Error denying user',
			500
		);
	}
}
new AdminDenyUser();