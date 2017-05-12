<?php
	
include 'database.php';

if (!isset($_POST['func'])
 	&& !isset($_GET['func'])) {
	exit('非法访问！');
}

if ($_SERVER['REQUEST_METHOD']=="POST") {
	
	if (!isset($_POST['func'])) {
		exit('非法访问！');
	}
}
else if ($_SERVER['REQUEST_METHOD']=="GET") {

	if (!isset($_GET['func'])) {
		exit('非法访问！');
	}
	
	if ("getRecommended" == $_GET['func'] ) {
		getRecommended();
	}
}

function getRecommended()
{
	session_start();
	if (!$_SESSION["isLogin"]) {
		echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
		return;
	}
	
	$userId = $_SESSION['userId'];
	
	$con = connectToDB();
	if (!$con)
	{
		echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'设置失败，请稍后重试！'));
		return;
	}

	$result = mysql_query("select * from ClientTable where ReferreeId='$userId'");
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'查找被推荐人时出错，请稍后重试！'));
		return;
	}
	else {	
		$arr = array();
		$num = mysql_num_rows($result);
		if ($num == 0) {	
		}
		else {
			$idx = 1;
			while($row = mysql_fetch_array($result))
			{
				$man = array("id"=>$row["UserId"],
								"name"=>$row["Name"],
							 	"phone"=>$row["PhoneNum"],
							 	"time"=>$row["RegisterTime"]);
			 	$arr[$idx] = $man;
			 	++$idx;
			}
		}
		
		echo json_encode(array('error'=>'false', 'list'=>$arr));
	}
	return;
}
	
?>