<?php
include_once("../../core.php");

global $db, $helper;

$uri = explode('?', $_SERVER['REQUEST_URI'])[0];
$page = strtolower(explode('/', $uri)[1]);
$api_key = '9a35458ad03bcfb6f60b5eff0fe61bc5487dcda60378774710aba1f14fa6e284';
$email = 'tspa@live.com';

$query = "
	SELECT 
	notification_setting_email,
	notification_setting_daily75,
	notification_setting_daily90,
	notification_setting_monthly75,
	notification_setting_monthly90,
	notification_setting_ratelimit
	FROM users
	WHERE api_key = '$api_key'
";

$results = $db->do_select($query);
$results = $results[0] ?? array(
	"notification_setting_email" => 0,
	"notification_setting_daily75" => 0,
	"notification_setting_daily90" => 0,
	"notification_setting_monthly75" => 0,
	"notification_setting_monthly90" => 0,
	"notification_setting_ratelimit" => 0,
);

$checkbox1 = (bool)$results['notification_setting_email'];
$checkbox2 = (bool)$results['notification_setting_daily75'];
$checkbox3 = (bool)$results['notification_setting_daily90'];
$checkbox4 = (bool)$results['notification_setting_monthly75'];
$checkbox5 = (bool)$results['notification_setting_monthly90'];
$checkbox6 = (bool)$results['notification_setting_ratelimit'];

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

				<h4 class="nav-item" onclick="window.location.href='/plan'">
					<i class="fa fa-rocket"></i> 
					Plan & Billing
				</h4>

				<h4 class="nav-item nav-item-active" onclick="window.location.href='/notifications'">
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
					Dashboard / <span class="casper-red">Notifications</span>
				</div>

				<div id="notification-wrap">
					<div id="notification-inner">
						<div id="notification-header">
							<span>
								<i class="fa fa-gear"></i> 
								Notification Settings
							</span>
						</div>
						<div id="notification-body">
							What plan usage alerts would you like to receive?
							<ul class="mt20" style="list-style: none;">
								<li class="mt5">
									<input id="checkbox-1" type="checkbox" <?php echo $checkbox1 ? 'checked' : ''; ?>>
									<label for="checkbox-1">Notify me by email</label>
								</li>
								<li class="mt5">
									<input id="checkbox-2" type="checkbox" <?php echo $checkbox2 ? 'checked' : ''; ?>>
									<label for="checkbox-2">Daily usage over 75%</label>
								</li>
								<li class="mt5">
									<input id="checkbox-3" type="checkbox" <?php echo $checkbox3 ? 'checked' : ''; ?>>
									<label for="checkbox-3">Daily usage over 90%</label>
								</li>
								<li class="mt5">
									<input id="checkbox-4" type="checkbox" <?php echo $checkbox4 ? 'checked' : ''; ?>>
									<label for="checkbox-4">Monthly usage over 75%</label>
								</li>
								<li class="mt5">
									<input id="checkbox-5" type="checkbox" <?php echo $checkbox5 ? 'checked' : ''; ?>>
									<label for="checkbox-5">Monthly usage over 90%</label>
								</li>
								<li class="mt5">
									<input id="checkbox-6" type="checkbox" <?php echo $checkbox6 ? 'checked' : ''; ?>>
									<label for="checkbox-6">HTTP request rate limit exceeded</label>
								</li>
							</ul>
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



$("#checkbox-1").on('change', function() {
	update_notification1(this.checked);
});

$("#checkbox-2").on('change', function() {
	update_notification2(this.checked);
});

$("#checkbox-3").on('change', function() {
	update_notification3(this.checked);
});

$("#checkbox-4").on('change', function() {
	update_notification4(this.checked);
});

$("#checkbox-5").on('change', function() {
	update_notification5(this.checked);
});

$("#checkbox-6").on('change', function() {
	update_notification6(this.checked);
});

