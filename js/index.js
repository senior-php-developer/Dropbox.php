$(init_index);

var rate;

function init_index() {
	$(".reg").click(open_reg);
	$(".soon").click(soon);
	$("#login").click(open_login);
	$("#logout").click(logout);
	$("#d_reg .b_submit").click(register);
	$("#d_login #b_login").click(login);
	$("#d_login #b_reset").click(reset_pwd);
	$("#d_login #b_change").click(change_pwd);
	
	$(".d_box input").focus(function(){	$(this).removeClass("blur").addClass("focus");});
	$(".d_box input").blur(function(){	$(this).removeClass("focus").addClass("blur");});
	$("#d_reg .b_cancel").click(function(){	$("#d_reg").fadeOut("slow");});
	$("#d_login .b_cancel").click(function(){	$("#d_login").fadeOut("slow");});
  $("#d_soon .close").click(function(){ $("#d_soon").fadeOut("slow");});
	
	$.ajaxSetup({
		beforeSend: function(){$("#p_ajax").show();},
    success: function(){$("#p_ajax").hide();},
    complete: function(){$("#p_ajax").hide();}
  });
}

function close_ajax() {
  setTimeout('$("#p_info").slideUp("slow");',2500);
}

function hide_popup() {
  $("#d_soon").hide();
  $("#d_login").hide();
  $("#d_reg").hide();
}

function open_reg() {
  hide_popup();
	rate = $(this).attr("rate");
	var w = $(window).width()*0.5-100;
	var h = $(window).height()*0.5-100;
	$("#d_reg input").val('');
	$("#d_reg").css('top',h+'px').css('left',w+'px').fadeIn("slow",Recaptcha.reload);
}

function soon() {
  hide_popup();
	var w = $(window).width()*0.5-100;
	var h = $(window).height()*0.5-100;
	$("#d_soon").css('top',h+'px').css('left',w+'px').fadeIn("slow");
}

function open_login() {
  hide_popup();
	var w = $(window).width()*0.5-100;
	var h = $(window).height()*0.5-100;
	$("#d_login input").val('');
	$("#d_login span#password").show(); 
	$("#d_login span#code").hide();
	$("#b_change").hide();
	$("#b_reset").hide();
	$("#b_login").show();
	$("#d_login").css('top',h+'px').css('left',w+'px').fadeIn("slow");
}

function register() {
	if (($("#d_reg input[name='mail']").val() == '') || ($("#d_reg input[name='pass']").val() == '')) {
		$("#p_info").text("fill all neccessary fields").slideDown("slow",close_ajax);
		return;
	}
	var data = {};
	$("#d_reg :input").each(function(){
		data[$(this).attr('name')]=$(this).val();
	});
	data['rate']=rate;
	$.post('inc/login.php?do=register',data,function(reply){
		$("#p_info").text(reply).slideDown("slow",close_ajax);
		$("#d_reg :input").val('');
		$("#d_reg").fadeOut('slow');
	});
}

function login() {
	if (($("#d_login input[name='mail']").val() == '') || ($("#d_login input[name='pass']").val() == '')) {
		$("#p_info").text("login information is missing").slideDown("slow",close_ajax);
		return;
	}
	var data = {};
	$("#d_login :input").each(function(){
		data[$(this).attr('name')]=$(this).val();
	});
	$.post('inc/login.php?do=login',data,function(reply){
		$("#p_info").text(reply).slideDown("slow",close_ajax);
		if (reply != 'password incorrect')
			setTimeout('location.href="storage.php"',2000);  
	});
}

function logout() {
	$.post('inc/login.php?do=logout',function(reply){
		$("#p_info").text(reply).slideDown("slow",close_ajax);
		setTimeout('location.href="index.php"',2000);  
	});
}

function show_reset_dlg() {
	$("#b_login").hide();
	$("#b_reset").show();
	$("#d_login span#password").hide();
}

function reset_pwd() {
	var mail = $("#d_login input[name='mail']").val();
	if (mail == '') return;
	$.post('inc/login.php?do=reset',{mail: mail},function(reply){
		$("#p_info").text(reply).slideDown("slow",close_ajax);
		show_change_pwd();
	});
}

function show_change_pwd() {
	$("#b_reset").hide();	
	$("#b_change").show();  
	$("#d_login span#password").show(); 
	$("#d_login span#code").show();
}

function change_pwd() {
	var data = {};
	$("#d_login :input").each(function(){
		data[$(this).attr('name')]=$(this).val();
	});
	$.post('inc/login.php?do=change',data,function(reply){
		$("#p_info").text(reply).slideDown("slow",close_ajax);
	});	
}