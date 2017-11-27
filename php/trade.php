<?php
	
include 'database.php';

if (!isset($_POST['func'])) {
	exit('非法访问！');
}

if ("phoneCharge" == $_POST['func']) {
	createChargePhoneOrder();
}
else if ("fuelCharge" == $_POST['func']) {
	createChargeFuelOrder();
}
else if ("fcla" == $_POST['func']) {
	createChargeFuelLAOrder();
}
else if ("purchase" == $_POST['func']) {
	purchaseProduct();
}
else if ("delivery" == $_POST['func']) {
	deliveryProduct();
}
else if ("deliveryPhone" == $_POST['func']) {
	deliveryPhoneFare();
}
else if ("deliveryOil" == $_POST['func']) {
	deliveryOilFare();
}
else if ("markExported" == $_POST['func']) {
	markProductExported();
}
else if ("accept" == $_POST['func']) {
	acceptProduct();
}
else if ("confirmOrder" == $_POST['func']) {
	confirmOrder();
}
else if ("queryUserOrder" == $_POST['func']) {
	quertUserOrder();
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

function createChargePhoneOrder()
{
	include 'constant.php';
	include 'regtest.php';
	
	$phonenum = trim(htmlspecialchars($_POST['phonenum']));
	$amount = trim(htmlspecialchars($_POST['amount']));
	$paypwd = trim(htmlspecialchars($_POST['paypwd']));
	
	// 验证电话号码
	if (!isValidCellPhoneNum($phonenum)) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'电话号码格式不对，请重新填写！'));
		return;
	}
	
	if ($amount < $phoneChargeLeast) {
		$str = '充值额度不能小于' . $phoneChargeLeast . '，请重新输入！';
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>$str));
		return;
	}
	else if ($amount > $phoneChargeMost) {
		$str = '充值额度不能大于' . $phoneChargeMost . '，请重新输入！';
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>$str));
		return;		
	}

	if ($amount % 10 != 0) {
		echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'充值额度须为10的整数倍！'));
		return;		
	}
	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	if (!password_verify($paypwd, $_SESSION['buypwd'])) {
		echo json_encode(array('error'=>'true','error_code'=>'15','error_msg'=>'支付密码错误，请重新输入！'));
		return;
	}	
	
	$con = connectToDB();
	if (!$con) {
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	else {
		
		$userid = $_SESSION["userId"];
		$handleFee = $amount * $phoneChargeRate;
		
		$res = mysql_query("select * from Credit where UserId='$userid'");
		if (!$res || mysql_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'查询用户信息出错！'));
			return;				
		}
		
		$row = mysql_fetch_assoc($res);
		$credit = $row["Credits"];
		
		if ($credit < $handleFee + $amount) {
			echo json_encode(array('error'=>'true','error_code'=>'6','error_msg'=>'您的线上云量余额不足！'));
			return;					
		}
		
		$result = createTransactionTable();
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'7','error_msg'=>'交易创建失败，请稍后重试！'));	
			return;						
		}
		
		$now = time();
		$res1 = mysql_query("insert into Transaction (UserId, Type, ProductId, Price, HandleFee, Count, CellNum, OrderTime, Status)
								values($userid, 2, 0, $amount, $handleFee, 1, $phonenum, $now, $OrderStatusBuy)");
		if (!$res1) {
			echo json_encode(array('error'=>'true','error_code'=>'8','error_msg'=>'交易创建失败，请稍后重试！'));	
			return;				
		}
		
		$credit = $credit - $handleFee - $amount;
		$res1 = mysql_query("update Credit set Credits='$credit' where UserId='$userid'");
		if (!$res1) {
			/// !! log error
		}
		else {
			$res2 = mysql_query("insert into CreditRecord (UserId, Amount, CurrAmount, HandleFee, ApplyTime, AcceptTime, Type)
									values($userid, $amount, $credit, $handleFee, $now, $now, $codeTryChargePhone)");
			if (!$res2) {
				/// !! log error
			}	
		}
	}
	
	echo json_encode(array("error"=>"false"));
	return;
}

