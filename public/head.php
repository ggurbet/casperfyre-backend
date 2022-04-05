<style>
<?php
for($i=5; $i<=200; $i+=5) {
	echo '.pt'.(string)$i.'{padding-top:'.(string)$i.'px;}';
	echo '.pb'.(string)$i.'{padding-bottom:'.(string)$i.'px;}';
	echo '.p'.(string)$i.'{padding:'.(string)$i.'px;}';
}
for($i=1;$i<=150;$i++) { echo '.fs'.(string)$i.'{font-size:'.(string)$i.'px;}'; }
for($i=0;$i<=9;$i++) { echo '.op'.(string)$i.'{opacity:0.'.(string)$i.';}'; }
for($i=0;$i<=100;$i+=5) { echo '.mt'.(string)$i.'{margin-top:'.(string)$i.'px;}'; }	
?>

</style>

<title>Casper Faucet API</title>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
<link rel="apple-touch-icon" sizes="57x57" href="/assets/images/57x57.png">
<link rel="apple-touch-icon" sizes="180x180" href="/assets/images/180x180.png">
<link rel="icon" sizes="32x32" href="/assets/images/32x32.png">
<link rel="icon" sizes="192x192" href="/assets/images/192x192.png">
<meta property="og:title" content=" Casper Faucet API">
<meta property="og:image" content="/assets/images/og.png">
<meta property="og:description" content=" Casper Faucet API - Mainnet token dispenser">
<meta name="theme-color" content="#ffffff"/>
<meta name="description" content=" Casper Faucet API - Mainnet token dispenser"/>
<meta name="keywords" content=""/>
<link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="/assets/css/iziToast.min.css">
<link rel="stylesheet" type="text/css" href="/assets/css/iziModal.min.css">
<link rel="stylesheet" type="text/css" href="/assets/css/fuse-icon-font.css">
<link rel="stylesheet" type="text/css" href="/assets/css/ui.css">
<link rel="stylesheet" type="text/css" href="/assets/css/datatable.min.css">
<link rel="stylesheet" type="text/css" href="/assets/css/datatable.responsive.css">
<link rel="stylesheet" type="text/css" href="/assets/css/datatable.scroller.css">
<link rel="stylesheet" type="text/css" href="/assets/css/materialdesignicons.min.css">
<link rel="stylesheet" type="text/css" href="/assets/css/font-awesome.min.css">
<link type="text/css" rel="stylesheet" href="/assets/css/datepicker.min.css">
<link type="text/css" rel="stylesheet" href="/assets/css/apexcharts.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,200;0,400;0,700;1,900&display=swap" rel="stylesheet">

<style>

body {
	background-color: #f9fafb;
	font-family: 'Poppins';
}

a {
	color: #E1372D;
	text-decoration: unset;
}

a:hover {
	color: #E1372D;
	opacity: 0.8;
	text-decoration: unset;
}

a:visited {
	color: #E1372D;
	text-decoration: unset;
}

a:active {
	color: #E1372D;
	opacity: 0.8;
	text-decoration: unset;
}

.casper-red {
	color: #E1372D;
}

.grey {
	color: #e9ecef;
}

.white {
	color: #fff;
}

.green {
	color: #2CA02C;
}

.tiny-img {
	width: 22px; height: auto;
}

.nav-item {
	font-size: 17px;
	margin: 0; padding: 0;
	font-weight: 600; text-transform: uppercase;
	padding-top: 12px;
	padding-bottom: 12px;
	padding-left: 10px;
	border-left: 6px solid transparent;
	cursor: pointer;
}

.nav-item-active {
	border-left: 6px solid #E1372D !important; opacity: 1 !important;
}

.nav-item:hover {
	border-left: 6px solid rgba(225, 55, 45, 0.3);
}

#logo-col {
	padding: 15px; height: 65px;
	border-bottom: 1px solid #e6e6e6;
}

#logo-col img {
	height: 100%; width: auto;
}

#nav-col {
	margin-top: 15px; padding: 0; width: 250px;
	border-right: 1px solid #e6e6e6;
	min-height: calc(100vh - 85px);
	position: relative;
}


/* main page */

#dashboard-header-wrap {
	padding: 15px; width: calc(100% - 250px);
	min-height: calc(100vh - 65px);
	position: relative;
}

#dashboard-header {
	width: 100%; background-color: #e9ecef;
	padding: 12px; border-radius: 0.25rem;
	font-size: 14px;
}

