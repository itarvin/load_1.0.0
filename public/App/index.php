<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>客户登录系统测试版|320,275</title>
<link rel="stylesheet" type="text/css" href="images/login.css">
<script type="text/javascript" src="images/myJquery.js"></script>
<script type="text/javascript" src="images/kv_cookie.js"></script>
<style>
	.loginBox{display:none;}
	.loginBox h2{margin:10px auto;}
	.lg-item tr{width: 260px;height: 34px; line-height: 34px;font-size: 16px;margin:auto;}
	.lg-item tr input{height:30px;border: 1px solid #ccc;}
	.lg-item tr input:last-child{border: none;}
	.lg-item tr td{text-align:left;padding:5px 0;}
	.btn2{height:30px;padding:5px;line-height: 18px; font-size: 16px;border: 1px solid #ccc;}
	.lg-item1 input,.lg-item2 input{width:160px;line-height: 30px;}
	.lg-item3 input{width:74px;margin-right: 5px;}
	.lg-item3 img{vertical-align: middle;}
	.tisA{border:#060 solid 1px; background:#CAFFD8;color:#060;margin:10px 30px 0px 30px;padding:5px;}
	.tisB{border:#F30 solid 1px; background:#FC9;color:#900;margin:10px 30px 0px 30px;padding:5px;}
	.tisC{border:#666 solid 1px; background:#CCC;color:#333;margin:10px 30px 0px 30px;padding:5px;}
</style>
</head>
<body>
<div class="checkBox" id="checkBox">
	<form onsubmit="return false" action="##" method="post">
		<h2>免登陆快速查询</h2>
		<input class="qq" type="tel" name="value" placeholder="请输入QQ查询">
		<div id="tis"></div>
		<button type="submit" class="btn1" style="background:#006600;color:#fff;" onclick="search()">立即查询</button>
		<button type="button" class="btn1" id="btn1" >登录客户系统</button>
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
			<td><input class="" name="verify" placeholder="请输入验证码"><img src="http://localhost:8081/public/index.php/api/login/verify" onclick="refreshVerify()" id="verify_img"></td>
		</tr>
		<tr class="lg-item4">
			<td colspan="2">
			<span class="fl"><input style="border:none;" name="recowd" type="checkbox" id="recpwd"><label for="recpwd">记住密码</label></span>
			<span class="fr" style="margin-top: :6px;"><a href="index1.php"><input class="btn2" type="submit" onclick="login()" value="登录"></a></span></td>
		</tr>
	</table>
</form>
</div>
<script>
$("#btn1").click(function(){
	$("#loginBox").fadeIn();
	$("#checkBox").fadeOut();
})
// 外调查询

function search(){
	var value = $("input[name='value']").val();
	$.ajax({
		type: 'post',
		url: 'http://localhost:8081/public/index.php/api/Index/index',
		dataType: 'json',
		data: {value : value},
		success: function(msg){
			if(msg.status == 1){
				// alert(msg.info);
				$("#tis").html('当前客户已被'+msg.data.users+'登记,转交给'+msg.data.qq1);
			}else if(msg.status == -1) {
				// alert(msg.info);
				$("#tis").html('当前客户未被注册，加油！');
			}else {
				// elert(msg.info);
				$("#tis").html(msg.info);
			}
		},
		beforeSend: function(request) {
			request.setRequestHeader("X-Requested-With","XMLHttpRequest");
		}
	});
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
			if(msg.status == 1)
			{
				alert(msg.info);
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
