<?php
include_once('../../core.php');
/**
 *
 * POST /admin/enable-apikey
 *
 * HEADER Authorization: Bearer
 *
 * @param int $api_key_id
 */
class AdminEnableApikey extends Endpoints {
	function __construct(
		$api_key_id = 0
	) {
		global $db, $helper;

		require_method('POST');

		$auth = authenticate_session(2);
		$admin_guid = $auth['guid'] ?? '';
		$api_key_id = (int)(parent::$params['api_key_id'] ?? 0);

		/* Check if there is already an active api-key for this user */
		$query = "
			SELECT guid
			FROM api_keys
			WHERE id = $api_key_id
		";
		$check = $db->do_select($query);
		$check_guid = $check[0]['guid'] ?? '';

		if(!$check || !$check_guid) {
			_exit(
				'error',
				'Invalid api_key_id',
				400,
				'Invalid api_key_id'
			);
		}

		$query = "
			SELECT active
			FROM api_keys
			WHERE guid = '$check_guid'
			AND active = 1
		";
		$check2 = $db->do_select($query);
		$check2_active = (int)($check2[0]['active'] ?? 0);

		if($check2 && $check2_active == 1) {
			_exit(
				'error',
				'User cannot have more than one (1) api key active at a time.',
				403,
				'User cannot have more than one (1) api key active at a time.'
			);
		}

		/* finally update api_key by api_key_id */
		$query = "
			UPDATE api_keys
			SET active = 1
			WHERE id = $api_key_id
		";
		$result = $db->do_query($query);

		if($result) {
			_exit(
				'success',
				'Successfully enabled API key'
			);
		}

		_exit(
			'error',
			'Failed to enable api key',
			500,
			'Failed to enable api key'
		);
	}
}
new AdminEnableApikey();