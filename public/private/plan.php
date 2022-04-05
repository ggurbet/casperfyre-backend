<?php
include_once("../../core.php");

global $db, $helper;

$uri = explode('?', $_SERVER['REQUEST_URI'])[0];
$page = strtolower(explode('/', $uri)[1]);
$api_key = '9a35458ad03bcfb6f60b5eff0fe61bc5487dcda60378774710aba1f14fa6e284';
$email = 'tspa@live.com';


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
				<h4 class="nav-item" onclick="window.location.href='/overview'">
					<i class="fa fa-dashboard"></i> 
					Overview
				</h4>

				<h4 class="nav-item nav-item-active" onclick="window.location.href='/plan'">
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
					Dashboard / <span class="casper-red">Plan & Billing</span>
				</div>

				<div id="plan-wrap">
					<div id="plan-inner">
						<div id="plan-header">
							<span>
								Your current plan
							</span>
						</div>
						<div id="plan-body">
							<h4 id="plan-type">Basic Plan</h4>
							<ul>
								<li>Daily token limit: <span id="plan-daily-limit" class="casper-red">0</span></li>
								<li>Monthly token limit: <span id="plan-monthly-limit" class="casper-red">0</span></li>
								<li>API call rate limit: <span id="plan-rate-limit" class="casper-red">0</span> per minute</li>
								<li>License: <span id="plan-license" class="casper-red"></span></li>
								<li>Support: <span id="plan-support" class="casper-red"></span></li>
							</ul>
							<p class="pt15">You are currently on the <span id="plan-type-2"></span>.</p>
							<p class="pt15">Use the dropdown box below to select a new plan.</p>
							<select id="select-plan" class="btn" style="width: 100%; max-width: 400px;">
								<option value="basic-plan">
									Basic Plan - Free for personal use
								</option>
								<option value="hobbyist-plan">
									Hobbyist Plan - Great for running personal projects
								</option>
								<option value="startup-plan">
									Startup Plan - Basic commercial plan
								</option>
								<option value="professional-plan">
									Professional Plan - Best for scaling projects
								</option>
							</select>
							<button id="upgrade-plan-btn" class="btn casper-btn">Upgrade your plan</button>
							<p class="pt5">By upgrading you consent to the <a href="/terms" target=_blank>Terms of Use</a> for the new plan.</p>
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

	<div id="upgrade-plan-modal">
		<div class="p15">
			<span class="fs18" id="span-selected-plan"></span>
			<p class="pt20">Upgrade</p>
			<button class="btn casper-btn">
				<i class="fa fa-plus-circle"></i>
				Upgrade Now
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
var csrf_token = '<?php echo CSRF_TOKEN; ?>';
// console.log(csrf_token);


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



/*  */
$("#upgrade-plan-modal").iziModal({
	title: 'Upgrade Plan',
	theme: 'light',
	headerColor: '#e9ecef'
});

$("body").on('click', '#upgrade-plan-btn', function(event) {
	event.preventDefault();
	var selected_plan = $("#select-plan").val();
	var selected_plan_text = $("#select-plan option:selected").text();
	$("#span-selected-plan").html(selected_plan_text);
	$('#upgrade-plan-modal').iziModal('open');
});



$("#api-key-box").click(function() {
	iziToast.success({
		title: 'API key copied!'
	});
});

$("#new-api-key-btn").click(function() {
	console.log(this);
});



</script>
</body>
</html>