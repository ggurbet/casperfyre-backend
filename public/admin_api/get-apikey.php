<?php
include_once('../../core.php');
/**
 *
 * GET /admin/get-apikey
 *
 * HEADER Authorization: Bearer
 *
 * Works by either specifying by api key ID or by user's guid. Checks guid first.
 *
 * @param string  $guid
 * @param int     $api_key_id
 *
 */
class AdminGetApikey extends Endpoints {
	function __construct(
		$guid = '',
		$api_key_id = 0
	) {
		global $db, $helper;

		require_method('GET');

		$auth = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';
		$api_key_id = (int)(parent::$params['api_key_id'] ?? 0);
		$user_guid = parent::$params['guid'] ?? '';

		if($user_guid) {
			$query = "
				SELECT a.guid, a.email, b.id AS api_key_id, b.api_key, b.active, b.created_at, b.total_calls
				FROM users AS a
				LEFT JOIN api_keys AS b
				ON b.guid = a.guid 
				AND b.active = 1
				WHERE a.guid = '$user_guid'
			";
			$selection = $db->do_select($query);
			$selection = $selection[0] ?? array();
			$api_key_id = $selection['api_key_id'] ?? 0;

			/* get total_cspr_sent <-- by guid */
			$query = "
				SELECT amount
				FROM orders
				WHERE guid = '$user_guid'
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


			/* get total_calls <-- by guid */
			$query = "
				SELECT SUM(total_calls)
				AS total_calls
				FROM api_keys
				WHERE guid = '$user_guid'
			";
			$total_calls = $db->do_select($query);
			$total_calls = (int)($total_calls[0]['total_calls'] ?? 0);
			$selection['total_calls'] = $total_calls;

			_exit(
				'success',
				$selection
			);
		}

		$query = "
			SELECT a.guid, a.email, b.id AS api_key_id, b.api_key, b.active, b.created_at, b.total_calls
			FROM users AS a
			JOIN api_keys AS b
			ON b.guid = a.guid
			WHERE b.id = $api_key_id
		";
		$selection = $db->do_select($query);
		$selection = $selection[0] ?? array();
		$api_key_id = $selection['api_key_id'] ?? 0;
		$user_guid = $selection['guid'] ?? '';
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
new AdminGetApikey();