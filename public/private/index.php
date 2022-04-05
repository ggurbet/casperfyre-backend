<?php
include_once("../../core.php");

global $db, $helper;

$uri = explode('?', $_SERVER['REQUEST_URI'])[0];
$page = strtolower(explode('/', $uri)[1]);
$api_key = 'c6615c54570af0c75f1da7dc1ad865466da5ab0405593c4451dfcec26c7de31a';
$session_token = 'd1523e7a81fabb2d3724058532e21cdd2be6fa52a43090a1796231249c3638159e99a5064acc53879aaaaa908fa9467461c23fc816ec677a12647bc70add027c677e64dd8cfbd94607fdbde996eff34bfb6c37b91e98236d366acbccd32b2a1d3bda14ca5758c18b7c5a2febe484f02b0b8cd8981033811276ef99b1e4cb1113';
$email = 'tspa@live.com';
$plan = 'startup';

switch($plan) {
	case 'basic':
		$badge_class = 'basic';
		$badge = '';
	break;
	case 'hobbyist':
		$badge_class = 'upgraded';
		$badge = '';
	break;
	case 'startup':
		$badge_class = 'upgraded';
		$badge = '';
	break;
	case 'pro':
		$badge_class = 'upgraded';
		$badge = '';
	break;
	default:
		$badge_class = 'basic';
		$badge = '';
	break;
}

/*
basic, hobbyist, startup, pro
*/


?>
<!DOCTYPE html>
<html>
<head>
	<?php include_once('../head.php'); ?>
