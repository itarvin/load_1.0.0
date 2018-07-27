function selfLoadInfo()
{
	var storage = window.localStorage;
	$("#myInfo").attr('src',storage["bg"]);
	$("#myInfo").text(storage["users"]);

}

function logOut()
{
	$.ajax({
		type: 'post',
		url: 'http://localhost:8081/public/index.php/api/login/logout',
		dataType: 'json',
		success: function(msg){
			if(msg.status == 1){
				var storage = window.localStorage;
            	storage.clear();
				window.location.href="index.php";
			}else {
				window.location.reload();
			}
		},
	});
}
selfLoadInfo();
