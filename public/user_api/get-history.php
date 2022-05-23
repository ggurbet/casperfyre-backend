<?php
include_once('../../core.php');
/**
 *
 * GET /user/get-history
 *
 * HEADER Authorization: Bearer
 *
 */
class UserGetHistory extends Endpoints {
	function __construct() {
		global $helper, $db;

		require_method('GET');
		$auth = authenticate_session(1);
		$guid = $auth['guid'] ?? '';

		$query = "
			SELECT * 
			FROM orders
			WHERE guid = '$guid'
			ORDER BY id DESC
		";
		$results = $db->do_select($query);

		_exit(
			'success',
			$results
		);

		/*
		not used anymore
		*/
		$output = array(
			"data" => array()
		);

		if($results) {
			foreach($results as $result) {
				switch((int)$result['fulfilled']) {
					case 0:
						$fulfilled = "<img src='/assets/images/pending.svg' class='tiny-img'>";
					break;
					case 1:
						$fulfilled = "<img src='/assets/images/check-circle.png' class='tiny-img'>";
					break;
					case 2:
						$fulfilled = "<img src='/assets/images/failed-circle.png' class='tiny-img'>";
					break;
					default:
						$fulfilled = "<img src='/assets/images/pending.svg' class='tiny-img'>";
					break;
				}

				if((int)$result['fulfilled'] == 0) {
					$fulfilled = "<img src='/assets/images/pending.svg' class='tiny-img'>";
				}

				if((int)$result['fulfilled'] == 1) {
					if((int)$result['success'] == 0) {
						$fulfilled = "<img src='/assets/images/check-circle1.png' class='tiny-img'>";
					}

					if((int)$result['success'] == 1) {
						$fulfilled = "<img src='/assets/images/check-circle2.png' class='tiny-img'>";
					}

					if((int)$result['success'] == 2) {
						$fulfilled = "<img src='/assets/images/warning-circle.png' class='tiny-img'>";
					}
				}

				$color = 'casper-red';

				if($result['return_code'] == 200) $color = 'green';

				$return_code = '<span class="'.$color.'">'.$result['return_code'].'</span>';

				$output['data'][] = array(
					'<div>'.$return_code.' - IP '.$result['ip'].'</div>'.'<span class="fs10"><a href="https://cspr.live/account/'.$result['address'].'" target=_blank>'.$result['address'].'</a></span>',
					$result['amount'],
					$result['created_at'],
					$fulfilled
				);
			}
		}

		exit(json_encode($output));
	}
}
new UserGetHistory();