<?php

use PHPUnit\Framework\TestCase;

include_once(__DIR__.'/../core.php');

final class HelperTest extends TestCase
{
	public function testGenerateGuid()
	{
		$test_guid = Helper::generate_guid();
		$this->assertContains('4c4c', explode('-', $test_guid));
	}

	public function testGenerateApiKey()
	{
		$test_api_key = Helper::generate_apikey();
		$this->assertEquals(64, strlen($test_api_key));
		$this->assertTrue(ctype_xdigit($test_api_key));
	}

	public function testGenerateSessionToken()
	{
		$test_session_key = Helper::generate_session_token();
		$this->assertEquals(256, strlen($test_session_key));
		$this->assertTrue(ctype_xdigit($test_session_key));
	}

	public function testGenerateHash()
	{
		$test_hash = Helper::generate_hash(22);
		$this->assertEquals(22, strlen($test_hash));
	}

	public function testCompareDatetime()
	{
		$test_date1 = Helper::get_datetime();
		sleep(1);
		$test_date2 = Helper::get_datetime();
		$this->assertGreaterThan($test_date1, $test_date2);
	}

	public function testGenerateWallet()
	{
		$test_wallet = Helper::generate_wallet();
		$this->assertArrayHasKey('public', $test_wallet);
		$this->assertArrayHasKey('secret', $test_wallet);
	}

	public function testAesEncryptionAndDecryption()
	{
		$test_string = "abc123xyz";
		$cypher_text = Helper::aes_encrypt($test_string);
		$plain_text = Helper::aes_decrypt($cypher_text);
		$this->assertEquals($test_string, $plain_text);
	}

	public function testCorrectValidatorIdFormat()
	{
		$test_id = '011117189c666f81c5160cd610ee383dc9b2d0361f004934754d39752eedc64957';
		$test_validity = Helper::correct_validator_id_format($test_id);
		$this->assertTrue($test_validity);
	}

	public function testFailIncorrectValidatorIdFormat()
	{
		$fail_id = '311117189c666f81c5160cd610ee383dc9b2d0361f004934754d39752eedc6';
		$fail_validity = Helper::correct_validator_id_format($fail_id);
		$this->assertFalse($fail_validity);
	}

	public function testInCidrRange()
	{
		$in_range = Helper::in_CIDR_range(
			'192.168.15.255', 
			'192.168.2.1/20'
		);
		$this->assertTrue($in_range);
	}

	public function testNotInCidrRange()
	{
		$in_range = Helper::in_CIDR_range(
			'192.168.1.3', 
			'192.168.1.1/31'
		);
		$this->assertFalse($in_range);
	}

}

?>