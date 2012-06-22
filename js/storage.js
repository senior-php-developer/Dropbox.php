$(init_storage);
var cur = 0;
var files = new Array();
var dirs = new Array();
var share_user = 0;
var share_mode;
var timer = 0;
var box = 0;

function init_storage() {
	$("#b_show_addfolder").click(show_add_folder);
	$("#b_show_add_dir").click(show_add_dir);
	$("#b_file_upload").click(show_file_upl);
	init_folders();
	init_shares();
 	box = $("#profile").overlay({api:true, zIndex: 99, fadeInSpeed: 'slow', closeOnClick: false});
	
	$.ajaxSetup({
		beforeSend: function(){$("#p_ajax").show();},
    success: function(){$("#p_ajax").hide();},
    complete: function(){$("#p_ajax").hide();}
  });
  
  // loading content
  $("#own_folders .ajax").load('inc/storage.php?do=show_folders',init_folders);
}

function init_panels() {
	$(".d_panel input").focus(function(){	$(this).removeClass("blur").addClass("focus");});
	$(".d_panel input").blur(function(){	$(this).removeClass("focus").addClass("blur");});
}

function init_folders() {
	$("#own_folders .folder_list div").click(open_folder);
	$("#own_folders .folder_list div").hover(function(){$(this).children("img").addClass("hover");},function(){$(this).children("img").removeClass("hover");});
	$("#own_folders .folder_list div").contextMenu({menu: 'folder_menu'}, menu_folders);
}

function init_shares() {
	$("#shared_folders .folder_list div").click(open_share);
	$("#shared_folders .folder_list div").hover(function(){$(this).children("img").addClass("hover");},function(){$(this).children("img").removeClass("hover");});
}

function init_content() {
	if (share_mode) {
		$("#folder_contents .dir").contextMenu({menu: 'dir_menus'}, menu_dir);
		$("#folder_contents .file").contextMenu({menu: 'file_menus'}, menu_file);
		$("#folder_contents .element").click(select_share);
	} else {
		$("#folder_contents .dir").contextMenu({menu: 'dir_menu'}, menu_dir);
		$("#folder_contents .file").contextMenu({menu: 'file_menu'}, menu_file);
		$("#folder_contents .element").click(select_element);	
	}
	files.length = 0; dirs.length = 0;
}

/* menu behavior */
function menu_folders(action, el, pos) {
  var id = $(el).attr('gid');
  if (action == 'open') {
  	$(el).click();
	}
	if (action == 'settings') {
    $(el).click();
		$("#folder_settings .ajax").load('inc/storage.php?do=show_folder_set&new=0&id='+id,function(){
			init_panels();
		});
	}
	if (action == 'share') {
    $(el).click();
		$("#folder_settings .ajax").load('inc/storage.php?do=show_share_set&cur='+id,function(){
			init_panels();
			$("#newshare input[name='perm[4]']").click(function(){ $("#newshare input[name='perm[1]']").attr('checked','checked'); });
			$("#txt_user").keyup(function(){
				clearTimeout(timer);
				timer = setTimeout("suggest_lookup();",900);
			});
		});
	}
	if (action == 'delete') {
		if (confirm("Are you sure want to delete this folder? All contents inside will be deleted also!"))
			$.post('inc/storage.php?do=delete_folder&id='+id,function(reply){
				$("#own_folders .ajax").load('inc/storage.php?do=show_folders',init_folders);
				if (id == cur) hide_contents();
				$("#p_info").text(reply).slideDown("slow",close_ajax);
			});
	}
}

function menu_dir(action, el, pos) {
	$("#folder_information .ajax").html('');
	var id = $(el).attr('gid');
	if (action == 'open') {
		cur = id;
		$("#folder_contents h2").text('Contents of '+$(el).text());
		$("#folder_contents .ajax").load('inc/storage.php?do=show_folder_content&cur='+cur,function(){
			init_content();
		});
	}
  if (action == 'queue') {
    var name = $(el).text();
    $("#queue_list .ajax").append('<div class="qdir" gid="'+id+'"><img src="img/del_cross.png">'+name+'</div>');
    $("#queue_list .ajax div img").click(queue_del);
    $("#queue_list div#buts").show();
  }
	if (action == 'rename') {
		$('#folder_settings .ajax').load('inc/storage.php?do=show_dir_set&new=0',{dir: id}, init_panels);
	}
	if (action == 'delete') {
		if (confirm("Are you sure want to delete this directory? All contents inside will be deleted also!"))
			$.post('inc/storage.php?do=delete_dir',{id:id},function(reply){
				$("#folder_contents .ajax").load('inc/storage.php?do=show_folder_content&cur='+cur,init_content);
				$("#p_info").text(reply).slideDown("slow",close_ajax);
		});
	}
	if (action == 'queue_all')
		queue_add();
		
}

