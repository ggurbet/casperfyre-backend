<?php

use PHPUnit\Framework\TestCase;

include_once(__DIR__.'/../../core.php');

final class SyntaxTest extends TestCase
{
	public function testPhpSyntax()
	{
		global $helper;

		$base_folders = array(
			'public',
			'classes',
			'templates',
			'tests'
		);

		foreach($base_folders as $folder) {
			$list = Helper::get_dir_contents(__DIR__.'/../../', $folder);
			$new_list = array();

			foreach($list as $item) {
				if(strstr($item, '.php')) {
					$result = shell_exec("php -l ".$item);

					if(strstr($result, 'Errors parsing')) {
						$new_list[] = trim($result);
					}
				}
			}

			foreach($new_list as $p) {
				echo $p."\n";
			}

			if(empty($new_list)) {
				echo "Good syntax on ".$folder." PHP files\n";
			}

			$this->assertTrue(empty($new_list));
		}
	}
}