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
.label_text {width: 35px;}
.layui-input-inline {width: 80px;float: left;}
.keyword {width: 200px;float: left;}
.layui-form {padding-top: 20px;}
.layui-elem-quote {width: 300px;}
.layui-form-item label {padding-left: 1px;}
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
<form class="layui-form layui-col-md12 x-so search" onsubmit="return false" action="##" method="post">
  <div class="layui-input-inline">
      <select name="type" lay-filter="type">
          <option value="0">模糊</option>
          <option value="qq" >QQ</option>
          <option value="phone">电话</option>
          <option value="weixin">微信</option>
      </select>
  </div>
  <input type="text" name="value"  placeholder="关键字" autocomplete="off" class="layui-input keyword" id="value">
  <button class="layui-btn"  lay-submit="" lay-filter="search" >
      <i class="layui-icon" id="logo">&#xe615;</i>
  </button>
</form>
<blockquote class="layui-elem-quote" style="margin-top: 10px;display:none;">
  <div><span id="tips"></span><a id="link"></a></div>
</blockquote>

<form class="layui-form" id="dataInfo" style="display:none;" onsubmit="return false" action="##" method="post">
	<div class="layui-form-item">
		<label class="layui-form-label">名称</label>
		<div class="layui-input-block">
			<input type="text" name="username" placeholder="请输入标题" autocomplete="off" class="layui-input">
		</div>
	</div>
	<input type="hidden" name="id" id="checkId" value="">
	<div class="layui-form-item">
		<label class="layui-form-label">性别</label>
		<div class="layui-input-block">
			<input type="radio" name="sex" value="男" title="男">
			<input type="radio" name="sex" value="女" title="女" checked>
		</div>
	</div>
	<div class="layui-form-item">
		<label class="layui-form-label">电话</label>
		<div class="layui-input-block">
			 <input type="tel" name="phone" lay-verify="phone" autocomplete="off" class="layui-input" placeholder="请输入电话">
		</div>
	</div>
	<div class="layui-form-item">
		<label class="layui-form-label">QQ</label>
		<div class="layui-input-block">
			<input type="text" name="qq" placeholder="请输入QQ" autocomplete="off" class="layui-input">
		</div>
	</div>
	<div class="layui-form-item">
		<label class="layui-form-label">微信</label>
		<div class="layui-input-block">
			<input type="text" name="weixin" placeholder="请输入微信" autocomplete="off" class="layui-input" >
		</div>
	</div>
	<!-- <div class="layui-form-item">
		<label class="layui-form-label">名称</label>
		<div class="layui-input-block">
			<input type="text" name="title" required  lay-verify="required" placeholder="请输入标题" autocomplete="off" class="layui-input">
		</div>
	</div> -->
	<div class="layui-form-item layui-form-text">
		<label class="layui-form-label">备注</label>
		<div class="layui-input-block">
			<textarea name="note" id="note" placeholder="请输入备注内容" class="layui-textarea"></textarea>
		</div>
	</div>
	<div class="layui-form-item">
		<div class="layui-input-block">
			<button class="layui-btn" lay-submit lay-filter="store">立即提交</button>
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
	form.on('submit(search)', function(data){

		$('.layui-elem-quote').show();
		$('#dataInfo').show();

		$.ajax({
			type: 'post',
			url: 'http://localhost:8081/public/index.php/api/Members/search',
			dataType: 'json',
			data:data.field,
			success: function(msg){

				if(msg.status == 1){

					var storage=window.localStorage;

					var clientid = storage["clientid"];

					if(msg.data.uid == clientid){

						$("#tips").text('当前客户于'+msg.data.newtime+'被你登记!');

						$.each(msg.data,function(index, el) {

							if(index == 'note'){
								$('#note').val(el);
							}else if(index == 'sex'){
								form.val("dataInfo", {
									"sex": el,
								})
							}else {
								$('input[name='+index+']').val(el);
							}
						});

					}else {

						$("#tips").text('当前客户'+msg.data.newtime+'已被'+msg.data.users+'登记!请联系');

						$("#link").attr('href',"http://wpa.qq.com/msgrd?v=3&uin="+msg.data.qq1+"&site=qq&menu=yes");

						$('#link').text(msg.data.qq1);
					}

				}else if(msg.status == -1) {
					$("#tips").text('这是一个新客户，请好好把握!');
					loadNowData();
				}else if(msg.status == -10) {

					$("#tips").text(msg.info);
				}
			},
			beforeSend: function(request) {
				request.setRequestHeader("X-Requested-With","XMLHttpRequest");
			}
		});

		return false;
	});
	function loadNowData()
	{
		// 默认赋值
		form.on('select(type)', function(data){
			var option = $('input[name="'+data.value+'"]');
			console.log(option);
			option.val($('#value').val());
		});
	}
	//监听提交
	form.on('submit(store)', function(data){
		$('.layui-elem-quote').show();
		$('#dataInfo').show();
		var id = $('#checkId').val();
		if(id !== null){
			$.ajax({
				type: 'post',
				url: 'http://localhost:8081/public/index.php/api/Members/edit',
				dataType: 'json',
				data:data.field,
				success: function(msg){
					if(msg.status == 1){
						$("#tips").text('更新成功了！');
					}else{
						$("#tips").text(msg.info);
					}
				},
				beforeSend: function(request) {
					request.setRequestHeader("X-Requested-With","XMLHttpRequest");
				}
			});
			return false;
		}else {
			$.ajax({
				type: 'post',
				url: 'http://localhost:8081/public/index.php/api/Members/add',
				dataType: 'json',
				data:data.field,
				success: function(msg){
					if(msg.status == 1){
						$("#tips").text('好棒哦！再接再厉！');
					}else{
						$("#tips").text(msg.info);
					}
				},
				beforeSend: function(request) {
					request.setRequestHeader("X-Requested-With","XMLHttpRequest");
				}
			});
			return false;
		}
	});
});
</script>
</html>
