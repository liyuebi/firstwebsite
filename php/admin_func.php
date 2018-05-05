<?php

function setAdminCookie($name, $userid) 
{
	$time = time() + 60 * 60;
	setcookie("name", $name, $time, '/');
	setcookie("adminId", $userid, $time, '/');
	setcookie("adminLogin", "true", $time, '/');
}

function deleteAdminCookie()
{
	$time = time() - 1000;
	setcookie("name", '', $time, '/');
	setcookie("adminId", '', $time, '/');
	setcookie("adminLogin", 'false', $time, '/');
}

function setAdminSession($row)
{
	$_SESSION['adminUid'] = $row['AdminId'];
	$_SESSION['name'] = $row['Name'];
	$_SESSION['pwd'] = $row['Password'];
	$_SESSION['priority'] = $row['Priority'];
	$_SESSION['adminLogin'] = true;
	
	setAdminCookie($row['Name'], $row['AdminId']);
}

function checkLoginOrJump()
{
	if (!isset($_COOKIE['adminLogin']) || !$_COOKIE['adminLogin']) {	
		$home_url = '../admin.php';
		header('Location: ' . $home_url);
		return false;
	}
	
	session_start();
	setAdminCookie($_SESSION['name'], $_SESSION['adminUid']);
	
	return true;
}

function isAdminLogin()
{
	return isset($_SESSION['adminLogin']) && $_SESSION['adminLogin'];
}

function getIndexDisplayCnt($con, $bSetCookie=true)
{
	include 'constant.php';

 	$cntNewPackOrder = 0;
	$cntNewUesrOrder = 0;	
	$cntPhoneChargeOrder = 0;
	$cntPhoneChargeForNew = 0;
	$cntOilChargeOrder = 0;

	$cntOLReview = 0;
	$cntOLWithdrawApply = 0;
	$cntPntWithdrawApply = 0;

	$cntExchangeOnSell = 0;

	$res = mysqli_query($con, "select * from Transaction where Type='8' and Status='$OrderStatusBuy'");
	if ($res) {
		$cntNewPackOrder = mysqli_num_rows($res);
	}

	$res = mysqli_query($con, "select * from Transaction where Type='1' and Status='$OrderStatusBuy'");
	if ($res) {
		$cntNewUesrOrder = mysqli_num_rows($res);
	}
	$res = mysqli_query($con, "select * from Transaction where Type='2' and Status='$OrderStatusBuy'");
	if ($res) {
		$cntPhoneChargeOrder = mysqli_num_rows($res);
	}
	$res = mysqli_query($con, "select * from Transaction where Type='4' and Status='$OrderStatusPaid'");
	if ($res) {
		$cntPhoneChargeForNew = mysqli_num_rows($res);
	}
	$res = mysqli_query($con, "select * from Transaction where Type='3' and Status='$OrderStatusBuy'");
	if ($res) {
		$cntOilChargeOrder = mysqli_num_rows($res);
	}

	$res = mysqli_query($con, "select * from OfflineShop where Status='$olshopApplied'");
	if ($res) {
		$cntOLReview = mysqli_num_rows($res);
	}
	$res = mysqli_query($con, "select * from ProfitWdApplication where Status='$olShopWdApplied'");
	if ($res) {
		$cntOLWithdrawApply = mysqli_num_rows($res);
	}
	$res = mysqli_query($con, "select * from PntsWdApplication where Status='$olShopWdApplied'");
	if ($res) {
		$cntPntWithdrawApply = mysqli_num_rows($res);
	}

	$res = mysqli_query($con, "select * from CreditTrade where Status='$creditTradeInited'");
	if ($res) {
		$cntExchangeOnSell = mysqli_num_rows($res);
	}

	if ($bSetCookie) {

		$time = time() + 24 * 60 * 60;
		setcookie("c_n_u_p", $cntNewPackOrder, $time, '/');
		setcookie("c_n_u_o", $cntNewUesrOrder, $time, '/');		
		setcookie("c_p_c_o", $cntPhoneChargeOrder, $time, '/');
		setcookie("c_p_c_f_n", $cntPhoneChargeForNew, $time, '/');	
		setcookie("c_o_c_o", $cntOilChargeOrder, $time, '/');
		setcookie("c_ol_r", $cntOLReview, $time, '/');	
		setcookie("c_ol_wd_a", $cntOLWithdrawApply, $time, '/');
		setcookie("c_p_wd_a", $cntPntWithdrawApply, $time, '/');	
		setcookie("c_ex_on", $cntExchangeOnSell, $time, '/');
	}
}

?>
