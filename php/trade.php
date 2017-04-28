<?php
	
include 'database.php';

if (!isset($_POST['func'])) {
	exit('非法访问！');
}

if ("purchase" == $_POST['func']) {
	purchaseProduct();
}
else if ("delivery" == $_POST['func']) {
	deliveryProduct();
}
else if ("accept" == $_POST['func']) {
	acceptProduct();
}
/*
else if ("logout" == $_POST['func']) {
	logout();	
}
else if ("loginAdmin" == $_POST['func']) {
	loginAdmin();	
}
else if ("setPayPwd" == $_POST['func']) {
	setPayPwd();
}
else if ("changePayPwd" == $_POST['func']) {
	changePayPwd();
}
*/

function purchaseProduct()
{
	include 'constant.php';
	
	$productId = trim(htmlspecialchars($_POST['productId']));
	$count = trim(htmlspecialchars($_POST['count']));
	$paypwd = trim(htmlspecialchars($_POST['paypwd']));
	$addressId = trim(htmlspecialchars($_POST['addressId']));
	
	$price = 0;
	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	if ($_SESSION['buypwd'] != $paypwd) {
		echo json_encode(array('error'=>'true','error_code'=>'15','error_msg'=>'支付密码错误，请重新输入！'));
		return;
	}

	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}

	mysql_select_db("my_db", $con);
	$userid = $_SESSION["userId"];
	
	$result = mysql_query("select * from Product where ProductId='$productId'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'选择的产品无效！'));	
		return;
	}
	else {
		if (mysql_num_rows($result) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'找不到指定产品！'));	
			return;				
		}
		else {
			$product = mysql_fetch_assoc($result);
			$price = $product["Price"];
		}
	}
	
	if ($price <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'产品的价格信息出错，请稍后再试'));
		return;
	}
	
	if ($count <= 0) {
		echo json_encode(array("error"=>"true","error_code"=>4,"error_msg"=>"选择的数量无效！"));			
		return;
	}
	
	// 更新小金库
	$res1 = mysql_query("select * from User where UserId='$userid'");
	if (!$res1) {
		echo json_encode(array('error'=>'true','error_code'=>'13','error_msg'=>'增加小金库数值时出错1！'));
		return;				
	}
	$row1 = mysql_fetch_assoc($result);
	$isDynamic = $row1["RecommendingCount"] > 0;
	
	
	// check if count surpass the buy limit error_code 5，选择的数量超过购买上限
	
	$totalPrice = $price * $count;
	$creditInfo = false;
	$credit = 0;
	
	$res = mysql_query("select * from Credit where UserId='$userid'");
	if (!$res) {
		echo json_encode(array('error'=>'true','error_code'=>'6','error_msg'=>'用户积分信息出错！'));	
		return;
	}
	else {
		if (mysql_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'7','error_msg'=>'用户积分信息出错了！'));	
			return;				
		}
		else {
			$creditInfo = mysql_fetch_assoc($res);
			$credit = $creditInfo["Credits"];
		}
	}
	
	if ($totalPrice > $credit) {
		echo json_encode(array('error'=>'true','error_code'=>'8','error_msg'=>'余额不足，请先充值！'));	
		return;				
	}
	
	$result = mysql_query("select * from Address where AddressId='$addressId' and UserId='$userid'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'9','error_msg'=>'选择无效的地址，请稍后重试！'));	
		return;								
	}
	$row = mysql_fetch_assoc($result);
	$address = $row["Address"];
	$zipcode = $row["ZipCode"];
	$receiver = $row["Receiver"];
	$phonenum = $row["PhoneNum"];
	
	$result = createTranscationTable();
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'10','error_msg'=>'交易创建失败，请稍后重试！'));	
		return;						
	}

	$time = time();	
	$left = $credit - $totalPrice;
	// 更新用户数据
	$LastConsumptionTime = $row["LastConsumptionTime"];
	$dayConsume = 0;
	$monConsume = 0;
	$totalConsume = $row["TotalConsumption"] + $totalPrice;
	if (isInTheSameDay($time, $lastModified)) {
		$dayConsume = $row["DayConsumption"] + $totalPrice;
	}
	else  {
		$dayConsume = $totalPrice;
	}
	if (isInTheSameMonth($time, $lastModified)) {
		$monConsume = $row["MonthConsumption"] + $totalPrice;
	}
	else {
		$monConsume = $totalPrice;
	}
	if (isInTheSameYear($time, $lastModified)) {
// 				$yearRecharge = $row[] + $totalPrice;
	}
	else {
// 				$yearRecharge = $totalPrice;
	}
	$vault = $creditInfo["Vault"];
	$dynVault = $creditInfo["DynamicVault"];
	if ($isDynamic) {
		$vault += $totalPrice * $retRate + $dynVault;
		$dynVault = 0;	
	}
	else {
		$vault += $totalPrice;
		$dynVault += $totalPrice * ($retRate - 1);		// rate must be larger than 1
	}
	$result = mysql_query("update Credit set Credits='$left', LastConsumptionTime='$time', DayConsumption='$dayConsume', MonthConsumption='$monConsume', TotalConsumption='$totalConsume', Vault='$vault', DynamicVault='$dynVault' where UserId='$userid'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'11','error_msg'=>'扣款失败，请稍后重试！'));
		return;
	}

	$result = mysql_query("insert into Transcation (UserId, ProductId, Price, Count, Receiver, PhoneNum, Address, ZipCode, OrderTime, Status) 
					VALUES('$userid', '$productId', '$price', '$count', '$receiver', '$phonenum', '$address', '$zipcode', '$time', '$OrderStatusBuy')");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'12','error_msg'=>'交易插入失败，请稍后重试！'));	
		return;						
	}

	// 纪录积分记录
	$now = time();
	mysql_query("insert into CreditRecord (UserId, Amount, CurrAmount, ApplyTime, AcceptTime, Type)
					VALUES('$userid', '$totalPrice', '$left', '$now', '$now', '$codeConsume')");

	// 给上游用户分成	
	include 'func.php';
	$referBonus = distributeReferBonus($con, $userid, $count);
	
	// 更新统计数据
	$result = createStatisticsTable();
	if ($result) {
		date_default_timezone_set('PRC');
		$year = date("Y", $now);
		$month = date("m", $now);
		$day = date("d", $now);
		
		$result = mysql_query("select * from Statistics where Ye='$year' and Mon='$month' and Day='$day'");
		if ($result && mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			$gross = $row["OrderGross"] + $totalPrice;
			$orderNum = $row["OrderNum"] + 1;
			$refer = $row["RRTotal"] + $referBonus;
			mysql_query("update Statistics set OrderGross='$gross', OrderNum='$orderNum', RRTotal='$refer' where Ye='$year' and Mon='$month' and Day='$day'");
		}
		else {
			mysql_query("insert into Statistics (Ye, Mon, Day, OrderGross, OrderNum, RRTotal)
					VALUES('$year', '$month', '$day', '$totalPrice', '1', '$referBonus')");
		}
	}
	
	echo json_encode(array("error"=>"false"));
	return;
}

function deliveryProduct()
{
	// 权限判断 ！！！！！！！！！！！！！！
	
	include 'constant.php';
	
	$transcationId = trim(htmlspecialchars($_POST["index"]));
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}

	mysql_select_db("my_db", $con);
	$result = mysql_query("select * from Transcation where OrderId='$transcationId'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'未查到指定的交易，请稍后重试！'));
		return;		
	}
	
	$row = mysql_fetch_assoc($result);
	$status = $row['Status'];
	
	if ($status != $OrderStatusBuy) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'不是等待发货的状态，请重新检查！'));
		return;
	}
	
	$time = time();
	$result = mysql_query("update Transcation set Status='$OrderStatusDelivery', DeliveryTime='$time' where OrderId='$transcationId'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'更新成等待发货状态时出错，请稍后重试！'));
		return;		
	}
		
	echo json_encode(array('error'=>'false'));
	return;
}

function acceptProduct()
{
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$transcationId = trim(htmlspecialchars($_POST["index"]));
	
	$userid = $_SESSION["userId"];
	include 'constant.php';

	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}	
	
	mysql_select_db("my_db", $con);
	$userid = $_SESSION["userId"];
	$result = mysql_query("select * from Transcation where OrderId='$transcationId'"); 
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'未查到指定的交易，请稍后重试！'));
		return;		
	}
	
	$row = mysql_fetch_assoc($result);
	$status = $row['Status'];
	
	if ($status != $OrderStatusDelivery) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'货物尚未发货，请重新检查！'));
		return;
	}

	$time = time();

	$result = mysql_query("update Transcation set Status='$OrderStatusAccept', CompleteTime='$time' where OrderId='$transcationId'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'更新成交易完成状态时出错，请稍后重试！'));
		return;		
	}
	
// 	echo mysql_error();
	echo json_encode(array('error'=>'false'));
	return;
}

?>