</head>
<body>
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12" id="logo-col">
				<img src="/assets/images/logo-black.png">
				<div style="float: right; padding-top: 5px; font-size: 22px; cursor: pointer; opacity: 0.8;">
					<i id="account-btn" class="fa fa-user"></i>
				</div>
			</div>
		</div>
	</div>

	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-2" id="nav-col">
				<h4 class="nav-item nav-item-active" onclick="window.location.href='/overview'">
					<i class="fa fa-dashboard"></i> 
					Overview
				</h4>

				<h4 class="nav-item" onclick="window.location.href='/plan'">
					<i class="fa fa-rocket"></i> 
					Plan & Billing
				</h4>

				<h4 class="nav-item" onclick="window.location.href='/notifications'">
					<i class="fa fa-bell"></i> 
					Notifications
				</h4>

				<h4 class="nav-item" style="position: absolute; bottom: 80px; opacity: 0.5; pointer-events: none;">
					Helpful Links
				</h4>
				<h4 id="ask-question-btn" class="nav-item" style="position: absolute; bottom: 40px;">
					Ask a Question
				</h4>
				<h4 class="nav-item" style="position: absolute; bottom: 0;">
					API Documentation
				</h4>
			</div>
			<div class="col-sm-10" id="dashboard-header-wrap">
				<div id="dashboard-header">
					Dashboard / <span class="casper-red">Overview</span>
				</div>

				<div id="api-key-wrap">
					<div id="api-key-inner">
						<div id="api-key-header">
							<span>
								<i class="fa fa-key op6"></i> 
								API Key
								<i class="fa fa-user-plus op4" id="new-api-key-btn" style="float: right; cursor: pointer;"></i>
							</span>
						</div>
						<div id="api-key-body">
							<div data-clipboard-target="#api-key-text" id="api-key-box">
								<span id="api-key-text" class="op5 fs20" data-clipboard-text="<?php echo $api_key; ?>">
									****************************************************************
								</span>
							</div>
						</div>
					</div>
				</div>
				<div id="usage-wrap">
					<div id="usage-inner">
						<div id="usage-header">
							<span>
								<i class="fa fa-pie-chart op6"></i> 
								API Key Usage
								<div class="badge-<?php echo $badge_class; ?>">
									<?php echo ucfirst($plan); ?> Plan <?php echo $badge; ?>
								</div>
							</span>
						</div>
						<div id="usage-body">
							<div id="usage-box-1">
								<span class="fs13">Tokens Today</span>
								<div class="usage-bar-grey">
									<div id="bar-today" class="usage-bar-green"></div>
								</div>
								<span class="fs10">
									<span id="span-usage-today">0</span>/<span id="span-usage-today-total">0</span>
								</span>
							</div>

							<div id="usage-box-2">
								<span class="fs13">Tokens Yesterday</span>
								<div class="usage-bar-grey">
									<div id="bar-yesterday" class="usage-bar-green"></div>
								</div>
								<span class="fs10">
									<span id="span-usage-yesterday">0</span>/<span id="span-usage-yesterday-total">0</span>
								</span>
							</div>

							<div id="usage-box-3">
								<span class="fs13">Tokens This Month</span>
								<div class="usage-bar-grey">
									<div id="bar-thismonth" class="usage-bar-green"></div>
								</div>
								<span class="fs10">
									<span id="span-usage-thismonth">0</span>/<span id="span-usage-thismonth-total">0</span>
								</span>
							</div>

							<div id="usage-box-4">
								<span class="fs13">Tokens Last Month</span>
								<div class="usage-bar-grey">
									<div id="bar-lastmonth" class="usage-bar-green"></div>
								</div>
								<span class="fs10">
									<span id="span-usage-lastmonth">0</span>/<span id="span-usage-lastmonth-total">0</span>
								</span>
							</div>
						</div>
					</div>
				</div>

				<div id="log-wrap">
					<div id="log-inner">
						<div id="log-header">
							<span>
								<i class="fa fa-calendar op6"></i> 
								API Request Log
							</span>
						</div>
						<div id="log-body">
							<table class="table display" id="historyTable" style="width: 100%;">

								<?php $headers = array('&ensp;', 'Amount', 'Timestamp', '&ensp;'); ?>

								<thead>
									<tr>
										<?php foreach($headers as $header) { ?>
											<th class="secondary-text">
												<div class="table-header">
													<span class="column-title op8 fs14"><?php echo $header; ?></span>
												</div>
											</th>
										<?php } ?>
									</tr>
								</thead>
								<tbody class="fs13">
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- modals -->
	<div id="account-modal">
		<div class="p15">
			<p class="pt5 op6">
				<i id="account-btn" class="fa fa-user"></i>
				<?php echo $email; ?>&ensp;
				<button id="logout-btn" class="btn neutral-btn">
					<i class="fa fa-sign-out"></i>
					Logout
				</button>
			</p>
			<hr>
			<h4>Change password</h4>
			<div class="form-group pt10">
				<label for="usr">Old password</label>
				<input id="input-old-password" type="password" class="form-control">
				<label for="usr">New password</label>
				<input id="input-new-password" type="password" class="form-control">
				<label for="usr">Reenter password</label>
				<input id="input-new-password2" type="password" class="form-control">
				<button id="button-update-password" class="btn casper-btn">Update Password</button>
			</div>
			<hr>
			<h4>Freeze account</h4>
			<div class="form-group pt10">
				<p>Freezing your account disables your api key and stops any further service charges from hitting your account. You can freeze your account for an unlimited amount of time. You can unfreeze your account at any time.</p>
				<button id="button-freeze-account" class="btn casper-btn">Freeze Account</button>
			</div>
		</div>
	</div>

	<div id="ask-question-modal">
		<div class="p15">
			Please use the form below to ask a question of our team.
		</div>
	</div>

	<div id="new-api-key-modal">
		<div class="p15">
			<p>If you believe your API key has been compromised, or you simply want to generate a new one, click below.</p>
			<p><b>When you click this button, your old API key will be lost. Please be sure this is intentional, otherwise it may break your existing integrations.</b></p>

			<button id="generate-new-api-key-btn" class="btn casper-btn">
				<i class="fa fa-plus"></i>
				&ensp;Generate New API Key
			</button>
		</div>
	</div>