function createChargeFuelOrder()
{
	include 'constant.php';
	include 'regtest.php';
	
	$card = trim(htmlspecialchars($_POST['card']));
	$phonenum = trim(htmlspecialchars($_POST['phonenum']));
	$amount = trim(htmlspecialchars($_POST['amount']));
	$paypwd = trim(htmlspecialchars($_POST['paypwd']));
	
	// 验证电话号码
	if (!isValidCellPhoneNum($phonenum)) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'电话号码格式不对，请重新填写！'));
		return;
	}
	
	if ($amount < $oilChargeLeast) {
		$str = '充值额度不能小于' . $oilChargeLeast . '，请重新输入！';
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>$str));
		return;
	}
	else if ($amount > $oilChargeMost) {
		$str = '充值额度不能大于' . $oilChargeMost . '，请重新输入！';
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>$str));
		return;		
	}

	if ($amount % 10 != 0) {
		echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'充值额度须为10的整数倍！'));
		return;		
	}
	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	if (!password_verify($paypwd, $_SESSION['buypwd'])) {
		echo json_encode(array('error'=>'true','error_code'=>'15','error_msg'=>'支付密码错误，请重新输入！'));
		return;
	}	
	
	$con = connectToDB();
	if (!$con) {
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	else {
		$userid = $_SESSION["userId"];
		$handleFee = $amount * $oilChargeRate;
		
		$res = mysql_query("select * from Credit where UserId='$userid'");
		if (!$res || mysql_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'查询用户信息出错！'));
			return;				
		}
		
		$row = mysql_fetch_assoc($res);
		$credit = $row["Credits"];
		
		if ($credit < $handleFee + $amount) {
			echo json_encode(array('error'=>'true','error_code'=>'6','error_msg'=>'您的线上云量余额不足！'));
			return;					
		}
		
		$result = createTransactionTable();
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'7','error_msg'=>'交易创建失败，请稍后重试！'));	
			return;						
		}
		
		$now = time();
		$res1 = mysql_query("insert into Transaction (UserId, Type, ProductId, Price, HandleFee, Count, CardNum, CellNum, OrderTime, Status)
								values($userid, 3, 0, $amount, $handleFee, 1, '$card', $phonenum, $now, $OrderStatusBuy)");
		if (!$res1) {
			echo json_encode(array('error'=>'true','error_code'=>'8','error_msg'=>'交易创建失败，请稍后重试！','sql_error'=>mysql_error()));	
			return;				
		}
		
		$credit = $credit - $handleFee - $amount;
		$res1 = mysql_query("update Credit set Credits='$credit' where UserId='$userid'");
		if (!$res1) {
			/// !! log error
		}
		else {
			$res2 = mysql_query("insert into CreditRecord (UserId, Amount, CurrAmount, HandleFee, ApplyTime, AcceptTime, Type)
									values($userid, $amount, $credit, $handleFee, $now, $now, $codeTryChargePhone)");
			if (!$res2) {
				/// !! log error
			}	
		}
	}
	
	echo json_encode(array("error"=>"false"));
}