function menu_file(action, el, pos) {
	$("#folder_information .ajax").html('');
	var id = $(el).attr('gid');
	if (action == 'download') {
		$("#dwn_frm").attr('src','inc/storage.php?do=down_file&id='+id);
	}
	if (action == 'queue') {
		var name = $(el).text();
		$("#queue_list .ajax").append('<div class="qfile" gid="'+id+'"><img src="img/del_cross.png">'+name+'</div>');
		$("#queue_list .ajax div img").click(queue_del);
		$("#queue_list div#buts").show();
	}
	if (action == 'rename') {
		$('#folder_settings .ajax').load('inc/storage.php?do=show_file_set&id='+id, init_panels);
	}
	if (action == 'delete') {
		if (confirm("Are you sure want to delete this file? It cannot be restored back!"))
			$.post('inc/storage.php?do=delete_file&id='+id, function(reply){
				$("#folder_contents .ajax").load('inc/storage.php?do=show_folder_content&cur='+cur,init_content);
				$("#p_info").text(reply).slideDown("slow",close_ajax);
		});	
	}
	if (action == 'queue_all')
		queue_add();
	if (action == 'delete_all')
		delete_all();
		show_menu_def();
}

function show_menu_def() {
	$("#file_menu").html('<li class="download"><a href="#download">Download</a></li><li class="queue"><a href="#queue">Queue</a></li><li class="rename"><a href="#rename">Rename</a></li><li class="delete"><a href="#delete">Delete</a></li>');
	$("#dir_menu").html('<li class="open_folder"><a href="#open">Open</a></li><li class="queue"><a href="#queue">Queue</a><li class="rename"><a href="#rename">Rename</a></li><li class="delete"><a href="#delete">Delete</a></li>');
}
function show_menu_sel() {
	$("#file_menu").html('<li class="queue"><a href="#queue_all">Queue</a></li><li class="delete"><a href="#delete_all">Delete</a></li>');
	$("#dir_menu").html('<li class="queue"><a href="#queue_all">Queue</a></li><li class="delete"><a href="#delete_all">Delete</a></li>');
}
function show_menu_share(perm) {
	$("#dir_menus").html('');
	$("#file_menus").html('');
  $("#folder_contents .toolbar").html('<img src="img/new_folder.png" id="b_show_add_dir" title="New directory" onclick="show_add_dir()"><img src="img/upload.png" id="b_file_upload" title="Upload file(s)" onclick="show_file_upl()">');
  
	if ((perm == 1) || (perm == 3) || (perm == 5) || (perm == 7)) {
    $("#file_menus").append('<li class="download"><a href="#download">Download</a></li><li class="queue"><a href="#queue">Queue</a></li>');
    $("#dir_menus").append('<li class="open_folder"><a href="#open">Open</a></li><li class="queue"><a href="#queue">Queue</a></li>');
  }
	if ((perm == 2) || (perm == 3) || (perm == 6) || (perm == 7))
		$("#folder_contents .toolbar").show();
  else
    $("#folder_contents .toolbar").html('');
    
	if ((perm == 4) || (perm == 5) || (perm == 6) || (perm == 7)) {
		$("#dir_menus").append('<li class="rename"><a href="#rename">Rename</a></li><li class="delete"><a href="#delete">Delete</a></li>')
		$("#file_menus").append('<li class="rename"><a href="#rename">Rename</a></li><li class="delete"><a href="#delete">Delete</a></li>')
	}
}
function show_menu_share_sel(perm) {
  $("#file_menus").html('<li class="queue"><a href="#queue_all">Queue</a></li>');
  $("#dir_menus").html('<li class="queue"><a href="#queue_all">Queue</a></li>');
  if (perm == 4) {
    $("#file_menus").append('<li class="delete"><a href="#delete_all">Delete</a></li>');
    $("#dir_menus").append('<li class="delete"><a href="#delete_all">Delete</a></li>');
  }    
}

/* end menu behavior */

function close_ajax() {
  setTimeout('$("#p_info").slideUp("slow");',2500);
}

function hide_contents() {
	$("#folder_contents h2").text('Storage area');
	$("#folder_contents .toolbar").hide();
	$("#folder_contents .ajax").html('');
	$("#folder_settings .ajax").html('');
}

