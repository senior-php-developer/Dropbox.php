<?php
include("inc/index.php");
include("inc/login.php");
check_login();
if (isset($GLOBALS['CURUSER'])) $CURUSER = $GLOBALS['CURUSER'];
if ($CURUSER) header('Location: storage.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="description" content="#">
<meta http-equiv="cahe-control" content="cache">
<meta http-equiv="Content-Language" content="en">
<meta name="keywords" content="PLEASE ENTER YOUR KEYWORDS HERE">
<!-- CSS -->
<link rel="stylesheet" type="text/css" href="css/reset.css">
<link rel="stylesheet" type="text/css" href="css/main.css">
<!--[if lt IE 8]>
<link rel="stylesheet" type="text/css" href="css/ie.css">
<![endif]-->
<title><?=$title?></title>
</head>
<!-- BODY -->
<body>
<div class="header">
	<div class="canvas">
<?  if ($CURUSER)
			print('<a class="login_box brd5" id="logout" href="#" onclick="return false;">logout</a>');
    else
    	print('<a class="login_box brd5" id="login" href="#" onclick="return false;">login</a>');
?>
	</div>
</div>

<div class="middle">
	<div class="canvas">
		<div class="main_content brd10">
		<h2><img src="img/cumulogo.gif"></h2>
			<div class="cloud_box brd10 reg" id="box1" rate="1">
				<div class="cloud">
					<h3>100 MB</h3>
				</div>
				<p>no filesize limit!</p>
			</div>
			
			<div class="cloud_box brd10 reg" id="box2" rate="2">
				<div class="cloud">
					<h3>1000 MB</h3>
				</div>
				<p>10MB per file</p>
			</div>
			
			<div class="cloud_box brd10 soon" id="box3" rate="3">
				<div class="cloud">
					<h3>???</h3>
				</div>
				<p>customize</p>
			</div>
			
			<div class="clear">&nbsp;</div>
		</div>
	</div>
</div>

<div class="footer">
	<div class="canvas">
	</div>
</div>
</body>

<!-- DIALOGS -->
<div class="d_box hid brd10" id="d_reg">
	Your E-mail<br>
	<input type="text" class="blur" name="mail"><br><br>
	Choose Password<br>
	<input type="password" class="blur" name="pass"><br><br>
	<? require_once('inc/recaptchalib.php');
	 $publickey = "6LfMuQYAAAAAANwkeykCqj7_Truw2vW-bGHELgS8"; 
	 echo recaptcha_get_html($publickey);
	?>
	<button class="b_submit lt">Register</button> <button class="b_cancel rt">Cancel</button> 
</div>

<div class="d_box hid brd10" id="d_login">
	Your E-mail<br>
	<input type="text" class="blur" name="mail"><br>
	<span id="password">
		<br>Your Password<br>
		<input type="password" class="blur" name="pass"><br>
	</span>
	<span class="hid" id="code">
		<br>Confirmation Code<br>
		<input type="text" class="blur" name="code"><br>
	</span><br>
	<button class="b_submit lt" id="b_login">Login</button> 
	<button class="b_submit lt hid" id="b_reset">Reset</button> 
	<button class="b_submit lt hid" id="b_change">Change</button> 
	<button class="b_cancel rt">Cancel</button><br>
	<div class="clr"></div>
	<a href="#" onclick="show_reset_dlg(); return false;">Lost password?</a> 
</div>

<div class="d_box hid brd10" id="d_soon">
  <div class="close"></div>
  <br>
	<center><h1>Not Yet Availible!</h1><br>
	<h2>Coming soon..</h2></center>
</div>

<div class="hid" id="p_info"></div>
<div class="hid" id="p_ajax"><img src="img/ajaxload.gif"></div>

<!-- JAVASCRIPT -->
<script type="text/javascript" src="js/jquery/jquery.js"></script>
<script type="text/javascript" src="js/index.js"></script>
<!--[if lt IE 8]>
<script type="text/javascript" src="js/ie8.js"></script>
<![endif]-->
</html>
