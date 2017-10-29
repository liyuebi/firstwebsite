<?php

$reason = "";
$pagename = "";
$page = "#";

if ($_GET["source"] == '1') {
	$reason = "对不起，您还没有登录哦";
	$pagename = "登录页面";
	$page = "../index.php";
}
else if ($_GET["source"] == '2') { 
	$reason = "请先将默认登录密码修改后再进行相关操作";
	$pagename = "登录密码修改页面";
	$page = "changeLoginPwd.html";
}
else if ($_GET["source"] == '3') { 
	$reason = "设置了支付密码之后才能申请取现";
	$pagename = "支付密码设置页面";
	$page = "setBuyPwd.php";
}
else if ($_GET["source"] == '4') { 
	$reason = "请先设置收款账号";
	$pagename = "支付管理页面";
	$page = "payment.php";
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8">
		<title>跳转</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">
		<meta http-equiv="Refresh" content="5;url=<?php echo $page; ?>" />
		
		<link rel="stylesheet" type="text/css" href="../css/mystyle.css" />
	</head>
	
	<body>
        <div>
			<p><?php echo $reason; ?></p>
			<p>页面将在5秒内跳转到<?php echo $pagename; ?></p>
			<p>如果超过5秒没有跳转，请点击下面的链接</p>
			<a href="<?php echo $page; ?>">点击跳转！</a>
        </div>
    </body>
    <div style="text-align:center;">
    </div>
</html>