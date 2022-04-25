<?php
include_once('../../../core.php');

global $helper;

require_method('POST');
$auth = authenticate_api();
$guid = $auth['guid'] ?? '';
$params = get_params();
$address = $params['address'] ?? null;
$amount = (int)($params['amount'] ?? 0);

process_order(
	$guid, 
	$address, 
	$amount,
	$auth
);
