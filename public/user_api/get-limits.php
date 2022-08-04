<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/../core.php');
/**
 *
 * GET /user/get-limits
 *
 * HEADER Authorization: Bearer
 *
 */
class UserGetLimits extends Endpoints {
	function __construct() {
		global $db, $helper;

		require_method('GET');
		$auth = authenticate_session();
		$guid = $auth['guid'] ?? '';

		$query = "
			SELECT per_limit, day_limit, week_limit, month_limit
			FROM limits
			WHERE guid = '$guid'
		";
		$selection = $db->do_select($query);
		$selection = $selection[0] ?? array(
			"per_limit" => 0,
			"day_limit" => 0,
			"week_limit" => 0,
			"month_limit" => 0
		);

		_exit(
			'success',
			$selection
		);
	}
}
new UserGetLimits();