<?php
include("inc/storage.php");
if (!$CURUSER) {
	check_login();
	if (isset($GLOBALS['CURUSER'])) $CURUSER = $GLOBALS['CURUSER'];
	else header('Location: index.php');
}

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
<link rel="stylesheet" type="text/css" href="css/jquery.css">
<link rel="stylesheet" type="text/css" href="plugin/uploadify/uploadify.css">
<!--[if lt IE 8]>
<link rel="stylesheet" type="text/css" href="css/ie.css">
<![endif]-->
<title><?=$title?></title>
</head>
<!-- BODY -->
<body>
<div class="header">
	<div class="canvas">
		<a class="login_box brd5" id="prof_but" href="javascript:void(0)" onclick="box.load();">profile</a> <a class="login_box brd5" id="logout" href="#" onclick="logout(); return false;">logout</a>
	</div>
</div>

<div class="middle">
	<div class="canvas">
		<div class="main_content brd10">
		  <div class="left_panel">
		  	<div id="own_folders" class="p_content brd10">
		  		<h2>My Folders:</h2>
		  		<hr noshade="noshade">
		  		<div class="toolbar">
		  			<img src="img/add_folder.png" id="b_show_addfolder" title="Add folder">
		  		</div>
		  		<div class="ajax folder_list"></div>
		  	</div>
		  	
		  	<div id="shared_folders" class="p_content brd10">
		  		<h2>Shared Folders:</h2>
		  		<hr noshade="noshade">
		  		<div class="ajax folder_list"><?show_shared_folders();?></div>
		  	</div>
		  </div>
		  
		  <div class="center_panel">
		  	<div id="folder_contents" class="p_content brd10">
		  		<h2>Storage Area</h2>
		  		<hr noshade="noshade">
		  		<div class="toolbar hid">
		  			<img src="img/new_folder.png" id="b_show_add_dir" title="New directory">
		  			<img src="img/upload.png" id="b_file_upload" title="Upload file(s)">
		  		</div>   
		  		<div class="ajax">ddwdad</div>
		  	</div>
		  </div>
		  
		  <div class="right_panel">
		  	<div id="folder_settings" class="p_content brd10">
		  		<h2>Settings:</h2>
		  		<hr noshade="noshade">
		  		<div class="ajax"></div>
		  		<div class="queue hid"></div>
		  	</div>
		  	
		  	<div id="folder_information" class="p_content brd10">
		  		<h2>Information:</h2>
		  		<hr noshade="noshade">
		  		<div class="ajax"></div>
		  	</div>
		  	
		  	<div id="queue_list" class="p_content brd10">
		  		<h2>Download Queue:</h2>
		  		<hr noshade="noshade">
		  		<div class="ajax"></div>
		  		<div id="buts" class="hid">
		  			<a onclick="queue_down();">Download</a> | <a onclick="queue_clear();">Clear Queue</a>
		  		</div>
		  	</div>
		  </div>
		  
		  
		  <div class="clr"></div>
		</div>
	</div>
</div>

<div class="footer">
	<div class="canvas">
    <a href="info.php" target="_blank">Info</a> | <a href="blog.c.umu.li" target="_blank">Blog</a> | <a href="javascript:void(0);" onclick="$('#fdbk_tab').click();">Support</a>
	</div>
</div>

<iframe id="dwn_frm" width="0" height="0"></iframe>

<!-- dialogs -->
<div class="hid" id="p_info"></div>
<div class="hid" id="p_ajax"><img src="img/ajaxload.gif"></div>

<!-- menus -->
<ul id="folder_menu" class="contextMenu">
  <li class="open_folder"><a href="#open">Open</a></li>
  <li class="settings"><a href="#settings">Settings</a></li>
  <li class="share"><a href="#share">Share</a></li>
  <li class="delete_folder"><a href="#delete">Delete</a></li>
</ul>

<ul id="dir_menu" class="contextMenu">
  <li class="open_folder"><a href="#open">Open</a></li>
  <li class="queue"><a href="#queue">Queue</a></li>
  <li class="rename"><a href="#rename">Rename</a></li> 
  <li class="delete"><a href="#delete">Delete</a></li>
</ul>

<ul id="file_menu" class="contextMenu">
  <li class="download"><a href="#download">Download</a></li>
  <li class="queue"><a href="#queue">Queue</a></li>
  <li class="rename"><a href="#rename">Rename</a></li> 
  <li class="delete"><a href="#delete">Delete</a></li>
</ul>

<ul id="dir_menus" class="contextMenu"></ul>
<ul id="file_menus" class="contextMenu"></ul>

<div class="overlay" id="profile"><div class="close"></div><div class="content"><?show_prof();?></div></div>

<!-- scripts -->
<script type="text/javascript" src="js/jquery/jquery.js"></script>
<script type="text/javascript" src="js/jquery/jquery.menu.js"></script>
<script type="text/javascript" src="js/jquery/jquery.suggest.js"></script>
<script type="text/javascript" src="js/jquery/jquery.tools.js"></script>
<script type="text/javascript" src="plugin/uploadify/jquery.uploadify.js"></script>
<script type="text/javascript" src="js/storage.js"></script>
<!--[if lt IE 8]>
<script type="text/javascript" src="js/ie8.js"></script>
<![endif]-->
<script type="text/javascript">
  var curuser = <?=$CURUSER[id]?>;
</script>

<!-- feedback widget -->
<script type="text/javascript" charset="utf-8">
  var is_ssl = ("https:" == document.location.protocol);
  var asset_host = is_ssl ? "https://s3.amazonaws.com/getsatisfaction.com/" : "http://s3.amazonaws.com/getsatisfaction.com/";
  document.write(unescape("%3Cscript src='" + asset_host + "javascripts/feedback-v2.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript" charset="utf-8">
  var feedback_widget_options = {};
  feedback_widget_options.display = "overlay";  
  feedback_widget_options.company = "cumuli";
  feedback_widget_options.placement = "right";
  feedback_widget_options.color = "#222";
  feedback_widget_options.style = "idea";
  var feedback_widget = new GSFN.feedback_widget(feedback_widget_options);
</script>

</body>
</html>