<script type="text/javascript" src="/assets/js/jquery.min.js"></script>
<script type="text/javascript" src="/assets/js/ui.js"></script>
<script type="text/javascript" src="/assets/js/iziToast.min.js"></script>
<script type="text/javascript" src="/assets/js/iziModal.min.js"></script>
<script type="text/javascript" src="/assets/js/moment.min.js"></script>
<script type="text/javascript" src="/assets/js/md5.min.js"></script>
<script type="text/javascript" src="/assets/js/datepicker.min.js"></script>
<script type="text/javascript" src="/assets/js/datepicker.en.js"></script>
<script type="text/javascript" src="/assets/js/clipboard.min.js"></script>
<script type="text/javascript" src="/assets/js/jquery.datatables.min.js"></script>
<script type="text/javascript" src="/assets/js/datatable.responsive.js"></script>
<script type="text/javascript" src="/assets/js/datatable.scroller.js"></script>
<script type="text/javascript" src="/assets/js/apexcharts.js"></script>
<script>

var api_key = '<?php echo $api_key; ?>';
var session_token = '<?php echo $session_token; ?>';
var csrf_token = '<?php echo CSRF_TOKEN; ?>';
// console.log(csrf_token);

new ClipboardJS('#api-key-box');



/* account modal */
$("#account-modal").iziModal({
	title: 'My Account',
	theme: 'light',
	headerColor: '#e9ecef'
});

$("body").on('click', '#account-btn', function(event) {
	event.preventDefault();
	$('#account-modal').iziModal('open');
});



/* api key modal */
$("#new-api-key-modal").iziModal({
	title: 'New API Key',
	theme: 'light',
	headerColor: '#e9ecef'
});

$("body").on('click', '#new-api-key-btn', function() {
	event.preventDefault();
	$('#new-api-key-modal').iziModal('open');
});

$("#generate-new-api-key-btn").click(function() {
	$('#new-api-key-modal').iziModal('startLoading');

	$.ajax({
		method: "POST",
		url: "/user/update-api-key",
		contentType: 'application/json',
		dataType: 'json',
		data: JSON.stringify({}),
		beforeSend: function(req) {
			console.log(req);
			req.setRequestHeader("Content-type", "application/json");
			req.setRequestHeader("Authorization", "bearer "+session_token);
		}
	})
	.done(function(res) {
		// console.log(res);
		var status = res.status ? res.status : 'error';
		var msg = res.detail ? res.detail : '';

		if(status == 'success') {
			iziToast.success({
				title: 'Success!',
				message: 'API key updated'
			});
			api_key = msg
			$('#new-api-key-modal').iziModal('stopLoading');
			$('#new-api-key-modal').iziModal('close');
		} else {
			iziToast.error({
				title: 'Error',
				message: 'There was a problem updated your settings'
			});
		}
	});
});



/* ask a question modal */
$("#ask-question-modal").iziModal({
	title: 'Ask a question',
	theme: 'light',
	headerColor: '#e9ecef'
});

$("body").on('click', '#ask-question-btn', function(event) {
	event.preventDefault();
	$('#ask-question-modal').iziModal('open');
});



$("body").on('mouseover', '#api-key-box', function() {
	$("#api-key-text").text(api_key);
});

$("body").on('mouseout', '#api-key-box', function() {
	$("#api-key-text").text('****************************************************************');
});

$("#api-key-box").click(function() {
	iziToast.success({
		title: 'API key copied!'
	});
});

$.fn.dataTable.moment = function(format) {
	var types = $.fn.dataTable.ext.type;

	return types.order['moment-'+format+'-pre'] = function(d) {
		if(d && d.replace()) d = d.replace(/<.*?>/g, '');
		let m = moment(d*1000);
		return m.isValid() ? '<div style="width:0;height:0;overflow:hidden;">'+d+'</div>'+m.format(format) : d;
	}
}

var historyTable = $("#historyTable").DataTable({
	"info": true,
	"ordering": true,
	"searching": false,
	"processing": false,
	"serverSide": false,
	"responsive": true,
	"paging": true,
	"lengthMenu": [10, 25, 50, 100, 200, 500],
	"pageLength": 10,
	// "bLengthChange": false,
	"order": [[2, "desc"]],
	"ajax": {
		"url": "/user/history",
		"type": "GET",
		"beforeSend": function(req) {
			req.setRequestHeader("Authorization", "bearer "+session_token)
		}
	},
	"columnDefs": [{
		"render": $.fn.dataTable.moment('lll'),
		"targets": [2]
	},{
		"orderable": false,
		"targets": [3]
	}],
});