function logout() {
	$.post('inc/login.php?do=logout',function(reply){
		$("#p_info").text(reply).slideDown("slow",close_ajax);
		setTimeout('location.href="index.php"',2000);  
	});
}

function show_add_folder() {
	$('#folder_settings .ajax').load('inc/storage.php?do=show_folder_set&new=1',function(){
		init_panels();
	});
}

function save_folder_set() {
	if ($("#d_folder_set input[name='name']").val() == '') return false;
	var data = {};
	$("#d_folder_set :input").each(function(){
		data[$(this).attr('name')]=$(this).val();
	});
	data['limit']=$("#d_folder_set :checked").val();
	$.post('inc/storage.php?do=save_folder_set',data,function(reply){
		$("#own_folders .ajax").load('inc/storage.php?do=show_folders',function(){init_folders();});
		$("#p_info").text(reply).slideDown("slow",close_ajax);
		$("#folder_settings .ajax").html('');
	});
}

function open_folder() {
	cur = $(this).attr('gid'); 
	share_mode = 0;
	
	$("#folder_contents .toolbar").show();
	$("#folder_settings .ajax").html('');
	$("#folder_contents h2").text('Contents of '+$(this).text());
	$("#own_folders .folder_list div img").removeClass("active");
  $("#shared_folders .folder_list div img").removeClass("active");
	$(this).children("img").addClass("active");

	$("#folder_contents .ajax").load('inc/storage.php?do=show_folder_content&cur='+cur,function(){
		init_content();
	});
}

function show_add_dir() {
	$('#folder_settings .ajax').load('inc/storage.php?do=show_dir_set&new=1&cur='+cur,function(){
		init_panels();
	});
}

function save_dir_set() {
	var data = {};
	$("#d_dir_set :input").each(function(){
		data[$(this).attr('name')]=$(this).val();
	});
	$.post('inc/storage.php?do=save_dir_set&cur='+cur,data,function(reply){
		$("#p_info").text(reply).slideDown("slow",close_ajax);
		$("#folder_contents .ajax").load('inc/storage.php?do=show_folder_content&cur='+cur,init_content);
		$("#folder_settings .ajax").html('');
	});
}

function show_file_upl() {
	$("#folder_settings .ajax").load('inc/storage.php?do=show_fileup&cur='+cur,function(){
		var quota = $("#folder_settings .stats #gl").val();
		var fsize = $("#folder_settings .stats #fl").val();
		var dirlim = $("#folder_settings .stats #dl").val();
    var clr = false;
		$('#fileMulti').fileUpload({
			'uploader': 'plugin/uploadify/uploader.swf','script': 'inc/upload.php','cancelImg':'img/cancel.png','buttonImg':'img/but_up.png','width':'120','height':'36','multi':true,'auto':true, 'simUploadLimit': 1, 
      'onSelect': function(event, queue, file) {
				if (file.size > fsize) {
         clr = true;
         return false; 
        }
      },
      'onSelectOnce': function(event, data) {
        if (clr) {
         $('#fileMulti').fileUploadClearQueue();   
         $("#p_info").text("file limit exceeded").slideDown("slow",close_ajax);
         clr = false;
        } else
      	if (data.allBytesTotal > quota) {
      		$('#fileMulti').fileUploadClearQueue();
      		$("#p_info").text("account limit exceeded").slideDown("slow",close_ajax);
					return false;
      	} else if (dirlim != 0 && data.allBytesTotal > dirlim) {
					$('#fileMulti').fileUploadClearQueue();
      		$("#p_info").text("folder limit exceeded").slideDown("slow",close_ajax);
					return false;
      	} else if ((data.fileCount > 0) && (data.allBytesTotal > 0))
					$("#fileMulti").fileUploadSettings('scriptData','&cur='+cur+'&user='+curuser);
      },
			'onAllComplete': function(event, queueID, fileObj, response, data){
				$("#folder_contents .ajax").load('inc/storage.php?do=show_folder_content&cur='+cur,function(){
					init_content(); 
					show_file_upl();
				});
	      $("#p_info").text(response).slideDown("slow",close_ajax);
    	}
		});	
	});
}

function save_file_set() {
	var name = $("#d_file_set input[name='name']").val();
	var id = $("#d_file_set input[name='id']").val();
	$.post('inc/storage.php?do=save_file_set&id='+id,{name:name},function(reply) {
		$("#folder_contents .ajax").load('inc/storage.php?do=show_folder_content&cur='+cur,init_content);
		$("#p_info").text(reply).slideDown("slow",close_ajax);
		$("#folder_settings .ajax").html('');
	});
}

