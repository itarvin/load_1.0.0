<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>客户登录系统测试版|550,590</title>
<link rel="stylesheet" type="text/css" href="css/login.css">
<script type="text/javascript" src="./js/myJquery.js"></script>
<script type="text/javascript" src="./js/kv_cookie.js"></script>

<style>
body{background: url(./images/bg_01.png) center center;}
input{border:1px solid #ccc;}
.searchIpt #button{width:31px;height:31px;position:absolute;overflow:hidden;top:0px;right:0px;background:url(images/search.png) no-repeat 0px 0px;padding:0px;border:0px;}
.searchIpt{margin: auto;}
.userData{margin-left: 20%;margin-top: 10px;}
.dataBox{margin-left:14px;}
.data1,.data2{height:42px; line-height: 30px;}
.data2{width: 530px;}
.data1 .fl,.data2 .fl{text-align: left;}
.data1 .fl input,.data2 .fl input{line-height: 28px;}
.data2 .fl{width:180px;margin-right: 20px;}
.data2 .fl input{width:100px;}
.data2 .fl span{width:60px;}
.data2 .fl img{width:22px; height:22px;margin-left:4px;}
.fl_sub input{background-color: #81ab2c;}
.fl_btn input{background-color: #ccc;}
.datatxt{width: 90px;}
.datatxt1{width:70px;}
</style>
</head>
<body>
<div class="dluBox">
	<ul>
		<li class="dlu">
			<div class="photo fl"><img src="images/img_698.JPG" width="50px" height:"50px" id="bg"></div>
			<div class="utext fl">
				<a href="./data.php" id="users"></a>|<a href="#" onclick="logout()">退出</a>|

				<a href="crm.php">客户</a>|<a href= "">培训</a>|<a href="">命盘</a>
			</div>
		</li>
	</ul>
	<!-- <form action="" method="post" id="app_qq">

		<div class="searchIpt">
			<input name="qq" type="text" id="qq" value="" />
			<span>
				<input type="submit" id="button" value="" onclick="displayInfo()" onMouseOver="this.style.backgroundPosition='0px -31px';" onMouseOut="this.style.backgroundPosition='0px 0px';" />
			</span>
		</div>

	</form> -->
	<div id="detail_form"></div>
<!--个人资料-->
	<div class="userData">
		<form  action="http://localhost:8081/public/index.php/api/Admins/edit" method="post" enctype="multipart/form-data">
		<div class="dataBox">

			<div class="data1">
				<div class="fl datatxt">用户名：</div>
				<div class="fl"><input type="text" name="users"></div>
			</div>
			<input type="hidden" name="id">
			<div class="data1">
				<div class="fl datatxt">密码：</div>
				<div class="fl"><input type="password"></div>
			</div>
			<div class="data1">
				<div class="fl datatxt">电话：</div>
				<div class="fl"><input type="phone" name="phone"></div>
			</div>
			<div class="data1">
				<div class="fl datatxt">简介：</div>
				<div class="fl"><input type="text" name="description"></div>
			</div>
			<div class="data1">
				<div class="fl datatxt">用户名：</div>
				<div class="fl">
					<select>
						<option value="">背景1</option>
						<option value="">背景2</option>
						<option value="">背景3</option>
						<option value="">背景4</option>
						<option value="">背景5</option>
					</select>
					<input type="file" name='bg'></div>
			</div>
			<div class="data2">
				<div class="fl"><span>主QQ：</span><input name="qq1" type="text"></div>
				<div class="fl"><span>昵称：</span><input  type="text" name="qq1name"></div>
			</div>
			<div class="data2">
				<div class="fl"><span>副QQ：</span><input name="qq2" type="text"></div>
				<div class="fl"><span>昵称：</span><input  type="text" name="qq2name"></div>
			</div>
			<div class="data2">
				<div class="fl"><span>QQ3：</span><input name="qq3" type="text"></div>
				<div class="fl"><span>昵称：</span><input  type="text" name="qq3name"></div>
			</div>
			<div class="data2">
				<div class="fl"><span>VIP：</span><input name="qq4" type="text"></div>
				<div class="fl"><span>昵称：</span><input  type="text" name="qq4name"></div>
			</div>
			<div class="data2">
				<div class="fl"><span>微信：</span><input name="weixin" type="text"></div>
				<div class="fl"><span>昵称：</span><input  type="text" name="wxname">
					<img src="./images/weixin.png">
					<input name="qrcode" type="file">
				</div>
			</div>
			<div class="data2">
				<div class="fl fl_sub"><input type="submit" value="提交"></div>
				<div class="fl fl_btn"><input  type="button" value="重置"></div>
			</div>
		</div>
		</form>

	</div>
</div>
<script>
window.onload=function(){
	var storage=window.localStorage;
	var users = storage["users"];
	var bg = storage["bg"];
	$("#bg").attr('src',bg);
	$("#users").text(users);
	var identity = $.cookie('identity');
	if(identity != null){
		var contentType ="application/x-www-form-urlencoded; charset=utf-8";
		$.ajax({
			type: 'post',
			url: 'http://localhost:8081/public/index.php/api/Admins/info',
			dataType: 'json',
			contentType:contentType,
			success: function(msg){
				if(msg.status == '1'){
					$.each(msg.data,function(index, el) {
						$('input[name='+index+']').val(el);
					});
				}else {

				}
			},
			beforeSend: function(request) {
				request.setRequestHeader("X-Requested-With","XMLHttpRequest");
			}
		});
	}else {
		window.location.href="index.php";
	}
}
// function update()
// {
// 	// var id = $("input[name='id']").val();
// 	var data = {
// 		id : $("input[name='id']").val(),
// 		users : $("input[name='users']").val(),
// 		pwd : $("input[name='pwd']").val(),
// 		qq1 : $("input[name='qq1']").val(),
// 		qq1name : $("input[name='qq1name']").val(),
// 		qq2name : $("input[name='qq2name']").val(),
// 		qq2 : $("input[name='qq2']").val(),
// 		qq3 : $("input[name='qq3']").val(),
// 		qq3name : $("input[name='qq3name']").val(),
// 		wxname : $("input[name='wxname']").val(),
// 		weixin : $("input[name='weixin']").val(),
// 		qq4 : $("input[name='qq4']").val(),
// 		qq4name : $("input[name='qq4name']").val(),
// 		description : $("input[name='description']").val(),
// 		phone : $("input[name='phone']").val(),
// 	}
// 	console.log(data);
// 	$.ajax({
// 		type: 'post',
// 		url: 'http://localhost:8081/public/index.php/api/Admins/edit',
// 		dataType: 'json',
// 		data:data,
// 		success: function(msg){
// 			if(msg.status == 1){
// 				// 成功
// 			}else {
// 				alert(msg.info);
// 			}
// 		},
// 		beforeSend: function(request) {
// 			request.setRequestHeader("X-Requested-With","XMLHttpRequest");
// 		}
// 	});
// }
</script>
</body>
</html>
