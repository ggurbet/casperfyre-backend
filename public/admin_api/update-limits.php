<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/../core.php');
/**
 *
 * PUT /admin/update-limits
 *
 * HEADER Authorization: Bearer
 *
 * @param string  $guid
 * @param int     $per_limit
 * @param int     $day_limit
 * @param int     $month_limit
 *
 * If a parameter is not specified, then it will not be affected.
 */
class AdminUpdateLimits extends Endpoints {
	function __construct(
		$guid = '',
		$per_limit = 0,
		$day_limit = 0,
		$month_limit = 0
	) {
		global $db;

		require_method('PUT');

		$auth = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';
		$user_guid = parent::$params['guid'] ?? '';
		$per_limit = isset(parent::$params['per_limit']) ? (int)parent::$params['per_limit'] : null;
		$day_limit = isset(parent::$params['day_limit']) ? (int)parent::$params['day_limit'] : null;
		$month_limit = isset(parent::$params['month_limit']) ? (int)parent::$params['month_limit'] : null;
		$message = '';

		/* check user first */
		$query = "
			SELECT guid
			FROM users
			WHERE guid = '$user_guid'
		";
		$check = $db->do_select($query);

		if(!$check) {
			_exit(
				'error',
				'User does not exist',
				400,
				'User does not exist'
			);
		}

		if(gettype($per_limit) == 'integer') {
			if($per_limit < 0) {
				_exit(
					'error',
					'Cannot change a limit to a negative number',
					400,
					'Cannot change a limit to a negative number'
				);
			}

			$query = "
				UPDATE limits
				SET per_limit = $per_limit
				WHERE guid = '$user_guid'
			";
			$db->do_query($query);
			$message .= "Per request limit is now $per_limit. ";
		}

		if(gettype($day_limit) == 'integer') {
			if($day_limit < 0) {
				_exit(
					'error',
					'Cannot change a limit to a negative number',
					400,
					'Cannot change a limit to a negative number'
				);
			}

			$query = "
				UPDATE limits
				SET day_limit = $day_limit
				WHERE guid = '$user_guid'
			";
			$db->do_query($query);
			$message .= "Daily limit is now $day_limit. ";
		}

		if(gettype($month_limit) == 'integer') {
			if($month_limit < 0) {
				_exit(
					'error',
					'Cannot change a limit to a negative number',
					400,
					'Cannot change a limit to a negative number'
				);
			}

			$query = "
				UPDATE limits
				SET month_limit = $month_limit
				WHERE guid = '$user_guid'
			";
			$db->do_query($query);
			$message .= "Monthly limit is now $month_limit. ";
		}

		if($message == '') {
			_exit(
				'error',
				'No limits were affected',
				400,
				'No limits were affected'
			);
		}

		_exit(
			'success',
			'Updated cspr limits of this user. '.$message
		);
	}
}
new AdminUpdateLimits();