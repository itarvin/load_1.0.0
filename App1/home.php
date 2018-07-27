<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title id="winScreen">客户登录系统测试版|320,500</title>
<link rel="stylesheet" type="text/css" href="css/login.css">
<script type="text/javascript" src="./js/myJquery.js"></script>
<script type="text/javascript" src="./js/kv_cookie.js"></script>

<style>
body{background: url(./images/bg_01.png) center center;}
.searchIpt #button{width:31px;height:31px;position:absolute;overflow:hidden;top:0px;right:0px;background:url(images/search.png) no-repeat 0px 0px;padding:0px;border:0px;}
.crm_input {width: 210px;}
.crm_select {width: 60px;height: 28px;}
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
		<form id="app_qq" onsubmit="return false" action="##" method="post">
			<div class="searchIpt">
				<table border="1">
					<tr>
						<th>
							<select class="crm_select" name="type" id="type">
								<option value="qq">QQ</option>
								<option value="weixin">微信</option>
								<option value="phone">电话</option>
							</select>
						</th>
						<th>
							<input name="value" type="text" class="crm_input" onkeyup="value=value.replace(/[^\w\.\/]/ig,'')"/>
							<span>
								<input id="button" onclick="displayInfo()" onMouseOver="this.style.backgroundPosition='0px -31px';" onMouseOut="this.style.backgroundPosition='0px 0px';" />
							</span>
						</th>
					</tr>
				</table>
			</div>
		</form>
		<div>
			<div class="hint" ><p id="tips"></p><a id="link"></a></div>
			<form onsubmit="return false" action="##" method="post" id="addform">
			<table >
				<tbody>
					<tr>
						<td>名称：</td>
						<td><input type="text" name="username"></td>
						<td><input type="hidden" name="id"></td>
					</tr>
					<tr>
						<td>性别：</td>
						<td>
							<label><input type="radio" name="sex" checked="checked" value="男">男</label>
							<label><input type="radio" name="sex" value="女">女</label>
						</td>
					</tr>
					<tr>
						<td>QQ：</td>
						<td><input type="text" name="qq" onkeyup="value=value.replace(/[^\w\.\/]/ig,'')" onblur="upperQq()"></td>
					</tr>
					<tr>
						<td>微信：</td>
						<td><input type="text" name="weixin" onblur="upperCase()"></td>
					</tr>
					<tr>
						<td>电话：</td>
						<td><input type="text" name="phone" onblur="upperCase()" onkeyup="value=value.replace(/[^\w\.\/]/ig,'')"></td>
					</tr>
					<tr>
						<td>年龄：</td>
						<td><input type="text" name="birthday"></td>
					</tr>
					<tr>
						<td>地址：</td>
						<td><input type="text" name="address"></td>
					</tr>
					<tr>
						<td>备注：</td>
						<td><textarea name="note" row="6" id="note" style="width:170px;height:84px;margin:10px 0px 0px;"></textarea></td>
					</tr>
					<tr class="xin2">
						<td class="back"><input  type="button" value="返回"></td>
						<td class="add"><input type="button" onclick="addData()" value="添加"></td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
</div>
<script>
// 监听ajax提交
$(document).keyup(function(event){
	if(event.keyCode ==13){
		$("#button").trigger("click");
	}
});

function upperCase(){
	var phone = $('input[name="phone"]').val();
	if(!(/^1[345678]\d{9}$/.test(phone))){
		$("#tips").text('手机号码有误，请重填!');
		$('input[name="phone"]').val("");
		return false;
	}
}
function upperCase(){
	var weixin = $('input[name="weixin"]').val();
	if(!(/^1[345678]\d{9}$/.test(weixin))){
		$("#tips").text('微信号码有误，请重填!');
		$('input[name="weixin"]').val("");
		return false;
	}
}
function upperQq(){
	var qq = $('input[name="qq"]').val();
	if(qq.length < 5 || qq.length > 12){
		$("#tips").text('QQ号码有误，请重填!');
		$('input[name="qq"]').val("");
		return false;
	}
}
window.onload=function(){
	var identity = $.cookie('identity');
	if(!identity){
		window.location.href="/App/index.php";
	}
	var storage=window.localStorage;
	var users = storage["users"];
	var bg = storage["bg"];
	$("#bg").attr('src',bg);
	$("#users").text(users);
}

function addData(){
	var advance = ['id','username', 'qq', 'weixin', 'phone', 'birthday', 'address'];
	// 提交数据使用对象，不能使用数组
	var getData = new Object();
	$.each(advance,function(index, el) {
		var tmp = $('input[name='+el+']').val();
		getData[el] = tmp;
	});
	if($('input[name="id"]').val() != ''){
		$.ajax({
			type: 'post',
			url: 'http://localhost:8081/public/index.php/api/Members/edit',
			dataType: 'json',
			data: getData,
			success: function(msg){
				if(msg.status == 1){
					// window.location.href="index.php";
					$("#tips").text(msg.info);
				}else {
					$("#tips").text(msg.info);
					window.location.reload();
				}
			},
			beforeSend: function(request) {
				request.setRequestHeader("X-Requested-With","XMLHttpRequest");
			}
		});
	}else {
		$.ajax({
			type: 'post',
			url: 'http://localhost:8081/public/index.php/api/Members/add',
			dataType: 'json',
			data: getData,
			success: function(msg){
				if(msg.status == 1){
					window.location.href="index.php";
				}else {
					$("#tips").text(msg.info);
					window.location.reload();
				}
			},
			beforeSend: function(request) {
				request.setRequestHeader("X-Requested-With","XMLHttpRequest");
			}
		});
	}
}

function displayInfo(){
	var options=$("#type option:selected").val();
	var value = $('input[name="value"]').val();
	if(options && value){
		var data = {
			type : options,
			value : value
		};
		$.ajax({
			type: 'post',
			url: 'http://localhost:8081/public/index.php/api/Members/search',
			dataType: 'json',
			data:data,
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
								if (sex=='男') {
					              $("input[name=sex]:eq(0)").prop("check", true);
					            } else {
					                $("input[name=sex]:eq(1)").prop("check", true);
					            }
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

					// 默认赋值
					var qq = $('input[name="'+options+'"]');

					qq.val(value);

				}else if(msg.status == -10) {

					$("#tips").text(msg.info);
				}
			},
			beforeSend: function(request) {
				request.setRequestHeader("X-Requested-With","XMLHttpRequest");
			}
		});
		$("#add_form").show();
	}
}

function logout(){
	$.ajax({
		type: 'post',
		url: 'http://localhost:8081/public/index.php/api/login/logout',
		dataType: 'json',
		success: function(msg){
			if(msg.status == 1){
				var storage=window.localStorage;
            	storage.clear();
				window.location.href="index.php";
			}else {
				window.location.reload();
			}
		},
		beforeSend: function(request) {
			request.setRequestHeader("X-Requested-With","XMLHttpRequest");
		}
	});
}
</script>
</body>
</html>
