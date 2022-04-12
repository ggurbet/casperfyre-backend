<?php

use PHPUnit\Framework\TestCase;

include_once(__DIR__.'/../core.php');

final class EndpointsTest extends TestCase
{
	public function testDispenseOrder()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, CORS_SITE.'/v1/dispense');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		$fields = array(
			"address" => "011117189c666f81c5160cd610ee383dc9b2d0361f004934754d39752eedc64957", 
			"amount" => 123
		);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		$headers = array();
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Authorization: token phpunittesttoken';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$response = curl_exec($ch);
		curl_close($ch);
		$json = json_decode($response);
		$RETURN_STATUS = $json->detail->RETURN_STATUS ?? null;
		$RETURN_CODE = $json->detail->RETURN_CODE ?? null;
		$this->assertEquals('success', $RETURN_STATUS);
		$this->assertEquals(200, $RETURN_CODE);
	}

	public function testRegisterNewUser()
	{
		global $db;

		$query = "
			DELETE FROM users
			WHERE guid = '00000000-0000-0000-4c4c-000000000000'
		";
		$db->do_query($query);
		$query = "
			DELETE FROM ips
			WHERE guid = '00000000-0000-0000-4c4c-000000000000'
		";
		$db->do_query($query);
		$query = "
			DELETE FROM sessions
			WHERE guid = '00000000-0000-0000-4c4c-000000000000'
		";
		$db->do_query($query);
		$query = "
			DELETE FROM limits
			WHERE guid = '00000000-0000-0000-4c4c-000000000000'
		";
		$db->do_query($query);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, CORS_SITE.'/user/register');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		$fields = array(
			"email" => "dev-test@example.com",
			"first_name" => "dev",
			"last_name" => "test",
			"password" => "dev-test-Password123",
			"company" => "Test Company",
			"description" => "Test description",
			"cspr_expectation" => 100,
			"phpunittesttoken" => "phpunittesttoken"
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
		$this->assertEquals($guid, '00000000-0000-0000-4c4c-000000000000');
		$this->assertEquals(256, strlen($bearer));
	}

	public function testRejectExistingEmailRegistration()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, CORS_SITE.'/user/register');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		$fields = array(
			"email" => "dev-test@example.com",
			"first_name" => "dev",
			"last_name" => "test",
			"password" => "dev-test-Password123",
			"company" => "Test Company",
			"description" => "Test description",
			"cspr_expectation" => 100,
			"phpunittesttoken" => "phpunittesttoken"
		);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		$headers = array();
		$headers[] = 'Content-Type: application/json';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$response = curl_exec($ch);
		curl_close($ch);
		$this->assertEquals(400, curl_getinfo($ch, CURLINFO_RESPONSE_CODE));
	}
}

?>