function createChargeFuelLAOrder()
{
	include 'constant.php';
	include 'regtest.php';
	
	$card = trim(htmlspecialchars($_POST['card']));
	$phonenum = trim(htmlspecialchars($_POST['phonenum']));
	$amount = trim(htmlspecialchars($_POST['amount']));
	$paypwd = trim(htmlspecialchars($_POST['paypwd']));
	
	// 验证电话号码
	if (!isValidCellPhoneNum($phonenum)) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'电话号码格式不对，请重新填写！'));
		return;
	}
	
	$amount = intval($amount);

	if ($amount != 1000 && $amount != 2000) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'充值额度无效！'));
		return;		
	}
	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	if (!password_verify($paypwd, $_SESSION['buypwd'])) {
		echo json_encode(array('error'=>'true','error_code'=>'15','error_msg'=>'支付密码错误，请重新输入！'));
		return;
	}	
	
	$con = connectToDB();
	if (!$con) {
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}
	else {
		$userid = $_SESSION["userId"];
		$handleFee = $amount * $oilChargeRate;
		
		$res = mysql_query("select * from Credit where UserId='$userid'");
		if (!$res || mysql_num_rows($res) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'查询用户信息出错！'));
			return;				
		}
		
		$row = mysql_fetch_assoc($res);
		$credit = $row["Credits"];
		
		if ($credit < $handleFee + $amount) {
			echo json_encode(array('error'=>'true','error_code'=>'6','error_msg'=>'您的线上云量余额不足！'));
			return;					
		}
		
		$result = createTransactionTable();
		if (!$result) {
			echo json_encode(array('error'=>'true','error_code'=>'7','error_msg'=>'交易创建失败，请稍后重试！'));	
			return;						
		}
		
		$now = time();
		$res1 = mysql_query("insert into Transaction (UserId, Type, ProductId, Price, HandleFee, Count, CardComp, CardNum, CellNum, OrderTime, Status)
								values($userid, 3, 0, $amount, $handleFee, 1, 2, '$card', $phonenum, $now, $OrderStatusBuy)");
		if (!$res1) {
			echo json_encode(array('error'=>'true','error_code'=>'8','error_msg'=>'交易创建失败，请稍后重试！','sql_error'=>mysql_error()));	
			return;				
		}
		
		$credit = $credit - $handleFee - $amount;
		$res1 = mysql_query("update Credit set Credits='$credit' where UserId='$userid'");
		if (!$res1) {
			/// !! log error
		}
		else {
			$res2 = mysql_query("insert into CreditRecord (UserId, Amount, CurrAmount, HandleFee, ApplyTime, AcceptTime, Type)
									values($userid, $amount, $credit, $handleFee, $now, $now, $codeTryChargePhone)");
			if (!$res2) {
				/// !! log error
			}	
		}
	}
	
	echo json_encode(array("error"=>"false"));
}

function reinvest()
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
	
	if (!password_verify($paypwd, $_SESSION['buypwd'])) {
		echo json_encode(array('error'=>'true','error_code'=>'15','error_msg'=>'支付密码错误，请重新输入！'));
		return;
	}
	
	if ($count <= 0) {
		echo json_encode(array("error"=>"true","error_code"=>'4',"error_msg"=>"选择的数量无效！"));			
		return;
	}

	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}

	$userid = $_SESSION["userId"];
	
	$boughtLimit = 0;
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
			$boughtLimit = $product["LimitOneDay"];
		}
	}
	
	// 检查是否超过一日购买上限
	if ($boughtLimit > 0) {
	 	$boughtCount = getDayBoughtCount($userid, $productId);
	 	if ($count > $boughtLimit - $boughtCount) {
 			echo json_encode(array('error'=>'true','error_code'=>'14','error_msg'=>'购买个数超过今天剩余的上限，请重新选择！'));
			return;				
	 	}
 	}
 	
 	// 检查是否超过等级购买上限
 	$lvlBought = getLevelBoughtCnt($userid, $_SESSION['lvl'], 0);	// use 0 as product id for reinvest
 	if ($lvlBought >= $levelReinvestTime[$_SESSION['lvl'] - 1]) {
		echo json_encode(array('error'=>'true','error_code'=>'15','error_msg'=>'当前等级下此产品已达到购买上限，暂时不能购买！'));
		return;				
 	}
 	if ($count > $levelReinvestTime[$_SESSION['lvl'] - 1] - $lvlBought) {
		echo json_encode(array('error'=>'true','error_code'=>'16','error_msg'=>'购买个数超过当前等级购买上限，请重新选择！'));
		return;				
 	}
	
	if ($price <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'产品的价格信息出错，请稍后再试'));
		return;
	}
		
	// 更新小金库
	$res1 = mysql_query("select * from ClientTable where UserId='$userid'");
	if (!$res1) {
		echo json_encode(array('error'=>'true','error_code'=>'13','error_msg'=>'增加小金库数值时出错1！'));
		return;				
	}
	$row1 = mysql_fetch_assoc($res1);
	
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
	
	$address = '';
	$zipcode = '';
	$receiver = '';
	$phonenum = '';
	if ($productId != 2) {
		
		$result = mysql_query("select * from Address where AddressId='$addressId' and UserId='$userid'");
		if (!$result || mysql_num_rows($result) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'9','error_msg'=>'选择无效的地址，请稍后重试！'));	
			return;								
		}
		$row = mysql_fetch_assoc($result);
		$address = $row["Address"];
