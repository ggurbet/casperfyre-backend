<?php
include_once('../../core.php');

global $helper;

require_method('GET');
$auth = authenticate_session();
$guid = $auth['guid'] ?? '';
$usage = get_usage($guid);

_exit(
	'success',
	$usage
);