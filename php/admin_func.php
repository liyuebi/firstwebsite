<?php

function setAdminCookie($name, $userid) 
{
	$time = time() + 60 * 20;
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
// 	return $_SESSION['adminLogin'];
	return true;
}

?>
