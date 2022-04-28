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