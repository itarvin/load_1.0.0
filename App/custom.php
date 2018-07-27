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
<div class="layui-form">
	<div class="layui-form-item">
		<div class="layui-inline">
			<label class="layui-form-label">开始时间</label>
			<div class="layui-input-inline">
				<input type="text" class="layui-input" id="start_time" placeholder="yyyy-MM-dd">
			</div>
		</div>
		<div class="layui-inline">
			<label class="layui-form-label">结束时间</label>
			<div class="layui-input-inline">
				<input type="text" class="layui-input" id="end_time" placeholder="yyyy-MM-dd">
			</div>
		</div>
	</div>
</div>
<table class="layui-table" lay-data="{width: 880, height:332, url:'http://localhost:8081/public/index.php/api/Members/index', page:true, id:'idTest'}" lay-filter="demo">
  <thead>
    <tr>
      <th lay-data="{field:'id', width:80, sort: true, fixed: true}">ID</th>
      <th lay-data="{field:'username', width:100}">用户名</th>
      <th lay-data="{field:'sex', width:80, sort: true}">性别</th>
      <th lay-data="{field:'qq', width:120}">QQ</th>
	  <th lay-data="{field:'phone', width:120}">电话</th>
      <th lay-data="{field:'weixin', width:120}">微信</th>
      <th lay-data="{field:'newtime', width:165, sort: true}">添加时间</th>
      <th lay-data="{field:'score', width:80, sort: true, fixed: 'right'}">消费</th>
      <th lay-data="{fixed: 'right', width:170, align:'center', toolbar: '#barDemo'}"></th>
    </tr>
  </thead>
</table>

<script type="text/html" id="barDemo">
  <a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="detail">去消费</a>
  <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
  <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
</script>


<script src="./layui/layui.js" charset="utf-8"></script>
<script src="./layui/org/jquery.min.js" charset="utf-8"></script>
<script src="./layui/org/public.js" charset="utf-8"></script>
<script>
layui.use(['form','element','table','laydate'], function(){
	var table = layui.table
	,form = layui.form
	,element = layui.elemen
	,laydate = layui.laydate;

	laydate.render({
		elem: '#start_time'
	});
	laydate.render({
		elem: '#end_time'
	});

	//监听工具条
	table.on('tool(demo)', function(obj){
		var data = obj.data;
	    if(obj.event === 'detail'){

			window.location.href="./record.php?khid="+data.id;

		} else if(obj.event === 'del'){

			layer.confirm('真的删除行么', function(index){
				$.ajax({
					type: 'post',
					url: 'http://localhost:8081/public/index.php/api/Members/delete',
					dataType: 'json',
					data:{kid : data.id},
					success: function(msg){
						if(msg.status == 1){
							obj.del();
							layer.close(index);
						}else{
							layer.close(index);
						}
					},
				});
				return false;
			});
		} else if(obj.event === 'edit'){
			// layer.alert('编辑行：<br>'+ JSON.stringify(data))

		}
	});

	$('.demoTable .layui-btn').on('click', function(){
		var type = $(this).data('type');
		active[type] ? active[type].call(this) : '';
	});
});
</script>
</body>
</html>
