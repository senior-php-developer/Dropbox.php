<?php
include("db.php");
include("login.php");
check_login();

if (!empty($_FILES)) upload_files();
if (isset($GLOBALS['CURUSER'])) $CURUSER = $GLOBALS['CURUSER'];
if (!$CURUSER) die;

function get_owner() {
	$cur = $_REQUEST[cur];
	$tmp = mysql_fetch_assoc(mysql_query("SELECT user FROM folders WHERE id = $cur"));
	return $tmp[user];
}

function get_path($id) {
  $tmp = mysql_fetch_assoc(mysql_query("SELECT date, name FROM files WHERE id = $id"));
  $date = getdate(strtotime($tmp[date]));
  $GLOBALS[realname] = $tmp[name];
  return "../files/$date[year]/$date[mon]/$date[mday]/$id";
}

function get_zip($id) {
  $tmp = mysql_fetch_assoc(mysql_query("SELECT date FROM packs WHERE id = $id"));
  $date = getdate(strtotime($tmp[date]));
  $GLOBALS[zip] = $date[year].$date[mon].$date[mday].$id.".zip";
  return "../files/zip/".$GLOBALS[zip];
}

function get_quota($id) {
  $tmp[dir] = $id;
  while ($tmp[dir]) {
    $tmp = mysql_fetch_assoc(mysql_query("SELECT `limit`, dir FROM folders WHERE id = $tmp[dir]"));  
  }
	return $tmp[limit]*1000000;
}

$GLOBALS[dirsize] = 0;
function used_quota($id) {
  $files = mysql_fetch_assoc(mysql_query("SELECT SUM(size) as sum FROM files WHERE dir = '$id'"));
  $GLOBALS[dirsize] += $files[sum];
  $dirs = mysql_query("SELECT id FROM folders WHERE dir = '$id'");
  while ($tmp = mysql_fetch_assoc($dirs)) {
    used_quota($tmp[id]);
  }
  return $GLOBALS[dirsize];
}

function dec_quota($id) {
	$tmp = mysql_fetch_assoc(mysql_query("SELECT d.user, f.size FROM files f, folders d WHERE f.dir = d.id AND f.id = '$id'"));
	$sizeM = $tmp[size]/(1024*1024);
	$user = $tmp[user];
	mysql_query("UPDATE users SET used = used - '$sizeM' WHERE id = '$user'");
}
 
function del_dir($id) {
	$files = mysql_query("SELECT id FROM files WHERE dir = '$id'");
	while ($tmp = mysql_fetch_assoc($files)) {
		dec_quota($tmp[id]);
		$file = get_path($tmp[id]);
		@unlink($file);
	}
	mysql_query("DELETE FROM files WHERE dir = '$id'");
	$dirs = mysql_query("SELECT id FROM folders WHERE dir = '$id'");
	while ($tmp = mysql_fetch_assoc($dirs)) {
		del_dir($tmp[id]);
	}
	mysql_query("DELETE FROM folders WHERE id = '$id'");
}

/* folder functions */

