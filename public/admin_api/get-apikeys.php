<?php
include_once('../../core.php');

global $db, $helper;

require_method('GET');
$auth = authenticate_session(2);
$admin_guid = $auth['guid'] ?? '';

$query = "
	SELECT a.guid, a.email, a.company, b.id AS api_key_id, b.api_key, b.active, b.created_at, b.total_calls
	FROM users AS a
	JOIN api_keys AS b
	ON a.guid = b.guid
";
$selection = $db->do_select($query);

if($selection) {
	foreach ($selection as $key => $value) {
		$api_key_id = $selection[$key]['api_key_id'] ?? 0;
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

		$selection[$key]['total_cspr_sent'] = $total_cspr_sent;
	}
}

_exit(
	'success',
	$selection
);
