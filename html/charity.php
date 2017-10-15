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
	header('Location: ' . $home_url);
	exit();
}

include "../php/database.php";
include "../php/constant.php";
$con = connectToDB();

$userid = $_SESSION["userId"];
$result = false;

if ($con) {
	
	$result = mysql_query("select * from Credit where UserId='$userid'");
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>会员慈善</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<link rel="stylesheet" type="text/css" href="../css/mystyle-1.01.css" />
		<link rel="stylesheet" href="../css/buttons.css">
		
		<script src="../js/jquery-1.8.3.min.js" ></script>
		<script src="../js/scripts.js" ></script>
		<script type="text/javascript">
			
			function goback() 
			{
				location.href = "home.php";
			}
		</script>
	</head>
	<body>
		<div style="height: 50px; margin-top: 10px; background-color: rgba(255, 255, 255, 0.24)">
			<h2 style="display: inline">会员慈善</h2>
			<input type="button" style="float: right" value="返回" class="button" onclick="goback()" />
		</div>
<!-- 		<p align="right">交易记录</p> -->
		<p>您投入的会员慈善金额为：</p>
		<p align="center"><b><?php if ($result) { $row = mysql_fetch_array($result); echo $row["Charity"]; } else echo 0;?></b></p>
    </body>
</html>