function show_folder_settings($folder = 0) {
	if ($folder) {
		$tmp = mysql_fetch_assoc(mysql_query("SELECT * FROM folders WHERE id = '$folder'"));
		if ($tmp[limit] > 0) $limit = $tmp[limit];
	}
	print('<div id="d_folder_set" class="d_panel">
				 <input type="hidden" name="new" value="'.$_GET['new'].'">
				 <input type="hidden" name="folder" value="'.$folder.'">
				 Folder name:</br>
			   <input type="text" name="name" class="blur" style="width: 170px;" value="'.$tmp['name'].'"><br><br>
			   Size Limit<br>
			   None <input type="radio" name="limit" value="0" '.(!$limit ? 'checked="checked"':'').'> 
			   <input type="radio" id="limtrue" name="limit" value="1" '.($limit ? 'checked="checked"':'').'> <input type="text" name="limit_size" value="'.$limit.'" class="blur" style="width: 60px;" onclick="$(\'#limtrue\').attr(\'checked\',\'checked\');"> MB<br><br>
			   <button type="submit" class="b_submit lt" onclick="save_folder_set();">Save</button> <button type="cancel" class="b_cancel rt" onclick="$(\'#d_folder_set\').fadeOut(\'slow\');">Cancel</button>
			   <div class="clr"></div>');
}

function save_folder_settings() {
$img = 16;
	foreach($_POST as $key => $val)
		$$key = mysql_real_escape_string($val);
	$user = $GLOBALS['CURUSER']['id'];
	$date = date("Y-m-d H:i:s");
	if ($limit) $limit = $limit_size;
	if ($new) {
		mysql_query("INSERT INTO folders VALUES(null, '0', '$name','$user','$limit','$date', '$img')") or die("database error occured");
		print("folder created");
	} else {
		 mysql_query("UPDATE folders SET name = '$name', `limit` = '$limit', `date` = '$date', img = '$img' WHERE id = '$folder'");
		 print("folder settings changed");		
	}
}

function show_folders() {
	$user = $GLOBALS['CURUSER']['id'];
	$res = mysql_query("SELECT * FROM folders WHERE user = $user AND dir = '0' ORDER BY id DESC");
	while ($tmp = mysql_fetch_assoc($res)) {
		print("<div title='Created on $tmp[date]' gid='$tmp[id]'><img src='img/folder/$tmp[img].png'>$tmp[name]</div>");
	}
}

function delete_folder() {
	$id = mysql_real_escape_string($_GET['id']);
	mysql_query("DELETE FROM folders WHERE id = $id") or die("database error");
	print("folder deleted");
}

function show_folder_content() {
	$id = mysql_real_escape_string($_GET['cur']);
  $tmp = mysql_fetch_assoc(mysql_query("SELECT dir, name FROM folders WHERE id = '$id'"));
  print("<input type='hidden' id='curdirname' value='$tmp[name]'");
  if ($tmp[dir])
    print("<div gid='up' class='element brd5' title='go to parent dir' onclick='go_up(\"$tmp[dir]\");'><img src='../img/files/up.png'><span>..</span></div>");
  
	$dir_arr = mysql_query("SELECT id, name, img FROM folders WHERE dir = '$id' ORDER BY `name`");
	$file_arr = mysql_query("SELECT id, name FROM files WHERE dir = '$id' ORDER BY `name`");
	
	while($tmp = mysql_fetch_assoc($dir_arr)) {
		print("<div gid='$tmp[id]' class='element dir brd5' title='$tmp[name]'><img src='../img/files/dir.png'><span>$tmp[name]</span></div>");
	}
 	while($tmp = mysql_fetch_assoc($file_arr)) {
		$fileext = substr(strstr($tmp[name],'.'),1);
		$img = 'blank';
		$ext = array('arch' => array('zip','rar','ace','7z','uha'),
							   'audio' => array('wav','mp3','flac','midi','rm','m3u'),
							   'doc' => array('doc','txt','docx','xls','xlsx','pdf'),
							   'exec' => array('com','exe','bat'),
							   'pic' => array('bmp','jpg','gif','tiff','png'),
							   'video' => array('avi','mpg','mpeg','divx','mkv','flv'),
							   'web' => array('php','js','html','htm','css'));
		foreach($ext as $k => $v) {
			if (in_array($fileext,$v))
				$img = $k;
		}	
		$name = $tmp[name];
		if (strlen($tmp[name]) > 14) $name = substr($tmp[name],0,14).'..';
		print("<div gid='$tmp[id]' class='element file brd5' title='$tmp[name]'><img src='../img/files/$img.png'><span>$name</span></div>");
 	}
}

/* directory functions */

function show_dir_settings($new = 0) {
	foreach($_REQUEST as $key => $val)
		$$key = mysql_real_escape_string($val);
	if ($dir)
		$tmp = mysql_fetch_assoc(mysql_query("SELECT name FROM folders WHERE id = '$dir'"));
	print('<div id="d_dir_set" class="d_panel">
				 <input type="hidden" name="new" value="'.$new.'">
				 <input type="hidden" name="dir" value="'.$dir.'">
				 Directory name:</br>
			   <input type="text" name="name" class="blur" style="width: 170px;" value="'.$tmp[name].'"><br><br>
			   <button type="submit" class="b_submit lt" onclick="save_dir_set();">Save</button> <button type="cancel" class="b_cancel rt" onclick="$(\'#d_dir_set\').fadeOut(\'slow\');">Cancel</button>
			   <div class="clr"></div>');
}

function save_dir_settings() {
	foreach($_REQUEST as $key => $val)
		$$key = mysql_real_escape_string($val);
  $res = mysql_query("SELECT id FROM folders WHERE name = '$name' AND dir = '$cur' LIMIT 1");
  if (mysql_num_rows($res) > 0)
    die("directory already exist");
	if ($new) {
		$user = get_owner();
		$date = date("Y-m-d H:i:s");
		mysql_query("INSERT INTO folders VALUES (null, '$cur', '$name', '$user', '0', '$date', '0')") or die("database error");
		print("directory created");
	} else {
		mysql_query("UPDATE folders SET name = '$name' WHERE id = '$dir'");
		print("directory renamed");
	}
}

function delete_dir() {
  $id = mysql_real_escape_string($_POST['id']);
  mysql_query("DELETE FROM folders WHERE id = '$id'");
  print("directory deleted");
}

function show_dir_content() {
  
}

/* file functions */

function show_upload_file() {
  $dir = mysql_real_escape_string($_REQUEST['cur']);
  $user = mysql_real_escape_string($_REQUEST['user']);
  $tmp = mysql_fetch_assoc(mysql_query("SELECT quota, filesize, used FROM users WHERE id = '$user'"));
	$quota = get_quota($dir);
	$used = used_quota($dir);
	$rem = $quota - $used;
	$rem_g = ($tmp[quota] - $tmp[used])*1000000;
	$file_g = $tmp[filesize]*1000000;
	print("<div class='stats'>
        <input type='hidden' id='dl' value='$rem'>
        <input type='hidden' id='gl' value='$rem_g'>
        <input type='hidden' id='fl' value='$file_g'>
         
         <b>Folder Quota</b> <br>
			   Limit: ".number_format($quota/1000000,2)." Mb<br>
	       Remaining: ".number_format($rem/1000000,2)." Mb<br>
	       <b>Global Quota</b><br>
	       Remaining: ".number_format($rem_g/1000000,2)." Mb<br> 
	       Max filesize: ".number_format($file_g/1000000,2)." Mb</div>");
	
	print('<br><input type="file" name="fileMulti" id="fileMulti"/>');
}

function download_file() {
  $id = mysql_real_escape_string($_GET[id]);
  $file = get_path($id);
  mysql_query("UPDATE files SET num_down = num_down + '1' WHERE id = '$id'");
	header('Content-Disposition: attachment; filename="'.$GLOBALS[realname].'"');
	readfile($file);
}

function show_file_settings() {
	$id = mysql_real_escape_string($_GET[id]);
	$tmp = mysql_fetch_assoc(mysql_query("SELECT name FROM files WHERE id = $id"));
	print('<div id="d_file_set" class="d_panel">
				 File name:</br>
				 <input type="hidden" name="id" value="'.$id.'">
			   <input type="text" name="name" class="blur" style="width: 170px;" value="'.$tmp[name].'"><br><br>
			   <button type="submit" class="b_submit lt" onclick="save_file_set();">Save</button> <button type="cancel" class="b_cancel rt" onclick="$(\'#d_file_set\').fadeOut(\'slow\');">Cancel</button>
			   <div class="clr"></div>');
}

function save_file_settings() {
	$id = mysql_real_escape_string($_GET[id]);
	$name = mysql_real_escape_string($_POST[name]);
	mysql_query("UPDATE files SET name = '$name' WHERE id = '$id'") or die("database error");
	print("file renamed");
}

function delete_file() {
	$id = mysql_real_escape_string($_GET[id]);
	$file = get_path($id);
	dec_quota($id);
	mysql_query("DELETE FROM files WHERE id = '$id'") or die("database error");
	@unlink($file);
	print("file deleted");
}

function delete_all_items() {
	$i = 0; 
	while($_POST['dir'.$i]) {
		del_dir($_POST['dir'.$i]);
		$i++;
	}
	$k = 0;
	while ($_POST['file'.$k]) {
		$id = $_POST['file'.$k];
		$file = get_path($id);
		dec_quota($id);
		@unlink($file);
		mysql_query("DELETE FROM files WHERE id = '$id'");
		$k++;
	}
	print("files deleted");
}

/* misc functions */

function show_information() {
	if ($_GET['many']) { // more than 1 files selected
	$files = 0;
  $dirs = 0;
		foreach($_POST as $k => $v) {
			if (substr($k,0,4) != 'file') 
        $dirs++;
      else {
        $fileid[] = $v;
        $files++;
      }      
		}
    $tmp =  mysql_fetch_assoc(mysql_query("SELECT SUM(size) as size FROM files WHERE id IN (" .implode(',',$fileid). ")"));
    $size = number_format($tmp[size]/1024,3) .' Kb';
		print("<center><b> $dirs dirs and $files files </b></center>
           Size: $size<br>");
	} else { // 1 file selected
		$file = mysql_real_escape_string($_REQUEST[id]);
		$tmp = mysql_fetch_assoc(mysql_query("SELECT f.*, d.name as dir, u.email as user FROM files f, folders d, users u WHERE f.dir = d.id AND f.user = u.id AND f.id = '$file'"));
    $size = number_format($tmp[size]/1024,3) .' Kb';
    $dis = $tmp[disabled] ? 'Yes' : 'No';
    print("<center><b>$tmp[name]</b></center>
           Owner: $tmp[user] <br>
           Directory: $tmp[dir]<br>
           Uploaded: $tmp[date]<br>
           Size: $size<br>
           Downloaded: $tmp[num_down]<br>
           Disabled: $dis");
	}
}

function show_dir_information() {
  $dir = mysql_real_escape_string($_REQUEST[id]);
  $tmp = mysql_fetch_assoc(mysql_query("SELECT d.name, d.date, d.limit, u.email, p.name as parent FROM folders d, folders p, users u WHERE d.dir = p.id AND d.user = u.id AND d.id = '$dir'"));
    $dis = $tmp[disabled] ? 'Yes' : 'No';
    print("<center><b>$tmp[name]</b></center>
           Owner: $tmp[email]<br>
           Folder: $tmp[parent]<br>
           Created: ".substr($tmp[date],0,10)."<br>
           Limit: ".($tmp[limit]*1000)." Kb<br>
           Used: ".(used_quota($dir)/1000))." Kb";
}

function zip_download() {
	$realpath = '/mnt/local/home/cumuli2/cumuli2/files';
	$sql_date = date("Ymd");
	$date = date("Ynj");
	$owner = $_REQUEST[user];
	mysql_query("INSERT INTO packs VALUES (null, '$sql_date', '$owner')") or die(mysql_error());
	$pack_id = mysql_insert_id();
	$pack_dir = "zip/".$date.$pack_id;
	$pack = $date.$pack_id;
	exec("cd $realpath/zip; mkdir $pack");
	
	for($i=0; $i<$_POST['files']; $i++) {
	  $file = substr(get_path($_POST['file'.$i]),9);
	  $real = $GLOBALS[realname];
	  exec("cd $realpath; ln $file $pack_dir/$real");
	}
	
	for($i=0; $i<$_POST['dirs'];$i++) {
		$id = $_POST['dir'.$i];
		$tmp = mysql_fetch_assoc(mysql_query("SELECT name FROM folders WHERE id = '$id'"));
		$dir = $tmp[name];
		exec("cd $realpath/$pack_dir; mkdir $dir");
		$res = mysql_query("SELECT id FROM files WHERE dir = '$id'");
		
		while ($tmp2 = mysql_fetch_assoc($res)) {
			$file = substr(get_path($tmp2[id]),9);
			$real = $GLOBALS[realname];
			exec("cd $realpath; ln $file $pack_dir/$dir/$real");
		}
	}
	
	exec("cd $realpath/zip; zip -rm ".$pack.".zip ".$pack);
  sleep(2);
	print($pack_id);	
}

function down_zip() {
  $id = mysql_real_escape_string($_GET[id]);
  $file = get_zip($id);
  mysql_query("DELETE FROM packs WHERE date < DATE(NOW())");
	header("Content-Disposition: attachment; filename=$GLOBALS[zip]");
	readfile($file);
}

function suggest_name() {
  $q = strtolower($_REQUEST["q"]);
  if (!$q) return;
  $user = $_REQUEST[user];
  $res = mysql_query("SELECT id, email FROM invites WHERE email LIKE '%$q%' AND user = '$user'");
  if (mysql_num_rows($res) > 0) 
    while ($arr = mysql_fetch_assoc($res)) {
      print('<li onclick="suggest_fill(\''.$arr['email'].'\',\'#txt_user\',\''.$arr['id'].'\');">'.$arr['email'].'</li>');
    }
}

/* share functions */

function show_share_settings() {
	$id = mysql_real_escape_string($_REQUEST[cur]);
	print('<div id="d_share_set" class="d_panel">
					<div id="newshare">
					 <input type="hidden" name="folder" value="'.$id.'">
					 Send invitation to: <br>
					 <input type="text" name="user" id="txt_user" class="blur" style="width: 170px;"><br>
					 <div class="suggestionsBox hid" id="suggestions">
      		 	 <img src="img/upArrow.png" style="position: relative; top: -12px; left: 30px" alt="upArrow" />
      			 <div class="suggestionList" id="autoSuggestionsList"></div>
	    		 </div>
					 Permissions:<br>
					 <input type="checkbox" name="perm[1]" value="read">R <input type="checkbox" name="perm[2]" value="write">W <input type="checkbox" name="perm[4]" value="delete">D<br>
					 <button type="submit" class="b_submit" onclick="save_share_set();">Share</button>
					</div>
					 <hr noshade="noshade">
					 <div id="shares">');
	$res = mysql_query("SELECT s.perm, u.email, s.id FROM shares s, users u WHERE s.folder = '$id' AND s.user = u.id");
	if (mysql_num_rows($res) > 0) {
		$i = 0;
		while ($tmp = mysql_fetch_assoc($res)) {
			$i++;
			$rchk = ''; $wchk = ''; $dchk = '';
			if (($tmp[perm] == 1) || ($tmp[perm] == 3) || ($tmp[perm] == 5) || ($tmp[perm] == 7))
				$rchk = "checked='checked'";
			if (($tmp[perm] == 2) || ($tmp[perm] == 3) || ($tmp[perm] == 6) || ($tmp[perm] == 7))
				$wchk = "checked='checked'";
			if (($tmp[perm] == 4) || ($tmp[perm] == 5) || ($tmp[perm] == 6) || ($tmp[perm] == 7))
				$dchk = "checked='checked'";
		$user = explode('@',$tmp[email]);
		print("<div class='suser' gid='$tmp[id]'>
						<img src='img/del_cross.png' onclick=\"del_share('$tmp[id]');\">
						<b>$user[0]</b> 
					 	<div class='perms'>
					 	 <input type='hidden' name='id$i' value='$tmp[id]' class='hidid'>
					 	 <input type='checkbox' name='perm".$i."[1]' $rchk> R <input type='checkbox' name='perm".$i."[2]' $wchk> W <input type='checkbox' name='perm".$i."[4]' $dchk> D 
					 	</div>
					 </div>");
		}
		print('<button type="submit" class="b_submit" onclick="save_share_old();">Save</button>');
	} 
	print('</div></div>');
	
}

function save_share_settings() {
	$folder = mysql_real_escape_string($_POST[folder]);
	foreach($_POST[perm] as $k => $v)
		$perms += $k;
	$mail = mysql_real_escape_string($_POST[user]);
	$inviter = $_COOKIE[user];
	mysql_query("INSERT INTO invites VALUES (null, '$inviter', '$mail')");
	$res = mysql_query("SELECT id FROM users WHERE email = '$mail'");
	if (mysql_num_rows($res) > 0) { //existing user
		$tmp = mysql_fetch_assoc($res);
		mysql_query("INSERT INTO shares VALUES (null, '$folder', '$tmp[id]', '$perms')");
		$subject = "Cumuli - invitation";
  	$headers = "From: no.reply@c.umu.li\r\n"."MIME-Version: 1.0\r\n"."Content-type: text/html; charset=iso-8859-1\r\n";
  	$body = 'You have been invited to use shared folder at cumuli. <br>
  					 To access shared folder follow to your account and find it in the Shared Folders box.';
  	if (mail($mail,$subject,$body,$headers)) 
			print("folder shared");
		else
			print("error sending mail");
	} else { // create new user
		$pass = generator();
		$passhash = md5(md5($mail).md5($pass));
		$date = date("Y-m-d");
		mysql_query("INSERT INTO users (email, password, `date`, registered, `quota`, filesize) VALUES ('$mail','$passhash','$date', '0', '100', '0')") or die("database error");
		$user = mysql_insert_id();
		mysql_query("INSERT INTO shares VALUES (null, '$folder', '$user', '$perms')");
		$subject = "Cumuli - invitation";
  	$headers = "From: noreply@c.umu.li\r\n"."MIME-Version: 1.0\r\n"."Content-type: text/html; charset=iso-8859-1\r\n";
  	$body = 'You have been invited to use shared folder at cumuli. <br>
  					 To access shared folder follow this <a href="http://rocknroll.c.umu.li/inc/login.php?do=confirm&user='.$user.'&str='.$passhash.'">link</a> and then login with following credentials:<br>
  					 e-mail:'.$mail.'<br> password:'.$pass.'<br>
  					 You can change password in the login box dialog!<br>';
  if (mail($mail,$subject,$body,$headers))
  	print("invitation sent");
  else
  	print("error sending mail");
	}
}

function save_share_old() {
  foreach($_POST as $k => $v) {
    if (substr($k,0,2) == 'id')
      $ss[] = substr($k,2);
  }
  foreach($ss as $k => $v) {
    $id = mysql_real_escape_string($_POST['id'.$v]);
    $perm = 0;
    foreach($_POST['perm'.$v] as $kk => $vv)
      $perm += $kk;
    mysql_query("UPDATE shares SET perm = '$perm' WHERE id = '$id'") or die("database error");
  }
  print("changes saved");
}

function delete_share() {
	$id = mysql_real_escape_string($_POST[id]);
	mysql_query("DELETE FROM shares WHERE id = '$id'") or die("database error");
	print("share deleted");
}

function show_shared_folders() {
	$user = $GLOBALS[CURUSER][id];
	$res = mysql_query("SELECT s.*, f.name, f.img FROM shares s, folders f WHERE s.user = $user AND s.folder = f.id ORDER BY id DESC");
	while ($tmp = mysql_fetch_assoc($res)) {
		print('<div perm="'.$tmp['perm'].'" gid="'.$tmp['folder'].'"><img src="img/folder/'.$tmp['img'].'.png">'.$tmp['name'].'</div>');
	}
}

function show_prof() {
  $user = $_REQUEST[user];
  $tmp = mysql_fetch_assoc(mysql_query("SELECT * FROM users WHERE id = '$user'"));
  print('Old Password: <input type="password" name="curpass"> New Password: <input type="password" name="newpass"> 
         <button class="b_submit" type="submit" onclick="change_pass()">Change</button>');
}

if ($_GET['do']=='show_folder_set') show_folder_settings(mysql_real_escape_string($_GET['id']));
if ($_GET['do']=='save_folder_set') save_folder_settings();
if ($_GET['do']=='delete_folder') delete_folder();
if ($_GET['do']=='show_folders') show_folders();
if ($_GET['do']=='show_folder_content') show_folder_content();

if ($_GET['do']=='show_dir_set') show_dir_settings($_GET['new']);
if ($_GET['do']=='save_dir_set') save_dir_settings();
if ($_GET['do']=='delete_dir') delete_dir();

if ($_GET['do']=='show_fileup') show_upload_file();
if ($_GET['do']=='down_file') download_file();
if ($_GET['do']=='show_file_set') show_file_settings();
if ($_GET['do']=='save_file_set') save_file_settings();
if ($_GET['do']=='delete_file') delete_file();

if ($_GET['do']=='delete_all') delete_all_items();

if ($_GET['do']=='show_info') show_information();
if ($_GET['do']=='show_dir_info') show_dir_information();
if ($_GET['do']=='zip_down') zip_download();
if ($_GET['do']=='down_zip') down_zip();
if ($_GET['do']=='suggest') suggest_name();

if ($_GET['do']=='show_share_set') show_share_settings();
if ($_GET['do']=='save_share_set') save_share_settings();
if ($_GET['do']=='save_share_old') save_share_old(); 
if ($_GET['do']=='del_share') delete_share();
?>