<?php
include_once('../../core.php');
/**
 *
 * PUT /user/update-limits
 *
 * HEADER Authorization: Bearer
 *
 * @param int     $per_limit
 * @param int     $day_limit
 * @param int     $month_limit
 *
 * If a parameter is not specified, then it will not be affected.
 *
 */
class UserUpdateLimits extends Endpoints {
	function __construct(
		$per_limit = 0,
		$day_limit = 0,
		$month_limit = 0
	) {
		global $db;

		require_method('PUT');

		$auth = authenticate_session();
		$user_guid = $auth['guid'] ?? '';
		$per_limit = isset(parent::$params['per_limit']) ? (int)parent::$params['per_limit'] : null;
		$day_limit = isset(parent::$params['day_limit']) ? (int)parent::$params['day_limit'] : null;
		$month_limit = isset(parent::$params['month_limit']) ? (int)parent::$params['month_limit'] : null;
		$message = '';

		if(gettype($per_limit) == 'integer') {
			$per_limit < 0 ? $per_limit = 0 : $per_limit;
			$query = "
				UPDATE limits
				SET per_limit = $per_limit
				WHERE guid = '$user_guid'
			";
			$db->do_query($query);
			$message .= "Per request limit is now $per_limit. ";
		}

		if(gettype($day_limit) == 'integer') {
			$day_limit < 0 ? $day_limit = 0 : $day_limit;
			$query = "
				UPDATE limits
				SET day_limit = $day_limit
				WHERE guid = '$user_guid'
			";
			$db->do_query($query);
			$message .= "Daily limit is now $day_limit. ";
		}

		if(gettype($month_limit) == 'integer') {
			$month_limit < 0 ? $month_limit = 0 : $month_limit;
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
			$message
		);
	}
}
new UserUpdateLimits();