var usage_today = 0;
var usage_yesterday = 0;
var usage_thismonth = 0;
var usage_lastmonth = 0;

var usage_today_total = 0;
var usage_yesterday_total = 0;
var usage_thismonth_total = 0;
var usage_lastmonth_total = 0;

var bar_today_width = 0;
var bar_yesterday_width = 0;
var bar_thismonth_width = 0;
var bar_lastmonth_width = 0;

var bar_today_color = '#2CA02C';
var bar_yesterday_color = '#2CA02C';
var bar_thismonth_color = '#2CA02C';
var bar_lastmonth_color = '#2CA02C';

function load_usage() {
	console.log('Getting usage metrics');
	$.ajax({
		method: "GET",
		url: "/user/usage",
		beforeSend: function(req) {
			req.setRequestHeader("Authorization", "bearer "+session_token)
		}
	}).done(function(res) {
		// console.log(res);
		var detail = res.detail ? res.detail : {};

		if(detail.hasOwnProperty("usage_today")) usage_today = detail.usage_today;
		if(detail.hasOwnProperty("usage_yesterday")) usage_yesterday = detail.usage_yesterday;
		if(detail.hasOwnProperty("usage_thismonth")) usage_thismonth = detail.usage_thismonth;
		if(detail.hasOwnProperty("usage_lastmonth")) usage_lastmonth = detail.usage_lastmonth;

		if(detail.hasOwnProperty("usage_today_total")) usage_today_total = detail.usage_today_total;
		if(detail.hasOwnProperty("usage_yesterday_total")) usage_yesterday_total = detail.usage_yesterday_total;
		if(detail.hasOwnProperty("usage_thismonth_total")) usage_thismonth_total = detail.usage_thismonth_total;
		if(detail.hasOwnProperty("usage_lastmonth_total")) usage_lastmonth_total = detail.usage_lastmonth_total;

		$("#span-usage-today").html(usage_today);
		$("#span-usage-yesterday").html(usage_yesterday);
		$("#span-usage-thismonth").html(usage_thismonth);
		$("#span-usage-lastmonth").html(usage_lastmonth);

		$("#span-usage-today-total").html(usage_today_total);
		$("#span-usage-yesterday-total").html(usage_yesterday_total);
		$("#span-usage-thismonth-total").html(usage_thismonth_total);
		$("#span-usage-lastmonth-total").html(usage_lastmonth_total);

		bar_today_width = (usage_today / usage_today_total) * 100;
		bar_yesterday_width = (usage_yesterday / usage_yesterday_total) * 100;
		bar_thismonth_width = (usage_thismonth / usage_thismonth_total) * 100;
		bar_lastmonth_width = (usage_lastmonth / usage_lastmonth_total) * 100;

		if(bar_today_width > 70) bar_today_color = "#E5A339";
		if(bar_today_width > 90) bar_today_color = "#9E2C2C";

		if(bar_yesterday_width > 70) bar_yesterday_color = "#E5A339";
		if(bar_yesterday_width > 90) bar_yesterday_color = "#9E2C2C";

		if(bar_thismonth_width > 70) bar_thismonth_color = "#E5A339";
		if(bar_thismonth_width > 90) bar_thismonth_color = "#9E2C2C";

		if(bar_lastmonth_width > 70) bar_lastmonth_color = "#E5A339";
		if(bar_lastmonth_width > 90) bar_lastmonth_color = "#9E2C2C";

		$("#bar-today").animate({
			"width": bar_today_width+"%",
			"background-color": bar_today_color
		},450,'easeOutQuad');

		$("#bar-yesterday").animate({
			"width": bar_yesterday_width+"%",
			"background-color": bar_yesterday_color
		},450,'easeOutQuad');

		$("#bar-thismonth").animate({
			"width": bar_thismonth_width+"%",
			"background-color": bar_thismonth_color
		},450,'easeOutQuad');

		$("#bar-lastmonth").animate({
			"width": bar_lastmonth_width+"%",
			"background-color": bar_lastmonth_color
		},450,'easeOutQuad');
	});
}

load_usage();

setInterval(function() {
	load_usage();
},20000);

</script>
</body>
</html>