// 		$zipcode = $row["ZipCode"];
		$receiver = $row["Receiver"];
		$phonenum = $row["PhoneNum"];
	}
	
	$result = createTransactionTable();
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'10','error_msg'=>'交易创建失败，请稍后重试！'));	
		return;						
	}

	$time = time();	
	$left = $credit - $totalPrice;
	// 更新用户数据
	$lastModified = $creditInfo["LastConsumptionTime"];
	$dayConsume = 0;
	$monConsume = 0;
	$yearConsume = 0;
	$totalConsume = $creditInfo["TotalConsumption"] + $totalPrice;
	if (isInTheSameDay($time, $lastModified)) {
		$dayConsume = $creditInfo["DayConsumption"] + $totalPrice;
	}
	else  {
		$dayConsume = $totalPrice;
	}
	if (isInTheSameMonth($time, $lastModified)) {
		$monConsume = $creditInfo["MonthConsumption"] + $totalPrice;
	}
	else {
		$monConsume = $totalPrice;
	}
	if (isInTheSameYear($time, $lastModified)) {
		$yearConsume = $creditInfo["YearConsumption"] + $totalPrice;
	}
	else {
		$yearConsume = $totalPrice;
	}
	$bpCntPre = $creditInfo["BPCnt"];
	$bpCntPost = $bpCntPre + $count; 
	$lastRwdBPCnt = $creditInfo["LastRwdBPCnt"];
	if ($lastRwdBPCnt == 0) {
		$lastRwdBPCnt = $bpCntPre;
	}
	$cnt = floor(($bpCntPost - $lastRwdBPCnt) / $rewardBPCnt);
	$lastRwdBPCntPost = $lastRwdBPCnt + $cnt * $rewardBPCnt;
	$dynVault = $creditInfo['Vault'] + $count * $dyNewAccountVault;
	
	$result = mysql_query("update Credit set Credits='$left', Vault='$dynVault', LastConsumptionTime='$time', DayConsumption='$dayConsume', MonthConsumption='$monConsume', YearConsumption='$yearConsume', TotalConsumption='$totalConsume', BPCnt='$bpCntPost', LastRwdBPCnt='$lastRwdBPCntPost' where UserId='$userid'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'11','error_msg'=>'扣款失败，请稍后重试！'));
		return;
	}

	$status = $OrderStatusBuy;
	// 虚拟产品，直接完成交易
	if ($productId == 2) {
		$status = $OrderStatusAccept;
	}
	$result = mysql_query("insert into Transaction (UserId, ProductId, Price, Count, AddressId, Receiver, PhoneNum, Address, ZipCode, OrderTime, Status) 
					VALUES('$userid', '$productId', '$totalPrice', '$count', '0', '$receiver', '$phonenum', '$address', '$zipcode', '$time', '$status')");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'12','error_msg'=>'交易插入失败，请稍后重试！'));	
		return;						
	}
	
	include 'func.php';
	
	$hasNewUser = "false";
	$newUserIds = '';
	// 添加新节点，并添加credit信息，没买一盒需要做一次这种操作
	while ($cnt > 0) {
		
		--$cnt;
		
		$phone = $row1["PhoneNum"]; 
		$name = $row1["Name"];
		$idNum = $row1["IDNum"];
		$error_code = '';
		$error_msg = '';
		$sql_error = '';
		
		insertNewUserNode($userid, $phone, $name, $idNum, 0, $newUserId, $error_code, $error_msg, $sql_error);	
	
		if (0 != $newUserId) {
			$result = mysql_query("select * from Credit where UserId='$newUserId'");
			if (!$result) {
				// !!! log error
			}
			else {
				$num = mysql_num_rows($result);
				if ($num == 0) {
					$vault = 0;
					$dynVault = 0;
					$result = mysql_query("insert into Credit (UserId, Vault)
						VALUES('$newUserId', '$vault')");
					if (!$result) {
						// !!! log
					}
				}					
				else {
					// !!! log
				}
			}
			
			$hasNewUser = "true";
			if (strlen($newUserIds) > 0) {
				$newUserIds .= ' ';
			}
			$newUserIds .= strval($newUserId);
		}
		
		// 统计新用户总数增加
// 		insertRecommendStatistics(0, 0, 0);
	}

	// 修改今天购买个数
	updateDayBoughtCount($userid, $productId, $count);
	// 修改等级购买个数
	updateLevelBoughtCount($userid, $_SESSION['lvl'], 0, $count);
	
	// 纪录积分记录
	$now = time();
	mysql_query("insert into CreditRecord (UserId, Amount, CurrAmount, ApplyTime, AcceptTime, Type)
					VALUES('$userid', '$totalPrice', '$left', '$now', '$now', '$codeConsume')");
	
	// 更新统计数据
	insertOrderStatistics($totalPrice, $count);
	
	echo json_encode(array("error"=>"false","has_new_user"=>$hasNewUser,"new_user_id"=>$newUserIds));
	return;
}

function purchaseProduct()
{
	include 'constant.php';
	
	$productId = trim(htmlspecialchars($_POST['productId']));
	if ($productId == '2') {
		reinvest();
		return;
	}
	
	$count = trim(htmlspecialchars($_POST['count']));
	$paypwd = trim(htmlspecialchars($_POST['paypwd']));
	$addressId = trim(htmlspecialchars($_POST['addressId']));
	
	$price = 0;
	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	if (!password_verify($paypwd, $_SESSION['buypwd'])) {
		echo json_encode(array('error'=>'true','error_code'=>'15','error_msg'=>'支付密码错误，请重新输入！'));
		return;
	}
	
	if ($count <= 0) {
		echo json_encode(array("error"=>"true","error_code"=>'4',"error_msg"=>"选择的数量无效！"));			
		return;
	}

	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}

	$userid = $_SESSION["userId"];
	
	$boughtLimit = 0;
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
			$boughtLimit = $product["LimitOneDay"];
		}
	}
	
	// 检查是否超过一日购买上限
	if ($boughtLimit > 0) {
	 	$boughtCount = getDayBoughtCount($userid, $productId);
	 	if ($count > $boughtLimit - $boughtCount) {
 			echo json_encode(array('error'=>'true','error_code'=>'14','error_msg'=>'购买个数超过今天剩余的上限，请重新选择！'));
			return;				
	 	}
 	}
 		
	if ($price <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'产品的价格信息出错，请稍后再试'));
		return;
	}
		
	// 更新小金库
	$res1 = mysql_query("select * from ClientTable where UserId='$userid'");
	if (!$res1) {
		echo json_encode(array('error'=>'true','error_code'=>'13','error_msg'=>'增加小金库数值时出错1！'));
		return;				
	}
	$row1 = mysql_fetch_assoc($res1);
	
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
	
	$address = '';
	$zipcode = '';
	$receiver = '';
	$phonenum = '';
	if ($productId != 2) {
		$result = mysql_query("select * from Address where AddressId='$addressId' and UserId='$userid'");
		if (!$result || mysql_num_rows($result) <= 0) {
			echo json_encode(array('error'=>'true','error_code'=>'9','error_msg'=>'选择无效的地址，请稍后重试！'));	
			return;								
		}
		$row = mysql_fetch_assoc($result);
		$address = $row["Address"];
// 		$zipcode = $row["ZipCode"];
		$receiver = $row["Receiver"];
		$phonenum = $row["PhoneNum"];
	}
	
	$result = createTransactionTable();
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'10','error_msg'=>'交易创建失败，请稍后重试！'));	
		return;						
	}

	$time = time();	
	$left = $credit - $totalPrice;
	// 更新用户数据
	$lastModified = $creditInfo["LastConsumptionTime"];
	$dayConsume = 0;
	$monConsume = 0;
	$yearConsume = 0;
	$totalConsume = $creditInfo["TotalConsumption"] + $totalPrice;
	if (isInTheSameDay($time, $lastModified)) {
		$dayConsume = $creditInfo["DayConsumption"] + $totalPrice;
	}
	else  {
		$dayConsume = $totalPrice;
	}
	if (isInTheSameMonth($time, $lastModified)) {
		$monConsume = $creditInfo["MonthConsumption"] + $totalPrice;
	}
	else {
		$monConsume = $totalPrice;
	}
	if (isInTheSameYear($time, $lastModified)) {
		$yearConsume = $creditInfo["YearConsumption"] + $totalPrice;
	}
	else {
		$yearConsume = $totalPrice;
	}
	$bpCntPre = $creditInfo["BPCnt"];
	$bpCntPost = $bpCntPre + $count; 
	$lastRwdBPCnt = $creditInfo["LastRwdBPCnt"];
	if ($lastRwdBPCnt == 0) {
		$lastRwdBPCnt = $bpCntPre;
	}
	$cnt = floor(($bpCntPost - $lastRwdBPCnt) / $rewardBPCnt);
	$lastRwdBPCntPost = $lastRwdBPCnt + $cnt * $rewardBPCnt;
	$dynVault = $creditInfo['Vault'] + $count * $dyNewAccountVault;
	
	$result = mysql_query("update Credit set Credits='$left', Vault='$dynVault', LastConsumptionTime='$time', DayConsumption='$dayConsume', MonthConsumption='$monConsume', YearConsumption='$yearConsume', TotalConsumption='$totalConsume', BPCnt='$bpCntPost', LastRwdBPCnt='$lastRwdBPCntPost' where UserId='$userid'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'11','error_msg'=>'扣款失败，请稍后重试！'));
		return;
	}

	$status = $OrderStatusBuy;
	// 虚拟产品，直接完成交易
	if ($productId == 2) {
		$status = $OrderStatusAccept;
	}
	$result = mysql_query("insert into Transaction (UserId, ProductId, Price, Count, AddressId, Receiver, PhoneNum, Address, ZipCode, OrderTime, Status) 
					VALUES('$userid', '$productId', '$totalPrice', '$count', '0', '$receiver', '$phonenum', '$address', '$zipcode', '$time', '$status')");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'12','error_msg'=>'交易插入失败，请稍后重试！'));	
		return;						
	}
	
	include 'func.php';
	
	$hasNewUser = "false";
	$newUserIds = '';

	// 修改今天购买个数
	updateDayBoughtCount($userid, $productId, $count);
	
	// 纪录积分记录
	$now = time();
	mysql_query("insert into CreditRecord (UserId, Amount, CurrAmount, ApplyTime, AcceptTime, Type)
					VALUES('$userid', '$totalPrice', '$left', '$now', '$now', '$codeConsume')");
	
	// 更新统计数据
	insertOrderStatistics($totalPrice, $count);
	
	echo json_encode(array("error"=>"false","has_new_user"=>$hasNewUser,"new_user_id"=>$newUserIds));
	return;
}

