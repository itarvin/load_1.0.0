<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>客户登录系统测试版|380,600</title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta name="renderer" content="webkit">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<link rel="stylesheet" href="./layui/css/layui.css">
<style>
	.layui-form {padding-top: 20px;}
	.layui-elem-quote {width: 300px;}
</style>
</head>
<body>
<ul class="layui-nav">
	<li class="layui-nav-item" lay-unselect="">
		<a href="javascript:;"><img src="http://t.cn/RCzsdCq" class="layui-nav-img" id="myInfo">我</a>
		<dl class="layui-nav-child">
			<dd><a href="./my.php">修改信息</a></dd>
			<dd><a href="javascript:;" onclick="logOut()">退了</a></dd>
		</dl>
	</li>
	<li class="layui-nav-item">
		<a href="./home.php">Home</a>
	</li>
	<li class="layui-nav-item">
		<a href="./custom.php">客户<span class="layui-badge">9</span></a>
	</li>
	<li class="layui-nav-item">
		<a href="">命盘<span class="layui-badge-dot"></span></a>
	</li>
</ul>
<blockquote class="layui-elem-quote" style="margin-top: 10px;display:none;">
  <div><span id="tips"></span></div>
</blockquote>
<form class="layui-form" onsubmit="return false" action="##" method="post">
	<div class="layui-form-item">
		<label class="layui-form-label label_text">姓名</label>
		<div class="layui-input-inline">
			<input type="text" name="users" lay-verify="title" autocomplete="off" placeholder="请输入姓名" class="layui-input">
		</div>
	</div>
	<input type="hidden" name="id">
	<div class="layui-form-item">
		<label class="layui-form-label label_text">手机</label>
		<div class="layui-input-inline">
			<input type="tel" name="phone" lay-verify="required|phone" autocomplete="off" class="layui-input">
		</div>
	</div>

	<div class="layui-form-item">
		<div class="layui-inline">
			<label class="layui-form-label label_text">QQ1</label>
			<div class="layui-input-inline">
				<input type="tel" name="qq1"  autocomplete="off" class="layui-input">
			</div>
		</div>
		<div class="layui-inline">
			<label class="layui-form-label label_text">昵称</label>
			<div class="layui-input-inline">
				<input type="text" name="qq1name"  autocomplete="off" class="layui-input">
			</div>
		</div>
	</div>
	<div class="layui-form-item">
		<div class="layui-inline">
			<label class="layui-form-label label_text">QQ2</label>
			<div class="layui-input-inline">
				<input type="tel" name="qq2"  autocomplete="off" class="layui-input">
			</div>
		</div>
		<div class="layui-inline">
			<label class="layui-form-label label_text">昵称</label>
			<div class="layui-input-inline">
				<input type="text" name="qq2name" autocomplete="off" class="layui-input">
			</div>
		</div>
	</div>
	<div class="layui-form-item">
		<div class="layui-inline">
			<label class="layui-form-label label_text">QQ3</label>
			<div class="layui-input-inline">
				<input type="tel" name="qq3" autocomplete="off" class="layui-input">
			</div>
		</div>
		<div class="layui-inline">
			<label class="layui-form-label label_text">昵称</label>
			<div class="layui-input-inline">
				<input type="text" name="qq3name" autocomplete="off" class="layui-input">
			</div>
		</div>
	</div>
	<div class="layui-form-item">
		<div class="layui-inline">
			<label class="layui-form-label label_text">微信</label>
			<div class="layui-input-inline">
				<input type="tel" name="weixin" autocomplete="off" class="layui-input">
			</div>
		</div>
		<div class="layui-inline">
			<label class="layui-form-label label_text">昵称</label>
			<div class="layui-input-inline">
				<input type="text" name="wxname" autocomplete="off" class="layui-input">
			</div>
		</div>
	</div>
	<div class="layui-form-item layui-form-text">
		<label class="layui-form-label label_text">备注</label>
		<div class="layui-input-block">
			<textarea placeholder="请输入内容" name="description" class="layui-textarea" id="desc"></textarea>
		</div>
	</div>
	<div class="layui-form-item">
		<div class="layui-input-block">
			<button class="layui-btn" lay-submit="" lay-filter="update">立即更新</button>
			<button type="reset" class="layui-btn layui-btn-primary">重置</button>
		</div>
	</div>
</form>
</body>
<script src="./layui/layui.js" charset="utf-8"></script>
<script src="./layui/org/jquery.min.js" charset="utf-8"></script>
<script src="./layui/org/public.js" charset="utf-8"></script>
<script>
layui.use(['form','element'], function(){
	var form = layui.form
	,element = layui.elemen;

	//监听提交
	form.on('submit(update)', function(data){
		$('.layui-elem-quote').show();
		$.ajax({
			type: 'post',
			url: 'http://localhost:8081/public/index.php/api/Admins/edit',
			dataType: 'json',
			data:data.field,
			success: function(msg){
				if(msg.status == '1'){
					$("#tips").text(msg.info);
				}else {
					$("#tips").text(msg.info);
				}
			},
		});
		return false;
	});

});
window.onload=function(){
	$.ajax({
		type: 'post',
		url: 'http://localhost:8081/public/index.php/api/Admins/info',
		dataType: 'json',
		success: function(msg){
			if(msg.status == '1'){
				$.each(msg.data,function(index, el) {
					if(index=="description"){
						$('#desc').html(el);
					}else {
						$('input[name='+index+']').val(el);
					}
				});
			}
		},
	});
}
</script>
</html>
