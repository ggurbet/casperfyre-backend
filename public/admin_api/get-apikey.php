<?php
include_once('../../core.php');

global $db, $helper;

require_method('GET');
$auth = authenticate_session(2);
$admin_guid = $auth['guid'] ?? '';
$params = get_params();
// $api_key_id = (int)($params['api_key_id'] ?? 0);
$api_key_id = (int)_request('api_key_id');

$query = "
	SELECT a.guid, a.email, b.id AS api_key_id, b.api_key, b.active, b.created_at, b.total_calls
	FROM users AS a
	JOIN api_keys AS b
	ON a.guid = b.guid
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
