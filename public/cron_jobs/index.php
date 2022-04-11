<?php
header('Content-type:application/json;charset=utf-8');
exit(json_encode(array(
	'status' => 'error',
	'detail' => 'Resource not specified'
)));