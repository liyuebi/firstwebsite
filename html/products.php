<?php

include "../php/database.php";

session_start();
// check if logined. check cookie to limit login time
// check session first to avoid if user close browser and reopen, cookie is still valid but can't find session
if ((isset($_SESSION['isLogin']) && $_SESSION['isLogin'])
	&& (isset($_COOKIE['isLogin']) && $_COOKIE['isLogin'])) {
	// no code here, just continue;		
} 
else {
	$home_url = '../index.php';
	header('Location: ' . $home_url);
	exit();
}

$result = false;
$con = connectToDB();
if (!$con) {
	exit();
}

$result = mysql_query("select * from Product where Status=1 and ProductId!=2");
$error = mysql_error();
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>产品</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">

		<link rel="stylesheet" type="text/css" href="../css/mystyle-1.01.css" />
		
		<script src="../js/jquery-1.8.3.min.js"></script>		
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			$(document).ready(function(){
				
				var width = $(".product_frame").width();
				$(".product_frame").height(width * 0.9);
				$(".img_container").height(width * 0.7);
			})
			
/*
			function buyItem() {
				
				var productId = e.target.var;
				var productName = document.getElementById("product_name" + productId).innerHTML;
				setCookie("willbuy", productId, 0.5);
				setCookie("willbuyname", productName, 0.5);
				location.href = "deal.php";
			}
*/
			
			function buyItem(div) {
				
				var productId = div.id;
				location.href = "productdetail.php?product_id=" + productId;
			}
		</script>
	</head>
	<body>
		<div id="banner_bar" class="banner_info">			
			<a class="banner_info_home" href='home.php'>蜜蜂工坊</a>
 			<input class="banner_info_logout" id="btnlogin" type="button" value="退出登录" onclick="logout()"/>
 			<a class="banner_info_data" href='me.php'>我的资料</a></p>
		</div>
		
        <div id="product_list">
			<?php
				while($row = mysql_fetch_array($result)) {
			?>	
					<div class="product_frame" id="<?php echo $row["ProductId"]; ?>" style="border: black solid 1;" onclick="buyItem(this)" >
						<div class="img_container" align="center" style="text-align: center;">
							<img src="<?php if ($row["FirstImg"] != "") echo "../img/icon/" . $row["FirstImg"]; ?>" style="max-width: 100%; max-height: 100%;" ></img>
						</div>
						<h3 align="center"><?php echo $row["ProductName"]; ?></h3>
						<p><?php echo $row["Price"]; ?> 采蜜券</p>
					</div>
			<?php
				}
			?>
        </div>
    </body>
    <div style="text-align:center;">
    </div>
</html>