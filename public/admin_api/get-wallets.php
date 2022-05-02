<?php
include_once('../../core.php');
/**
 *
 * GET /admin/get-wallets
 *
 * HEADER Authorization: Bearer
 *
 * @param string  $guid
 */
class AdminGetWallets extends Endpoints {
	function __construct(
		$guid = ''
	) {
		global $db;

		require_method('GET');

		$auth = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';
		$user_guid = parent::$params['guid'] ?? '';

		$query = "
			SELECT guid, active, created_at, inactive_at, address, balance
			FROM wallets
		";

		if($user_guid) $query .= "WHERE guid = '$user_guid'";

		$selection = $db->do_select($query);

		_exit(
			'success',
			$selection
		);
	}
}
new AdminGetWallets();