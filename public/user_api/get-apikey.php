<?php
include_once('../../core.php');
/**
 *
 * GET /user/get-apikey
 *
 * HEADER Authorization: Bearer
 *
 */
class UserGetApikey extends Endpoints {
	function __construct() {
		global $db, $helper;

		require_method('GET');

		$auth = authenticate_session();
		$guid = $auth['guid'] ?? '';

		$query = "
			SELECT a.guid, a.email, b.id AS api_key_id, b.api_key, b.active, b.created_at, b.total_calls
			FROM users AS a
			LEFT JOIN api_keys AS b
			ON b.guid = a.guid 
			AND b.active = 1
			WHERE a.guid = '$guid'
		"; 
		$selection = $db->do_select($query);
		$selection = $selection[0] ?? array();
		$api_key_id = $selection['api_key_id'] ?? 0;

		$query = "
			SELECT amount
			FROM orders
			WHERE api_key_id_used = $api_key_id
			AND success = 1
		";
		$result = $db->do_select($query);
		$total_cspr_sent = 0;

		if($result) {
			foreach($result as $r) {
				$total_cspr_sent += (int)$r['amount'];
			}
		}

		$selection['total_cspr_sent'] = $total_cspr_sent;

		_exit(
			'success',
			$selection
		);
	}
}
new UserGetApikey();