<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>客户登录系统测试版|880,500</title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta name="renderer" content="webkit">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<link rel="stylesheet" href="./layui/css/layui.css">
<style>
.label_text {width: 35px;}
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

	<table class="layui-table">
		<thead>
			<tr>
				<!-- 改了，也没影响 -->
				<th>客户名称(勿修改)</th>
				<th>销售(勿修改)</th>
				<th>产品名称</th>
				<th>价格</th>
				<th>备注</th>
				<th>操作</th>
		</thead>
		<form onsubmit="return false" action="##" method="post" >
			<tbody>
				<tr>
					<td><input type="text" id="username" class="layui-input"　disabled="disabled" style="background:#CCCCCC"></td>

					<td><input type="text" id="users" class="layui-input" 　disabled="disabled" style="background:#CCCCCC"></td>

					<input type="hidden" id="khid" name="khid" >

					<td><input type="text" name="product[]" required="" autocomplete="off" class="layui-input"></td>

					<td><input type="text" name="price[]" required=""  autocomplete="off" class="layui-input" onkeyup="value=value.replace(/[^\d\.\/]/ig,'')"/></td>

					<td><input type="text" name="note[]" required=""  autocomplete="off" class="layui-input" list="in"></td>
					<datalist id="in">
						<option>本人</option>
						<option>父亲</option>
						<option>母亲</option>
						<option>子女</option>
						<option>爱人</option>
						<option>亲人</option>
						<option>朋友</option>
						<option>上司</option>
					</datalist>
					<td><input onclick="addNewTr(this);" class="layui-btn layui-btn-normal" type="button" value="+" /></td>
				</tr>
				<tr id="submit" class="text-c">
					<td align="center"  colspan="6"><button class="layui-btn layui-btn-fluid" lay-filter="add">提交</button></td>
				</tr>
	  		</tbody>
	  	</from>
  </table>


<script src="./layui/layui.js" charset="utf-8"></script>
<script src="./layui/org/jquery.min.js" charset="utf-8"></script>
<script src="./layui/org/public.js" charset="utf-8"></script>
<script>

layui.use(['form','element','table','laydate'], function(){
	var table = layui.table
	,form = layui.form
	,element = layui.elemen
	,laydate = layui.laydate;

	//监听提交
	form.on('submit(add)', function(data){
		console.log(data);
		// $.ajax({
		// 	type: 'post',
		// 	url: 'http://localhost:8081/public/index.php/api/Index/index',
		// 	dataType: 'json',
		// 	data: data.field,
		// 	success: function(msg){
		//
		// 		if(msg.status == 1){
		//
		// 		}else {
		//
		// 		}
		// 	},
		// });
		return false;
	});

});
function addNewTr(btn)
{
	var tr = $(btn).parent().parent();
	var val = $(btn).val();
	if($(btn).val() == "+")
	{
		var newTr = tr.clone();
		newTr.find(":button").val("-");
		$("#submit").before(newTr);
	}else{
		tr.remove();
	}
}
window.onload=function(){
	var url = location.search; //获取url中"?"符后的字串
	var theRequest = new Object();
	if (url.indexOf("?") != -1) {
		var str = url.substr(1);
		strs = str.split("&");
		for(var i = 0; i < strs.length; i ++) {
			theRequest[strs[i].split("=")[0]]=unescape(strs[i].split("=")[1]);
		}
	}
	$.ajax({
		type: 'post',
		url: 'http://localhost:8081/public/index.php/api/Members/getMemeber',
		dataType: 'json',
		data:theRequest,
		success: function(msg){
			if(msg.status == '1'){
				$('#username').val(msg.data.username);
				$('#khid').val(msg.data.id);
				var storage = window.localStorage;
				$('#users').val(storage['users']);
			}
		},
	});
}
</script>
</body>
</html>
