<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>客户登录系统测试版|350,350</title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta name="renderer" content="webkit">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<link rel="stylesheet" href="./layui/css/layui.css">
</head>
<style>
.layui-elem-quote {width: 300px;}
/* .layui-form-item div {padding-left: 30px;} */
</style>
<body>
<div id="search">
<fieldset class="layui-elem-field layui-field-title" style="margin-top: 10px;">
	<legend>免登录快速查询</legend>
</fieldset>
<form class="layui-form" onsubmit="return false" action="##" method="post">
	<div class="layui-form-item">
		<label class="layui-form-label">查询</label>
		<div class="layui-input-inline">
			<input type="text" name="value" lay-verify="required" placeholder="请输入" autocomplete="off" onkeyup="value=value.replace(/[^\w\.\/]/ig,'')" class="layui-input">
		</div>
	</div>
	<blockquote class="layui-elem-quote" style="margin-top: 10px;display:none;">
	  <div><span id="tips"></span><a id="link"></a></div>
	</blockquote>
	<div class="layui-form-item">
		<div class="layui-input-block">
			<button class="layui-btn" lay-submit="" lay-filter="search">立即查询</button>
			<button class="layui-btn layui-btn-normal" onclick="toLogin()">立即登录</button>
		</div>
	</div>
</form>
</div>
<div style="display:none;" id="login">
<fieldset class="layui-elem-field layui-field-title" style="margin-top: 10px;">
	<legend>香港七星堂客户系统</legend>
</fieldset>
<form class="layui-form" action="">
	<blockquote class="layui-elem-quote" id="loginInfo" style="margin-top: 10px;display:none;">
	  <div><span id="tips"></span></div>
	</blockquote>
	<div class="layui-form-item">
		<label class="layui-form-label">账户</label>
		<div class="layui-input-inline">
			<input type="text" name="users" lay-verify="required" placeholder="请输入" autocomplete="off" class="layui-input">
		</div>
	</div>
	<div class="layui-form-item">
		<label class="layui-form-label">密码</label>
		<div class="layui-input-inline">
			<input type="password" name="pwd" placeholder="请输入密码" autocomplete="off" class="layui-input">
		</div>
	</div>
	<div class="layui-form-item">
		<label class="layui-form-label">验证码</label>
		<div class="layui-input-inline">

			<input name="verify" lay-verify="required" placeholder="验证码"  type="text" class="layui-input" style='width:90px; float:left;'>

			<img src="/public/index.php/api/login/verify" alt="captcha" onclick="refreshVerify()" id="verify_img" style='float:right;'>
		</div>
	</div>
	<div class="layui-form-item">
		<input type="checkbox" name="online" value="1" title="记住密码">
	</div>
	<div class="layui-form-item">
		<div class="layui-input-block">
			<button class="layui-btn" lay-submit="" lay-filter="login">立即提交</button>
			<button type="reset" class="layui-btn layui-btn-primary">重置</button>
		</div>
	</div>
</form>
</div>
</body>
<script src="./layui/layui.js" charset="utf-8"></script>
<script src="./layui/org/jquery.min.js" charset="utf-8"></script>
<script src="./layui/org/cookie.js" charset="utf-8"></script>
<script>
layui.use(['form'], function(){
	var form = layui.form
	,element = layui.elemen;

	//监听提交
	form.on('submit(search)', function(data){
		$('.layui-elem-quote').show();
		$.ajax({
			type: 'post',
			url: 'http://localhost:8081/public/index.php/api/Index/index',
			dataType: 'json',
			data: data.field,
			success: function(msg){

				if(msg.status == 1){

					$("#tips").text('当前客户'+msg.data.newtime+'已被'+msg.data.users+'登记!请联系');

					$("#link").attr('href',"http://wpa.qq.com/msgrd?v=3&uin="+msg.data.qq1+"&site=qq&menu=yes");

					$('#link').text(msg.data.qq1);

				}else if(msg.status == -1) {

					$("#tips").text('当前客户未被注册，加油！');
				}else {

					$("#tips").text(msg.info);
				}
			},
			beforeSend: function(request) {
				request.setRequestHeader("X-Requested-Witzh","XMLHttpRequest");
			}
		});
		return false;
	});

	form.on('submit(login)', function(data){
		$('#loginInfo').show();
		$.ajax({
			type: 'post',
			url: 'http://localhost:8081/public/index.php/api/login/login',
			dataType: 'json',
			data:data.field,
			success: function(msg){
				if(msg.status == 1){
					$("#tips").text(msg.info);
					var storage = window.localStorage;
					//写入a字段
					storage["users"] = msg.data.users;
					storage["bg"] = msg.data.bg;
					storage["clientid"] = msg.data.clientid;
					window.location.href="/App/home.php";
				}else {
					$("#tips").text(msg.info);
				}
			},
		});
		return false;
	});
});
// 跳转登录
function toLogin()
{
	$('#search').hide();
	$('#login').show();
}
function refreshVerify() {
	var ts = Date.parse(new Date())/1000;
	$('#verify_img').attr("src", "/public/index.php/api/login/verify?id="+ts);
}
window.onload=function(){
	var identity = $.cookie('identity');
	if(identity){
		window.location.href="/App/home.php";
	}
}
</script>
</html>