function select_element(e) {
	var id = $(this).attr("gid");
	if (e.shiftKey) { // shift pressed
		if ($(this).hasClass('selected')) {
			return;
		}
		if ($(this).hasClass('dir'))
			dirs.push(id);
		else
			files.push(id);
		$(this).addClass('selected');
		show_menu_sel();
    show_items_info();
	} else { // shift not pressed
		$(".element").removeClass('selected');
 		files.length = 0; dirs.length = 0;
		show_menu_def();
    if ($(this).hasClass('dir')) {
      cur = id;
      $("#folder_contents .ajax").load('inc/storage.php?do=show_folder_content&cur='+cur,function(){init_content();$("#folder_contents h2").text('Contents of '+$("#curdirname").val());});
      show_dir_info(id);
    }
    if ($(this).hasClass('file'))
       show_item_info(id);
	}
}

function show_item_info(item) {
	$("#folder_information .ajax").load('inc/storage.php?do=show_info&many=0&id='+item);
}

function show_items_info() {
	var data = {};
	$.each(files,function(k, v){data['file'+k]=v;});
	$.each(dirs,function(k, v){data['dir'+k]=v;});
	$("#folder_information .ajax").load('inc/storage.php?do=show_info&many=1',data);
}

function show_dir_info(item) {
  $("#folder_information .ajax").load('inc/storage.php?do=show_dir_info&id='+item);
}

/* queue functions */
function queue_del() {
	var el = $(this).parent();
	el.fadeOut('slow',function(){
    el.remove();
    if ($("#queue_list .ajax div").length == 0) $("#queue_list #buts").hide();
  });
}

function queue_down() {
	var i = 0;
	var data = {};
	$("#queue_list .qfile").each(function(){
		data["file"+i] = $(this).attr("gid");
		i++;
	});
	var k = 0;
	$("#queue_list .qdir").each(function(){
		data["dir"+k] = $(this).attr("gid");
		k++;
	});
	data['files'] = i;
	data['dirs'] = k;
	$.post('inc/storage.php?do=zip_down',data,function(reply){
		$("#dwn_frm").attr('src','inc/storage.php?do=down_zip&id='+reply);
	});
}

function queue_clear() {
	$("#queue_list .ajax").fadeOut('slow',function(){
		$("#queue_list .ajax").html('').show();
		$("#queue_list #buts").hide();
	});
}

function queue_add() {
	$.each(dirs,function(k, v) {
	var name = $("#folder_contents div[gid='"+v+"']").text();
		$("#queue_list .ajax").append('<div class="qdir" gid="'+v+'"><img src="img/del_cross.png">'+name+'</div>');
	});
	$.each(files,function(k, v){
		var name = $("#folder_contents div[gid='"+v+"']").text();
		$("#queue_list .ajax").append('<div class="qfile" gid="'+v+'"><img src="img/del_cross.png">'+name+'</div>');
	});
	$("#queue_list .ajax div img").click(queue_del);
	$("#queue_list div#buts").show();
}

function delete_all() {
	if (confirm("Are you sure want to delete all selected items?")) {
		var data = {};
		$.each(dirs,function(k, v){data['dir'+k]=v;});
		$.each(files,function(k, v){data['file'+k]=v;});
		$.post('inc/storage.php?do=delete_all',data,function(reply){
		  $("#folder_contents .ajax").load('inc/storage.php?do=show_folder_content&cur='+cur,init_content);
			$("#p_info").text(reply).slideDown("slow",close_ajax);
		});	
	}
}

function chk_flash() {
	var flag;
	if (window.ActiveXObject)
		try {new ActiveXObject("ShockwaveFlash.ShockwaveFlash");flag = true;} catch (e) {flag = false;}
	else {$.each(navigator.plugins, function (){if (this.name.match(/flash/gim)) {flag = true;return false;} else {flag = false;}});}
	return flag;
}

function wait(m) {
	var date = new Date();
	var curDate = null;
	do { curDate = new Date(); }
	while(curDate-date < m);
} 

function suggest_lookup() {
	var str = $("#txt_user").val();
	if(str.length < 3)
		$('#suggestions').hide();
	else {
		var url = 'inc/storage.php?do=suggest';
		$.post(url, {q: ""+str+""}, function(data){
			if(data.length >0) {
				$('#suggestions').show();
				$('#autoSuggestionsList').html(data);
			}
		});
	}
}

