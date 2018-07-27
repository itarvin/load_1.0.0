<!doctype html>
<html>

<head>
<meta charset="utf-8">
<title>客户登录系统测试版|710,600</title>
<link rel="stylesheet" type="text/css" href="./css/login.css">
<script type="text/javascript" src="./js/myJquery.js"></script>
<script type="text/javascript" src="./js/kv_cookie.js"></script>

<style type="text/css">
body{background:#fff;}
input{border:1px solid #ccc; line-height: 28px;}
	.backbtn{font-size: 18px; line-height: 40px; padding: 0px 6px 0px 34px;background:url(images/back.png) left center no-repeat;cursor: pointer;}
	.backbtn:hover{color:#81ab2c;}
	.clientList{margin: 0 auto;}
	.clientTop{height:40px;background-color:#f0f0f0;}
	.frBox{margin-top:4px;}
	#dbdate{margin:0 5px;}
	#submit{padding:5px 16px;}
	.table tr{text-align:center;line-height:38px;}
	.czuo a{height:30px;font-size:16px;line-height: 30px;margin: 0 8px; text-decoration:none;}
	.czuo img{border:none;vertical-align: middle;width:14px;height:14px;}
	.tbody tr td{font-size: 14px;}
	.pagination li{width:26px; height:26px;display:inline-block; float:left;line-height:26px;margin:0 4px;text-align:center;border:1px solid #e2e2e2;}
	.pagination .active{background-color: #25a518;color:#fff;}
	.pagination .disabled{line-height: 26px;}
	.page_input {width: 30px;}
	.page span {padding-left: 10px;}
</style>
</head>
<body>


<div class="clientList">
	<div class="clientTop">
		<a href="home.php" class="fl backbtn">返回</a>
		<form action="" class="fr frBox">
			<table>
				<tr>
					<td><input type="text" name="search"></td>
					<td><input type="text" name="dbdate" id="dbdate"></td>
					<td><input type="submit" id="submit" value="搜索"></td>
				</tr>
			</table>
		</form>
	</div>
	<table width="100%" border="0" cellpadding="4" cellspacing="2" class="table" align="center">
		<thead>
			<tr>
				<th>姓名</th>
				<th>性别</th>
				<th>年龄</th>
				<th>QQ</th>
				<th>消费</th>
				<th>时间</th>
				<th>操作</th>
			</tr>
		</thead>
        <tbody class="tbody" id="content">

        </tbody>
        <tfoot>
            <tr class="page">
				<td colspan="7">
					<button id="total"></button>
					<button id="per_page"></button>
					<button id="first_page">首页</button>
					<button id="prev_page">上一页</button>
					<span>当前<input value="" id="current_page" class="page_input" onblur="getdata()">页</span>
					<button id="next_page">下一页</button>
					<button id="end_page">尾页</button>
				</div>
				<div class="clear"></div>
			</td>
		</tr>
	</tfoot>
	</table>
</div>
<script>

function getdata()
{
	var page = $("#current_page").val();
	loadData(page);
}

window.onload=function(){
	var identity = $.cookie('identity');
	if(identity != null){
		var contentType ="application/x-www-form-urlencoded; charset=utf-8";
		$.ajax({
			type: 'post',
			url: 'http://localhost:8081/public/index.php/api/Members/index',
			dataType: 'json',
			contentType:contentType,
			success: function(msg){
				var html = "";
				if(msg.status == '1'){
					var data = msg.data.data.data;
					$.each(data,function(index, el) {
						html += '<tr bgcolor="#ffffff"><td>'+el.username+'</td><td>'+el.sex+'</td><td>'+el.age+'</td><td>'+el.qq+'</td><td>'+el.qq+'</td><td>'+el.newtime+'</td><td class="czuo"><a href=""><img src="images/icon_1.png">编辑</a><a href=""><img src="images/icon_2.png">删除</a><a href=""><img src="images/icon_3.png">消费</a></td></tr>';
					});
					$('#content').html(html);

					// 处理分页

					var page = msg.data.data;

					$("#total").html('共'+page.last_page+'页'+page.total+'条');

					if(page.current_page == 1){

						$("#first_page").hide();
						$("#prev_page").hide();

						if((page.last_page - page.current_page) > 1){
							$("#next_page").attr("onclick","loadData("+(page.current_page + 1)+")");
							$("#end_page").attr("onclick","loadData("+page.last_page+")");
						}else {
							$("#end_page").hide();
							$("#next_page").hide();
						}
					}else if (page.current_page == page.last_page) {

						$("#end_page").hide();
						$("#next_page").hide();

						if((page.last_page - page.current_page) > 1){
							$("#prev_page").attr("onclick","loadData("+(page.current_page - 1)+")");
							$("#first_page").attr("onclick","loadData(1)");
						}else {
							$("#first_page").hide();
							$("#prev_page").hide();
						}
					}
					$("#per_page").text('每页'+page.per_page+'条');

					$("#current_page").val(page.current_page);

					$("#total").html('共'+page.last_page+'页'+page.total+'条');
				}else {
					html += "<tr bgcolor='#ffffff'><td>"+el.username+"</td><td>"+el.sex+"</td><td>"+el.age+"</td><td>"+el.qq+"</td><td>"+el.qq+"</td><td>"+el.newtime+"</td><td class='czuo'><a href='s'><img src='images/icon_1.png'> 编辑</a><a href=''><img src='images/icon_2.png'> 删除</a><a href=''><img src='images/icon_3.png'> 消费</a></td></tr>";
					$('#content').html(html);
				}
			}
		})
	}else {
		window.location.href="index.php";
	}
}

function loadData(page)
{
	$('#content').html("");
	var contentType ="application/x-www-form-urlencoded; charset=utf-8";
	$.ajax({
		type: 'post',
		url: 'http://localhost:8081/public/index.php/api/Members/index.html?page='+page,
		dataType: 'json',
		contentType:contentType,
		success: function(msg){
			var html = "";
			if(msg.status == '1'){
				var data = msg.data.data.data;
				$.each(data,function(index, el) {
					html += "<tr bgcolor='#ffffff' onmouseover='this.style.backgroundColor=#F2F2F2'><td>"+el.username+"</td><td>"+el.sex+"</td><td>"+el.age+"</td><td>"+el.qq+"</td><td>"+el.qq+"</td><td>"+el.newtime+"</td><td class='czuo'><a href='s'><img src='images/icon_1.png'> 编辑</a><a href=''><img src='images/icon_2.png'> 删除</a><a href=''><img src='images/icon_3.png'> 消费</a></td></tr>";
				});
				$('#content').html(html);

				// 处理分页

				var page = msg.data.data;
				// console.log(page.current_page);
				$("#total").html('共'+page.last_page+'页'+page.total+'条');

				if(page.current_page == 1){

					$("#first_page").hide();
					$("#prev_page").hide();
					if((page.last_page - page.current_page) > 1){
						$("#next_page").attr("onclick","loadData("+(page.current_page + 1)+")");
						$("#end_page").attr("onclick","loadData("+page.last_page+")");
					}else {
						$("#end_page").hide();
						$("#next_page").hide();
					}
				}else if (page.current_page == page.last_page) {

					$("#end_page").hide();
					$("#next_page").hide();

					if((page.last_page - page.current_page) > 1){
						$("#prev_page").attr("onclick","loadData("+(page.current_page - 1)+")");
						$("#first_page").attr("onclick","loadData(1)");
					}else {
						$("#first_page").hide();
						$("#prev_page").hide();
					}
				}else {
					$("#first_page").show();
					$("#prev_page").show();
					$("#end_page").show();
					$("#next_page").show();
					$("#next_page").removeAttr("onclick");
					$("#end_page").removeAttr("onclick");
					$("#prev_page").removeAttr("onclick");
					$("#first_page").removeAttr("onclick");
					if((parseInt(page.last_page) - parseInt(page.current_page)) > 1){
						$("#next_page").attr("onclick","loadData("+(parseInt(page.current_page) + 1)+")");
						$("#end_page").attr("onclick","loadData("+page.last_page+")");
						$("#prev_page").attr("onclick","loadData("+(parseInt(page.current_page) - 1)+")");
						$("#first_page").attr("onclick","loadData(1)");
					}
				}
				$("#per_page").text('每页'+page.per_page+'条');

				$("#current_page").val(page.current_page);

				$("#total").html('共'+page.last_page+'页'+page.total+'条');
			}else {
				html += "<tr bgcolor='#ffffff' onmouseover='this.style.backgroundColor=#F2F2F2'><td>"+el.username+"</td><td>"+el.sex+"</td><td>"+el.age+"</td><td>"+el.qq+"</td><td>"+el.qq+"</td><td>"+el.newtime+"</td><td class='czuo'><a href='s'><img src='images/icon_1.png'> 编辑</a><a href=''><img src='images/icon_2.png'> 删除</a><a href=''><img src='images/icon_3.png'> 消费</a></td></tr>";
				$('#content').html(html);
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