function confirmOrder()
{
	include_once 'constant.php';
	
	$orderId = trim(htmlspecialchars($_POST['orderId']));
	$paypwd = trim(htmlspecialchars($_POST['paypwd']));
	$addressId = trim(htmlspecialchars($_POST['addressId']));
	
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	if (!password_verify($paypwd, $_SESSION['buypwd'])) {
		echo json_encode(array('error'=>'true','error_code'=>'15','error_msg'=>'支付密码错误，请重新输入！'));
		return;
	}

	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}

	$userid = $_SESSION["userId"];
	
	$result = mysql_query("select * from Transaction where OrderId='$orderId' and Userid='$userid'");
	if (!$result || mysql_num_rows($result) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'找不到对应的订单！'));
		return;
	}	
	$row = mysql_fetch_assoc($result);
	$count = $row['Count'];
	
	$result = mysql_query("select * from Address where AddressId='$addressId' and UserId='$userid'");
	if (!$result || mysql_num_rows($result) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'选择无效的地址！'));	
		return;								
	}
	$row = mysql_fetch_assoc($result);
	$address = $row["Address"];
// 	$zipcode = $row["ZipCode"];
	$receiver = $row["Receiver"];
	$phonenum = $row["PhoneNum"];

	$time = time();
	$result = mysql_query("update Transaction set AddressId='$addressId', Receiver='$receiver', PhoneNum='$phonenum', Address='$address', Status='$OrderStatusBuy', OrderTime='$time' where OrderId='$orderId' and Userid='$userid'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'更新订单状态出错，请稍后重试！'));	
		return;								
	}
	
	echo json_encode(array('error'=>'false'));

}

