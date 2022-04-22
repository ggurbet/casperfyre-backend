<?php
include_once('../../core.php');

global $helper, $db;

$now = $helper->get_datetime();
$three_months_ago = $helper->get_datetime(-7889400);

$query = "
	SELECT id, address
	FROM wallets
	WHERE inactive_at >= '$three_months_ago'
	ORDER BY last_balance_check ASC
	LIMIT 10
";

$selection = $db->do_select($query);

if($selection) {
	foreach($selection as $s) {
		$new_balance = $helper->get_wallet_balance($s['address']);
		$wallet_id = $s['id'] ?? 0;

		$query = "
			UPDATE wallets
			SET balance = $new_balance, last_balance_check = '$now'
			WHERE id = $wallet_id
		";
		$db->do_query($query);
	}
}