#usage-wrap {
	margin-top: 15px; width: calc(65% - 32px);
	padding: 5px; position: absolute; top: 50px;
	left: calc(35% + 20px);
}

#usage-inner {
	background-color: #fff; border-radius: 0.25rem;
	box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
	font-size: 16px;
}

#usage-header {
	border-bottom: 1px solid #e9ecef; padding: 15px;
}

#usage-body {
	display: flex; flex-direction: row;
	height: 155px; position: relative;
}

#usage-box-1,
#usage-box-2,
#usage-box-3
{
	display: flex; flex-direction: column;
	width: 25%; height: 100%;
	text-align: center;
	border-right: 1px solid #e9ecef;
	align-items: center; justify-content: center;
}

#usage-box-4 {
	display: flex; flex-direction: column;
	width: 25%; height: 100%; 
	text-align: center; 
	align-items: center; justify-content: center;
}

.usage-bar-grey {
	margin-top: 10px; margin-bottom: 5px;
	height: 5px; width: 80%; background-color: #ccc;
	border-radius: 4px; position: relative; overflow: hidden;
}

.usage-bar-green {
	height: 100%; width: 0; background-color: #2CA02C;
}

#api-key-wrap {
	margin-top: 15px; width: 35%; padding: 5px;
	position: absolute; top: 52px;
}

#api-key-inner {
	background-color: #fff; border-radius: 0.25rem;
	box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
	font-size: 14px; position: relative;
}

#api-key-header {
	border-bottom: 1px solid #e9ecef;
	padding: 15px; display: block;
}

#api-key-body {
	padding: 15px; display: block; height: 155px;
}

#api-key-box {
	border: 1px solid #e0e1e2; width: 100%; height: 125px; 
	background-color: #f9fafb; border-radius: 0.25rem; 
	padding: 12px; cursor: pointer;
}

#api-key-text {
	word-wrap: break-word; pointer-events: none;
}

#log-wrap {
	margin-top: 235px; width: 100%;
	padding: 5px; display: block;
}

#log-inner {
	background-color: #fff; border-radius: 0.25rem;
	box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
	font-size: 16px;
}

#log-header {
	border-bottom: 1px solid #e9ecef; padding: 15px;
}

#log-body {
	padding: 15px; overflow-y: scroll;
	display: block; height: 450px;
}

.badge-basic {
	float: right; height: 22px; width: 95px;
	border-radius: 10px; background-color: #e1e5ea; 
	font-size: 11px; font-weight: bold; 
	color: #68696b; text-align: center; 
	padding-top: 2px; pointer-events: none;
}

.badge-upgraded {
	float: right; height: 22px; width: 95px;
	border-radius: 10px; background-color: #E1372D; 
	font-size: 11px; font-weight: bold; 
	color: #fff; text-align: center; 
	padding-top: 2px; pointer-events: none;
}



/* plan page */

#plan-wrap {
	margin-top: 15px; width: 100%; padding: 5px;
	position: absolute; top: 52px;
	max-width: 800px;
}

#plan-inner {
	background-color: #fff; border-radius: 0.25rem;
	box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
	font-size: 14px; position: relative;
}

#plan-header {
	border-bottom: 1px solid #e9ecef;
	padding: 15px; display: block;
}

#plan-body {
	padding: 15px; display: block; height: auto;
}

#plan-type {
	font-weight: bold; opacity: 0.65;
}

.casper-btn {
	display: block; margin-top: 15px; width: 100%;
	max-width: 400px; background-color: #E1372D;
	color: #fff; font-weight: bold;
}

.casper-btn:hover {
	background-color: #E1372D;
	color: #fff; font-weight: bold;
	opacity: 0.8;
}

.neutral-btn {
	width: 100%;
	max-width: 200px; background-color: #b7babc;
	color: #333; font-weight: bold;
}

.neutral-btn:hover {
	background-color: #b7babc;
	color: #333; font-weight: bold;
	opacity: 0.8;
}



/* notification page */

#notification-wrap {
	margin-top: 15px; width: 100%; padding: 5px;
	position: absolute; top: 52px;
	max-width: 800px;
}

#notification-inner {
	background-color: #fff; border-radius: 0.25rem;
	box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
	font-size: 14px; position: relative;
}

#notification-header {
	border-bottom: 1px solid #e9ecef;
	padding: 15px; display: block;
}

#notification-body {
	padding: 15px; display: block; height: auto;
}

#notification-type {
	font-weight: bold; opacity: 0.65;
}



</style>