function deliveryProduct()
{
	include 'constant.php';
	include_once 'admin_func.php';
	
	session_start();
	if (!isAdminLogin()) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$TransactionId = trim(htmlspecialchars($_POST["index"]));
	$courier = trim(htmlspecialchars($_POST['courier']));
	
	if (strlen($courier) <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'无效的快递单号，请重新检查！'));
		return;
	}
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}

	$result = mysql_query("select * from Transaction where OrderId='$TransactionId'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'未查到指定的交易，请稍后重试！'));
		return;		
	}
	
	$row = mysql_fetch_assoc($result);
	$status = $row['Status'];
	
	if ($status != $OrderStatusBuy) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'不是等待发货的状态，请重新检查！'));
		return;
	}
	
	$time = time();
	$result = mysql_query("update Transaction set Status='$OrderStatusDelivery', DeliveryTime='$time', CourierNum='$courier' where OrderId='$TransactionId'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'更新成等待发货状态时出错，请稍后重试！'));
		return;		
	}
		
	echo json_encode(array('error'=>'false','index'=>$TransactionId));
	return;
}

function deliveryPhoneFare()
{
	include 'constant.php';
	include_once 'admin_func.php';
	
	session_start();
	if (!isAdminLogin()) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$TransactionId = trim(htmlspecialchars($_POST["index"]));
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}

	$result = mysql_query("select * from Transaction where OrderId='$TransactionId'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'未查到指定的交易，请稍后重试！'));
		return;		
	}
	
	$row = mysql_fetch_assoc($result);	
	$status = $row['Status'];
	
	if ($status != $OrderStatusBuy) {
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'不是等待发货的状态，请重新检查！'));
		return;
	}
	
	if ($row["Type"] != 2) {
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'订单类型错误！'));
		return;
	}
	
	$time = time();
	$result = mysql_query("update Transaction set Status='$OrderStatusAccept', DeliveryTime='$time', CompleteTime='$time' where OrderId='$TransactionId'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'更新成等待发货状态时出错，请稍后重试！'));
		return;		
	}
	
	include_once "func.php";
	$amount = $row["Price"];
	$fee = $row["HandleFee"];
	insertVLStatistics($amount, $fee);
		
	echo json_encode(array('error'=>'false','index'=>$TransactionId));
	return;
}

