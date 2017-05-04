<?php

include "../php/database.php";

if (!isset($_COOKIE['isLogin']) || !$_COOKIE['isLogin']) {
	
	$home_url = '../index.php';
	header('Location: ' . $home_url);
	exit();
}

session_start();

$result = false;
$con = connectToDB();
if (!$con) {
	exit();
}

$result = mysql_query("select * from Product where Status=1");
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

		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
		
		<script src="../js/jquery-1.8.3.min.js"></script>		
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
// 			$(document).ready(function(){
// 				if (!isLogined())
//  					location.href = "pleaselogin.html";

/*
				var data = 'func=getProducts';
				$.getJSON("../php/product.php", data, function(json){
					
					var container = document.getElementById("product_list");
					for (var key in json) {
		 				var h = document.createElement("ul");
		 				
		 				var i = document.createElement("li");
		 				i.innerHTML = json[key]["name"];
		 				i.id = "product_name" + key;
		 				h.appendChild(i);
		 				
		 				var j = document.createElement("li");
		 				j.innerHTML = json[key]["price"];
		 				h.appendChild(j);
		 				
		 				var k = document.createElement("input");
		 				k.type="button";
		 				k.value = "购买";
		 				k.var = key;
// 		 				o.onclick = "editAddress()";
						if (k.addEventListener) {
							k.addEventListener('click', buyItem, false);
						}
						else if (o.attachEvent) {
							k.attachEvent('onclick', buyItem);
						}
		 				h.appendChild(k);

		 				container.appendChild(h);
					}
				});
			});
*/
			
			function buyItem() {
				
				var productId = e.target.var;
				var productName = document.getElementById("product_name" + productId).innerHTML;
				setCookie("willbuy", productId, 0.5);
				setCookie("willbuyname", productName, 0.5);
				location.href = "deal.php";
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
				$width = "<script type=text/javascript>document.write(document.body.clientWidth)</script>";
				while($row = mysql_fetch_array($result)) {
			?>
					<div class="product_frame" width="<?php echo $width?>" height="100px" style="width: <?php echo $width; ?>px; height: 100px;"></div>
			<?php
					echo $width;
				}
			?>
        </div>
        <div class="product_frame" width="100px" height="100px"></div>
    </body>
    <div style="text-align:center;">
    </div>
</html>