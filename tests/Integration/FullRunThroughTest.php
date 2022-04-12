<?php

use PHPUnit\Framework\TestCase;

include_once(__DIR__.'/../../core.php');

final class FullRunThroughTest extends TestCase
{
	public function testRegister()
	{
		global $db;

		$INTEGRATION_TEST_EMAIL = getenv('INTEGRATION_TEST_EMAIL');
		$INTEGRATION_TEST_PASSWORD = getenv('INTEGRATION_TEST_PASSWORD');

		$query = "
			SELECT guid
			FROM users
			WHERE email = '$INTEGRATION_TEST_EMAIL'
		";
		$guid = $db->do_select($query);
		$guid = $guid[0]['guid'] ?? '';

		if($guid) {
			$query = "
				DELETE FROM users
				WHERE guid = '$guid'
			";
			$db->do_query($query);
			$query = "
				DELETE FROM ips
				WHERE guid = '$guid'
			";
			$db->do_query($query);
			$query = "
				DELETE FROM sessions
				WHERE guid = '$guid'
			";
			$db->do_query($query);
			$query = "
				DELETE FROM limits
				WHERE guid = '$guid'
			";
			$db->do_query($query);
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, CORS_SITE.'/user/register');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		$fields = array(
			"email" => $INTEGRATION_TEST_EMAIL,
			"first_name" => "dev",
			"last_name" => "test",
			"password" => $INTEGRATION_TEST_PASSWORD,
			"company" => "Test Company",
			"description" => "Integration test description",
			"cspr_expectation" => 100
		);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		$headers = array();
		$headers[] = 'Content-Type: application/json';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$response = curl_exec($ch);
		curl_close($ch);
		$json = json_decode($response);
		$bearer = $json->detail->bearer;
		$guid = $json->detail->guid;
		elog("Integration Tester GUID: ".$guid);
		elog("Integration Tester Bearer Token: ".$bearer);
		$this->assertEquals(256, strlen($bearer));
	}

}


?>