function suggest_fill(thisValue,where,id) {
	$(where).val(thisValue);
	setTimeout("$('#suggestions').hide();", 200);
}

function save_share_set() {
	if ($("#newshare :checked").length == 0) return;
	var data = {};
	var id = $("#d_share_set input[name='folder']").val();
	data['folder'] = id;
	data['user'] = $("#d_share_set input[name='user']").val();
	$("#d_share_set :checked").each(function(){
		data[$(this).attr('name')] = $(this).val();
	});
	$.post('inc/storage.php?do=save_share_set',data,function(reply){
		$("#folder_settings .ajax").load('inc/storage.php?do=show_share_set&cur='+id,function(){
			init_panels();
			$("#txt_user").keyup(function(){suggest_lookup($(this).val(),'inc/storage.php?do=suggest');});
      $("#newshare input[name='perm[4]']").click(function(){ $("#newshare input[name='perm[1]']").attr('checked','checked'); });
			$("#p_info").text(reply).slideDown("slow",close_ajax);
		});
	});
}

function save_share_old() {
	var stop = false;
	$("#shares .suser .perms").each(function(){
  	if ($(this).find(":checked").length == 0)	stop = true;
	});
	if (stop) return;
	var data = {};
	$("#d_share_set :checked").each(function(){
		data[$(this).attr('name')] = $(this).val();
	});
	$("#d_share_set .hidid").each(function(){
		data[$(this).attr('name')] = $(this).val();
	});
	$.post('inc/storage.php?do=save_share_old',data,function(reply){
		$("#p_info").text(reply).slideDown("slow",close_ajax);
	});
}

function del_share(id) {
	var el = $("#folder_settings #shares div[gid='"+id+"']");	
	$.post('inc/storage.php?do=del_share',{id:id},function(reply){
		el.fadeOut("slow",function(){
			el.remove();
			if ($("#shares .suser").length == 0)	$("#folder_settings #shares").remove();
      $("#p_info").text(reply).slideDown("slow",close_ajax);
		});
	});
}

function open_share() {
	var perm = $(this).attr("perm");
	var id = $(this).attr('gid');
	cur = id;
	share_mode = 1;
	
	$("#folder_contents .toolbar").hide();
	$("#folder_settings .ajax").html('');
	$("#folder_contents h2").text('Contents of '+$(this).text());
	$("#shared_folders .folder_list div img").removeClass("active");
  $("#own_folders .folder_list div img").removeClass("active");
	$(this).children("img").addClass("active");
	
  if (perm == 2) {
    $("#folder_contents .ajax").html('');
    $("#folder_contents .toolbar").html('<img src="img/new_folder.png" id="b_show_add_dir" title="New directory" onclick="show_add_dir()"><img src="img/upload.png" id="b_file_upload" title="Upload file(s)" onclick="show_file_upl()">');
    $("#folder_contents .toolbar").show();
  } else
	$("#folder_contents .ajax").load('inc/storage.php?do=show_folder_content&cur='+cur,function(){
	  show_menu_share(perm);
		init_content();
	});
}

function select_share(e) {
	var id = $(this).attr("gid");
	var perm = $("#shared_folders div[gid='"+cur+"']").attr("perm");	
	if (e.shiftKey) { // shift pressed
		if ($(this).hasClass('selected'))	return;
		if ($(this).hasClass('dir')) 
      dirs.push(id);
    else
      files.push(id);
		$(this).addClass('selected');
    show_menu_share_sel(perm);
		show_items_info();
	} else { // shift not pressed
		$(".element").removeClass('selected');
		dirs.length = 0; files.length = 0;
		show_menu_share(perm);
		if ($(this).hasClass('dir')) {
      cur = id;
      $("#folder_contents .ajax").load('inc/storage.php?do=show_folder_content&cur='+cur,function(){init_content();$("#folder_contents h2").text('Contents of '+$("#curdirname").val());});
      show_dir_info(id);
    } else
       show_item_info(id);
	}
}

function go_up(id) {
  cur = id;
  $("#folder_contents .ajax").load('inc/storage.php?do=show_folder_content&cur='+cur,function(){init_content();$("#folder_contents h2").text('Contents of '+$("#curdirname").val());});
}

function change_pass() {
  var oldp = $("#profile input[name='curpass']").val();
  var newp = $("#profile input[name='newpass']").val();
  $.post('inc/login.php?do=ch_pwd',{oldp:oldp, newp:newp},function(reply){
    $("#p_info").text(reply).slideDown("slow",close_ajax);
  });
}