function update_notification1(checked) {
	$.ajax({
		method: "POST",
		url: "/update-notification-email",
		contentType: 'application/json',
		dataType: 'json',
		data: JSON.stringify({
			checked: checked
		}),
		beforeSend: function(req) {
			console.log(req);
			req.setRequestHeader("Content-type", "application/json");
			req.setRequestHeader("Authorization", "bearer "+api_key);
		}
	})
	.done(function(res) {
		var status = res.status ? res.status : 'error';
		var msg = res.detail ? res.detail : '';

		if(status == 'success') {
			iziToast.success({
				title: 'Success!',
				message: msg
			});
		} else {
			iziToast.error({
				title: 'Error',
				message: 'There was a problem updated your settings'
			});
		}
	})
}

function update_notification2(checked) {
	$.ajax({
		method: "POST",
		url: "/update-notification-daily75",
		contentType: 'application/json',
		dataType: 'json',
		data: JSON.stringify({
			checked: checked
		}),
		beforeSend: function(req) {
			req.setRequestHeader("Content-type", "application/json");
			req.setRequestHeader("Authorization", "bearer "+api_key);
		}
	})
	.done(function(res) {
		var status = res.status ? res.status : 'error';
		var msg = res.detail ? res.detail : '';

		if(status == 'success') {
			iziToast.success({
				title: 'Success!',
				message: msg
			});
		} else {
			iziToast.error({
				title: 'Error',
				message: 'There was a problem updated your settings'
			});
		}
	})
}

function update_notification3(checked) {
	$.ajax({
		method: "POST",
		url: "/update-notification-daily90",
		contentType: 'application/json',
		dataType: 'json',
		data: JSON.stringify({
			checked: checked
		}),
		beforeSend: function(req) {
			req.setRequestHeader("Content-type", "application/json");
			req.setRequestHeader("Authorization", "bearer "+api_key);
		}
	})
	.done(function(res) {
		var status = res.status ? res.status : 'error';
		var msg = res.detail ? res.detail : '';

		if(status == 'success') {
			iziToast.success({
				title: 'Success!',
				message: msg
			});
		} else {
			iziToast.error({
				title: 'Error',
				message: 'There was a problem updated your settings'
			});
		}
	})
}

function update_notification4(checked) {
	$.ajax({
		method: "POST",
		url: "/update-notification-monthly75",
		contentType: 'application/json',
		dataType: 'json',
		data: JSON.stringify({
			checked: checked
		}),
		beforeSend: function(req) {
			req.setRequestHeader("Content-type", "application/json");
			req.setRequestHeader("Authorization", "bearer "+api_key);
		}
	})
	.done(function(res) {
		var status = res.status ? res.status : 'error';
		var msg = res.detail ? res.detail : '';

		if(status == 'success') {
			iziToast.success({
				title: 'Success!',
				message: msg
			});
		} else {
			iziToast.error({
				title: 'Error',
				message: 'There was a problem updated your settings'
			});
		}
	})
}

function update_notification5(checked) {
	$.ajax({
		method: "POST",
		url: "/update-notification-monthly90",
		contentType: 'application/json',
		dataType: 'json',
		data: JSON.stringify({
			checked: checked
		}),
		beforeSend: function(req) {
			req.setRequestHeader("Content-type", "application/json");
			req.setRequestHeader("Authorization", "bearer "+api_key);
		}
	})
	.done(function(res) {
		var status = res.status ? res.status : 'error';
		var msg = res.detail ? res.detail : '';

		if(status == 'success') {
			iziToast.success({
				title: 'Success!',
				message: msg
			});
		} else {
			iziToast.error({
				title: 'Error',
				message: 'There was a problem updated your settings'
			});
		}
	})
}

function update_notification6(checked) {
	$.ajax({
		method: "POST",
		url: "/update-notification-ratelimit",
		contentType: 'application/json',
		dataType: 'json',
		data: JSON.stringify({
			checked: checked
		}),
		beforeSend: function(req) {
			req.setRequestHeader("Content-type", "application/json");
			req.setRequestHeader("Authorization", "bearer "+api_key);
		}
	})
	.done(function(res) {
		var status = res.status ? res.status : 'error';
		var msg = res.detail ? res.detail : '';

		if(status == 'success') {
			iziToast.success({
				title: 'Success!',
				message: msg
			});
		} else {
			iziToast.error({
				title: 'Error',
				message: 'There was a problem updated your settings'
			});
		}
	})
}


</script>
</body>
</html>