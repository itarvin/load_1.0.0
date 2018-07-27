<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>客户登录系统测试版|320,275</title>
<link rel="stylesheet" type="text/css" href="./css/login.css">
<script type="text/javascript" src="./js/myJquery.js"></script>
<script type="text/javascript" src="./js/kv_cookie.js"></script>
<style>
body{background: url(./images/bg_01.png) center center;}
</style>
</head>
<body>
<div class="checkBox" id="checkBox">
	<form onsubmit="return false" action="##" method="post">
		<h2>免登陆快速查询</h2>
		<input class="qq" type="text" name="value" autocomplete="off" placeholder="请输入QQ查询" onkeyup="value=value.replace(/[^\w\.\/]/ig,'')">
		<div id="tis" style="display:none;">546456</div>
		<button type="submit" class="btn1" style="background:#028e02;color:#fff;" onclick="search()">立即查询</button>
		<button type="button" class="btn1 btn_1" id="btn1" >登录客户系统</button>
	</form>
</div>
<div class="loginBox" id="loginBox">
<h2>客户系统测试版</h2>
<form onsubmit="return false" action="##" method="post">
	<table class="lg-item" cellspacing="2">
		<tr class="lg-item1">
			<td>账号：</td>
			<td><input type="text" class="" name="users" placeholder="请输入账号"></td>
		</tr>
		<tr class="lg-item2">
			<td>密码：</td>
			<td><input type="password" class=""  name="pwd" placeholder="请输入密码"></td>
		</tr>
		<tr class="lg-item3">
			<td>验证码：</td>
			<td><input class="" name="verify"><img src="http://localhost:8081/public/index.php/api/Login/verify" onclick="refreshVerify()" id="verify_img"></td>
		</tr>
		<tr class="lg-item4">
			<td colspan="2">
			<span class="fl"><input style="border:none;" name="recowd" type="checkbox" id="recpwd"><label for="recpwd">记住密码</label></span>
			<span class="fr"><a href="index1.php"><input class="btn2" type="submit" onclick="login()" value="登录"></a></span></td>
		</tr>
	</table>
</form>
</div>
<script>
window.onload=function(){
	var identity = $.cookie('identity');
	if(identity){
		window.location.href="/App/home.php";
	}
}
var placeholder=$('[name="value"]').attr('placeholder')
$('[name="value"]').val(placeholder).css({color:'#999'}).focus(function(){
		if($(this).val()==placeholder){$(this).val('').css({color:'#333'})};
	}).blur(function(){
		if($(this).val()==''){$(this).val(placeholder).css({color:'#999'})}
	})
$("#btn1").click(function(){
	$("#loginBox").fadeIn();
	$("#checkBox").fadeOut();
})
// 外调查询
function search(){
	var value = $("input[name='value']").val();
	if(value != "请输入QQ查询" && value != ''){
		if(value <= 5){
			$("#tis").show();
			$("#tis").html('提交查询数据至少为7位！');
		}else {
			$.ajax({
				type: 'post',
				url: 'http://localhost:8081/public/index.php/api/Index/index',
				dataType: 'json',
				data: {value : value},
				success: function(msg){
					if(msg.status == 1){
						$("#tis").html('当前客户已被'+msg.data.users+'登记,转交给'+msg.data.qq1);
					}else if(msg.status == -1) {
						$("#tis").show();
						$("#tis").html('当前客户未被注册，加油！');
					}else {
						$("#tis").html(msg.info);
					}
				},
				beforeSend: function(request) {
					request.setRequestHeader("X-Requested-Witzh","XMLHttpRequest");
				}
			});
		}
	}
	return false;
}
function refreshVerify() {
	var ts = Date.parse(new Date())/1000;
	$('#verify_img').attr("src", "http://localhost:8081/public/index.php/api/Login/verify?id="+ts);
}
function login()
{
	var name = $('input[name="users"]').val();
	var pwd = $('input[name="pwd"]').val();
	var verify = $('input[name="verify"]').val();
	// 判断checkbox是否选中
	if ($('input[name="recowd"]').attr('checked')) {
		var online = $('input[name="recowd"]').val();
	}else{
		var online = 0;
	}
	var data = {
		users  : name,
		pwd    : pwd,
		verify : verify,
		online : online
	};
	var contentType ="application/x-www-form-urlencoded; charset=utf-8";
	$.ajax({
		type: 'post',
		url: 'http://localhost:8081/public/index.php/api/login/login',
		dataType: 'json',
		data:data,
		contentType:contentType,
		success: function(msg){
			if(msg.status == 1){
				var storage = window.localStorage;
				//写入a字段
				storage["users"] = msg.data.users;
				storage["bg"] = msg.data.bg;
				storage["clientid"] = msg.data.clientid;
				window.location.href="/App/home.php";
			}

		},
		beforeSend: function(request) {
			request.setRequestHeader("X-Requested-With","XMLHttpRequest");
		}
	});
	return false;
}
</script>
</body>
</html>
