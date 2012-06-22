<?php
include("db.php");

if (!empty($_FILES)) upload_files();

function get_path($id) {
  $tmp = mysql_fetch_assoc(mysql_query("SELECT date, name FROM files WHERE id = $id"));
  $date = getdate(strtotime($tmp[date]));
  $GLOBALS[realname] = $tmp[name];
  return "../files/$date[year]/$date[mon]/$date[mday]/$id";
}

function upload_files() {
//init file variables
  $dir = mysql_real_escape_string($_REQUEST[cur]);
  $user = mysql_real_escape_string($_REQUEST[user]);
  $temp_file = $_FILES['Filedata']['tmp_name'];
  $name = $_FILES['Filedata']['name'];
//check file size limit  
  $tmp = mysql_fetch_assoc(mysql_query("SELECT filesize FROM users WHERE id = '$user'"));
  $size = filesize($temp_file);
  $sizeM = $size / (1000000);
  if ($sizeM > $tmp[filesize]) return;
//add quota for dir owner
  $tmp = mysql_fetch_assoc(mysql_query("SELECT user FROM folders WHERE id = '$dir'"));
  $owner = $tmp[user];  
  mysql_query("UPDATE users SET used = used + '$sizeM' WHERE id = '$owner'");
//add file record to db
  $date = date("Y-m-d");
  mysql_query("INSERT INTO files VALUES (null,'$user','$date','$dir','$name','$size','0','0')") or die("database error");
//move file to fs  
  $target = get_path(mysql_insert_id());
  $path = dirname($target);
  if (!is_dir($path)) @mkdir($path, 0755, true);
  move_uploaded_file($temp_file,$target);
//print result
  if (file_exists($target)) 
    print("file(s) uploaded");
  else
    print("filesystem error");
}