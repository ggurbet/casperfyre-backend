<?php
include_once('../../core.php');
/**
 *
 * POST /user/create-apikey
 *
 * HEADER Authorization: Bearer
 *
 */
class UserCreateApikey extends Endpoints {
	function __construct() {
		global $db, $helper;

		require_method('POST');

		$auth = authenticate_session();
		$guid = $auth['guid'] ?? '';
		$new_api_key = $helper->generate_apikey();
		$created_at = $helper->get_datetime();

		$query = "
			UPDATE api_keys
			SET active = 0
			WHERE guid = '$guid'
		";

		$db->do_query($query);

		$query = "
			INSERT INTO api_keys (
				guid,
				api_key,
				created_at
			) VALUES (
				'$guid',
				'$new_api_key',
				'$created_at'
			)
		";

		$result = $db->do_query($query);

		if($result) {
			_exit(
				'success',
				$new_api_key
			);
		}

		_exit(
			'error',
			'Failed to create new api key',
			500,
			'Failed to create new api key'
		);
	}
}
new UserCreateApikey();