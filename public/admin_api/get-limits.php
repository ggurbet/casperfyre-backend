<?php
include_once('../../core.php');
/**
 *
 * GET /admin/get-limits
 *
 * HEADER Authorization: Bearer
 *
 * @param string  $guid
 */
class AdminGetLimits extends Endpoints {
	function __construct(
		$guid = ''
	) {
		global $db, $helper;

		require_method('GET');

		$auth = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';
		$user_guid = parent::$params['guid'];

		$query = "
			SELECT per_limit, day_limit, week_limit, month_limit
			FROM limits
			WHERE guid = '$user_guid'
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
new AdminGetLimits();