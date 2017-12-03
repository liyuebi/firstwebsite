<?php

session_start();
// check if logined. check cookie to limit login time
// check session first to avoid if user close browser and reopen, cookie is still valid but can't find session
if ((isset($_SESSION['isLogin']) && $_SESSION['isLogin'])
	&& (isset($_COOKIE['isLogin']) && $_COOKIE['isLogin'])) {
	// no code here, just continue;		
} 
else {
	$home_url = '../index.php';
	if (isset($_GET['s'])) {
		$home_url = $home_url . '?s=' . $_GET['s'];
	}
	header('Location: ' . $home_url);
	exit();
}

include "../php/constant.php";

$userid = $_SESSION['userId'];

$result = false;
if (isset($_GET['s'])) {

	include "../php/database.php";
	$con = connectToDB();

	$shopId = $_GET['s'];
	if ($con) {
		$result = mysql_query("select * from OfflineShop where ShopId='$shopId'");
	}
}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>线下商家</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/bootstrap-3.3.7/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="../css/mystyle1.0.1.css" />
		<link rel="stylesheet" href="../css/buttons.css">
		
		<script src="../js/jquery-3.2.1.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script src="../js/jquery.form-3.46.0.js" ></script>
		<script src="../js/md5.js" ></script>
		<script type="text/javascript">
						
			function search()
			{
				var cond = document.getElementById("search_cond").value;
				if (cond.length <= 0) {
					alert("请输入搜索条件！");
					return;
				} 
				
				$.post("../php/offlineTrade.php", {"func":"searchShop", "cond":cond}, function(data){
					
					var container = document.getElementById("result_block");
					
					if (data.error == "false") {
						
						var list = data.list;
						
						if (null != container && null != list) {
							
							$("#result_block").empty();
							for (var key in list) {
								
								var hr = document.createElement("hr");
								container.appendChild(hr);
								
								var div = document.createElement("div");
								container.appendChild(div);
								
								var name = document.createElement("label");
								name.innerHTML = "商家： " + list[key].name;
								div.appendChild(name);
								
								var br = document.createElement("br");
								div.appendChild(br);
								
								var cnt = document.createElement("input");
								cnt.className = "form-control";
								cnt.id = "cnt_" + key;
								cnt.placeholder = "请输入支付的线下云量金额！";
								div.appendChild(cnt);
								
								var div1 = document.createElement("div");
								div1.className = "input-group";
								div.appendChild(div1);
								
								var paypwd = document.createElement("input");
								paypwd.className = "form-control";
								paypwd.type = "password";
								paypwd.placeholder = "请输入支付密码！";
								paypwd.id = "pwd_" + key;
								div1.appendChild(paypwd);
								
								var btn = document.createElement("span");
								btn.className = "input-group-btn";
								div1.appendChild(btn);
								
								var pay = document.createElement("input");
								pay.type = "button";
								pay.className = "btn btn-default";
								pay.value = "支付";
								pay.id = key;
								if (pay.addEventListener) {
									pay.addEventListener('click', payShop, false);
								}
								else if (pay.attachEvent) {
									pay.attachEvent('onclick', payShop);
								}
								btn.appendChild(pay);
							}
						}
					}
					else {
						
						$("#result_block").empty();
						var name = document.createElement("label");
						name.innerHTML = "搜索失败: " + data.error_msg;
						name.className = "text-warning";
						container.appendChild(name);
					}
				}, "json");				
			}
			
			function payShop(e)
			{
				var cnt = document.getElementById("cnt_" + e.target.id).value;
				var pwd = document.getElementById("pwd_" + e.target.id).value;
				
				if (cnt.length <= 0) {
					alert("请输入支付金额！");
					return;
				}
				
				if (pwd.length <= 0) {
					alert("请输入支付密码！");
					return;
				}
				
				pwd = md5(pwd);
				$.post("../php/offlineTrade.php", {"func":"pOLS", "cnt":cnt, "paypwd":pwd, "sId":e.target.id}, function(data){
					
					if (data.error == "false") {
						alert("支付成功！");	
// 						document.getElementById("cnt_" + e.target.id).value = "";
// 						document.getElementById("pwd_" + e.target.id).value = "";

						location.href = "record.php?t=1";
					}
					else {
						alert("支付失败：" + data.error_msg);
						document.getElementById("cnt_" + e.target.id).value = "";
						document.getElementById("pwd_" + e.target.id).value = "";
					}
				}, "json");
			}
						
			function gotoMyShop()
			{
				location.href = "myolshop.php";
			}			
						
			function goback() 
			{
				location.href = "home.php";
			}
		</script>
	</head>
	<body>
		<div class="container-fluid" style="height: 50px; margin-top: 10px; background-color: rgba(0, 0, 255, 0.32);">
			<div class="row" style="position: relative; top: 10px;">
				<div class="col-xs-3 col-md-3"><a><img src="../img/sys/back.png" style="float: left;" onclick="goback()" </img></a></div>
				<div class="col-xs-6 col-md-6"><h3 style="text-align: center; color: white">线下商家</h3></div>
				<div class="col-xs-3 col-md-3"><input type="button" class="button button-raised button-rounded button-small" style="float: right" value="我的商家" onclick="gotoMyShop()"></div>
			</div>
		</div>

		<div style="margin: 10px 3px 0 3px;">
			<label>搜索线下商家</label>
			<div class="input-group" >
     			<input class="form-control" id="search_cond" type="text" placeholder="请输入商家编号／商家名称关键字">
     			<div class="input-group-btn">
		 			<button class="btn btn-default" type="button" onclick="search()">搜索</button>
		 			<!-- <button class="btn btn-default" type="button">扫描</button> -->
     			</div>
			</div>
		</div>	
		
		<div id="result_block">
			<?php
				if ($result) {
					while ($row = mysql_fetch_array($result)) {
			?>
					<hr>
					<div>
						<label>商家：<?php echo $row["ShopName"]; ?></label>
						<br>
						<input type="text" class="form-control" id="cnt_<?php echo $row["ShopId"]; ?>" placeholder="请输入支付的线下云量金额！" >

						<div class="input-group">
							<input type="password" class="form-control" id="pwd_<?php echo $row["ShopId"]; ?>" placeholder="请输入支付密码！" >
							<span class="input-group-btn">
								<input type="button" class="btn btn-default" value="支付" id="<?php echo $row["ShopId"]; ?>" onclick="payShop(event)">
							</span>
						</div>
					</div>
			<?php
					}				
				}
			?>
		</div>
	</body>
</html>