function deliveryOilFare()
{
	include 'constant.php';
	include_once 'admin_func.php';
	
	session_start();
	if (!isAdminLogin()) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$TransactionId = trim(htmlspecialchars($_POST["index"]));
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}

	$result = mysql_query("select * from Transaction where OrderId='$TransactionId'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'未查到指定的交易，请稍后重试！'));
		return;		
	}
	
	$row = mysql_fetch_assoc($result);	
	$status = $row['Status'];
	
	if ($status != $OrderStatusBuy) {
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'不是等待发货的状态，请重新检查！'));
		return;
	}
	
	if ($row["Type"] != 3) {
		echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'订单类型错误！'));
		return;
	}
	
	$time = time();
	$result = mysql_query("update Transaction set Status='$OrderStatusAccept', DeliveryTime='$time', CompleteTime='$time' where OrderId='$TransactionId'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'更新成等待发货状态时出错，请稍后重试！'));
		return;		
	}
	
	include_once "func.php";
	$amount = $row["Price"];
	$fee = $row["HandleFee"];
	insertVLStatistics($amount, $fee);
		
	echo json_encode(array('error'=>'false','index'=>$TransactionId));
	return;
}

function acceptProduct()
{
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$TransactionId = trim(htmlspecialchars($_POST["index"]));
	
	$userid = $_SESSION["userId"];
	include 'constant.php';

	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}	
	
	$userid = $_SESSION["userId"];
	$result = mysql_query("select * from Transaction where OrderId='$TransactionId'"); 
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

	$result = mysql_query("update Transaction set Status='$OrderStatusAccept', CompleteTime='$time' where OrderId='$TransactionId'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'更新成交易完成状态时出错，请稍后重试！'));
		return;		
	}
	
