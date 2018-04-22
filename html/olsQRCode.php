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

$url = "";
$isOwnShop = false;
$hasQRCode = false;

include "../php/database.php";
$con = connectToDB();
if ($con) {

	$result = mysqli_query($con, "select * from OfflineShop where UserId='$userid'");
	if ($result && mysqli_num_rows($result) > 0) {

		$isOwnShop = true;

		$row = mysqli_fetch_assoc($result);
		$url = $row["QRCode"];
		if ("" != $url) {
			$hasQRCode = true;
		}
	}
}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>商家二维码</title>
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
				
			$(document).ready(function(){

				// $('#main').height($(window).height() - $('#title').height();
				setContentVertialCenter();
				
				$(window).resize(function() {

					setContentVertialCenter();
				});
			});

			function setContentVertialCenter()
			{
				var mainHeight = $(window).height() - $('#title').height();
				var contentHeight = $(`#content`).height();

				if (contentHeight >= mainHeight) {
					$('#main').height(contentHeight);
					$('#main').css("margin-top", "10px");
					$('#space').height(0);
				}
				else {
					$('#main').height(mainHeight);
					$('#main').css("margin-top", "0");
					$('#space').height((mainHeight - contentHeight) / 2);	
				}
			}

			function goback() 
			{
				location.href = "myolshop.php";
			}
		</script>
	</head>
	<body>
		<div id="title" class="container-fluid" style="height: 50px; margin-top: 10px; background-color: rgba(0, 0, 255, 0.32);">
			<div class="row" style="position: relative; top: 10px;">
				<div class="col-xs-3 col-md-3"><a><img src="../img/sys/back.png" style="float: left;" onclick="goback()" </img></a></div>
				<div class="col-xs-6 col-md-6"><h4 style="text-align: center; color: white">商家二维码</h4></div>
				<div class="col-xs-3 col-md-3"></div>
			</div>
		</div>

		<div id="main" style="margin: 10px 3px 0 3px;">
			<div id="space" width="50px">
			</div>
			<div id="content">
			<?php 
				if (!$isOwnShop) {
			?>
				<div class="text-danger well">
					您还没有开启线下商家账号！
				</div>
			<?php  
				}
				else if (!$hasQRCode) {
			?>
				<div class="text-danger well">
					您还没有生成二维码！
				</div>
			<?php	
				}
				else {
			?>
				<div class="alert-info" style="padding: 20px; text-align: center;">
					<h3>连物网商家收款二维码</h3>
					<div style="padding: 40px 10%;">
						<p>
							<span class="pull-left"><b>商家编号：</b> <?php echo $row["ShopId"]; ?></span>
							<span class="pull-right"><?php echo $row["ShopName"];?></span>
						</p>
						<br>
						<img src="<?php echo '../olqrc/' . $url; ?>" style="width: 100%;" >
					</div>
				</div>
			<?php
				}
			?>
			</div>
		</div>	
	</body>
</html>