// 	echo mysql_error();
	echo json_encode(array('error'=>'false'));
	return;
}

function quertUserOrder()
{
	$userid = trim(htmlspecialchars($_POST["uid"]));	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！','uid'=>$userid));
		return;
	}
	$res = mysql_query("select * from ClientTable where UserId='$userid'");
	if (!$res || mysql_num_rows($res)<=0) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'无效的用户ID，请重新输入！','uid'=>$userid));
		return;				
	}
	$result = mysql_query("select * from Transaction where UserId='$userid'"); 
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'未查到指定的交易，请稍后重试！','uid'=>$userid));
		return;		
	}
	$array = array();
	while ($row = mysql_fetch_array($result)) {
		$productId = $row['ProductId'];
		$res1 = mysql_query("select * from Product where ProductId='$productId'");
		if ($res1 && mysql_num_rows($res1) > 0) {
			$row1 = mysql_fetch_assoc($res1);
			$row["ProductName"] = $row1["ProductName"];
		}
		$array[$row['OrderId']] = $row;
	}
	
	echo json_encode(array('error'=>'false','uid'=>$userid,'num'=>mysql_num_rows($result),'order_list'=>$array));
}

/*
 * Modify db to signal transaction info has been exported to excel.
 */
function markProductExported()
{
	session_start();
	
	include_once "admin_func.php";
	if (!isAdminLogin()) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$ids = trim(htmlspecialchars($_POST['ids']));
	
	$arr = explode(',', $ids);
	$cnt = count($arr);
	if ($cnt <= 0) {
		echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'要导出的订单列表为空！','uid'=>$userid));
		return;
	}
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！','uid'=>$userid));
		return;
	}
	
	for ($idx = 0; $idx < $cnt; ++$idx) {
		
		$orderid = $arr[$idx];
	 	$res = mysql_query("select * from Transaction where OrderId='$orderid'");
	 	if (!$res || mysql_num_rows($res) <= 0) {
		 	// ..
			continue; 	
	 	} 
	 	
	 	$row = mysql_fetch_assoc($res);
	 	$val = $row["Exported"] + 1;
	 	$res1 = mysql_query("update Transaction set Exported='$val' where OrderId='$orderid'");
	 	if (!$res) {
		 	// .. 
		 	continue;
	 	}
	 	
	 	
	}
	
	echo json_encode(array('error'=>'false'));
}

?>