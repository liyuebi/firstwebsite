<?php
	
include "database.php";
include "regtest.php";
include "constant.php";
include_once "func.php";

$phonenum = trim(htmlspecialchars($_POST['phonenum']));
$paypwd = htmlspecialchars($_POST['paypwd']);
// $username = htmlspecialchars($_POST['username']);
// $points = 500;	// default points value
$points = 0;

// 验证电话号码
if (!isValidCellPhoneNum($phonenum)) {
	echo json_encode(array('error'=>'true','error_code'=>'1','error_msg'=>'电话号码格式不对，请重新填写！'));
	return;
}

// 判断是否登录
session_start();
if (!$_SESSION["isLogin"]) {
	echo json_encode(array('error'=>'true','error_code'=>'20','error_msg'=>'请先登录！'));
	return;
}

if ($paypwd != $_SESSION["buypwd"]) {
	echo json_encode(array('error'=>'true','error_code'=>'2','error_msg'=>'支付密码出错，请重试！'));
	return;
}

$con = connectToDB();
if (!$con)
{
	echo json_encode(array('error'=>'true','error_code'=>'30','error_msg'=>'数据库连接失败，请稍后重试！','sql_error'=>mysql_error()));
	return;
}
else 
{		  
	$result = createUserTable();
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'31','error_msg'=>'用户表创建失败，请稍后重试！','sql_error'=>mysql_error())); 
		return;
	}
	$result = createCreditTable();
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'32','error_msg'=>'积分表创建失败，请稍后重试！','sql_error'=>mysql_error())); 
		return;
	}
	
	$userid = $_SESSION["userId"];
	$res1 = mysql_query("select * from Credit where UserId='$userid'");
	$credit = 0;
	if (!$res1) {
	}
	else {
		$row = mysql_fetch_assoc($res1);
		$credit = $row["Credits"];
	}
	
	if ($credit < $refererConsumePoint) {
		echo json_encode(array('error'=>'true','error_code'=>'3','error_msg'=>'您的蜜券不足，不能推荐用户！'));
		return;		
	}
	
	$now = time();
	$sql = "select * from User where PhoneNum='$phonenum'";
	$result = mysql_query($sql, $con);
	$newuserid = 0;
	if (!$result) {
		echo json_encode(array('error'=>'true','error_code'=>'4','error_msg'=>'账号查询出错，请稍后重试！','sql_error'=>mysql_error()));
		return;
	}
	else {
		$num = mysql_num_rows($result);
		if ($num == 0) {

			$error_code = '';
			$error_msg = '';
			$sql_error = '';
			
			$ret = insertNewUserNode($userid, $phonenum, '', '', 0, $newuserid, $error_code, $error_msg, $sql_error);	
			if (!$ret) {
				echo json_encode(array('error'=>'true','error_code'=>$error_code,'error_msg'=>$error_msg,'sql_error'=>$sql_error));
				return;
			}

/*
			$listChild = array($userid);
			$parentId = 0;
			$slot = 0;
			while (count($listChild)) {
				$currUid = array_shift($listChild);
				$res3 = mysql_query("select * from User where UserId='$currUid'");
				if (!$res3 || mysql_num_rows($res3) <= 0) {
					// !!! log
					continue;
				}
				else {
					$child1 = $res3["Group1Child"];
					$child2 = $res3["Group2Child"];
					$child3 = $res3["Group3Child"];
					$lvl = $res3["Lvl"];
					
					if ($child1 == 0) {
						$parentId = $currUid;	
						$slot = 1;
					}
					else if ($child1 > 0) {
						array_push($listChild, $child1);
					}
					
					if ($child2 == 0) {
						$parentId = $currUid;
						$slot = 2;
					}
					else if ($child2 > 0) {
						array_push($listChild, $child2);
					}
					
					if ($lvl >= $group3StartLvl) {
						if ($child3 == 0) {
							$parentId = $currUid;
							$slot = 3;
						}
						else {
							array_push($listChild, $child3);
						}
					}
				}
			}
			
			if ($parentId == 0 || $slot == 0) {
				echo json_encode(array('error'=>'true','error_code'=>'5','error_msg'=>'查找可插入的父节点失败，请稍后重试','sql_error'=>mysql_error()));
				return;
			}
			
			$res4 = mysql_query("insert into User (PhoneNum, Password, ReferreeId, ParentId, RegisterTime)
									values('$phonenum', '000000', '$userid', '$parentId', '$now')");
			if (!$res4) {
				echo json_encode(array('error'=>'true','error_code'=>'6','error_msg'=>'插入用户失败，请稍后重试','sql_error'=>mysql_error()));
				return;
			}
			$newuserid = mysql_insert_id();
			
			$groupName = "";
			$gounpCntName = "";
			if ($slot == 1) {
				$groupName = "Group1Child";
				$gounpCntName = "Group1Cnt";
			}
			else if ($slot == 2) {
				$groupName = "Group2Child";
				$gounpCntName = "Group2Cnt";
			}
			else if ($slot == 3) {
				$groupName = "Group3Child";
				$gounpCntName = "Group3Cnt";
			}
			
			$res5 = mysql_query("update User set $groupName='$newuserid', $gounpCntName=1 where UserId='$parentId'");
			if (!$res5) {
				// !!! log
				echo 'log error:' . mysql_error();
			}
			else {
				$res6 = mysql_query("select * from User where UserId='$parentId'");
				if (!$res6 || mysql_num_rows($res6) <= 0) {
					// !!! log	
				}
				else {			
					$row6 = mysql_fetch_assoc($res6);
					$currUid = $parentId;
					$parentId = $row6["ParentId"];
					while (true) {
						
						if ($parentId <= 0) {
							break;	
						}
						
						$res7 = mysql_query("select * from User where UserId='$parentId'");
						if (!$res7 || mysql_num_rows($res7) <= 0) {
							// !!! log
							// can't find parent node, jump out
							break;
						}
						
						$row7 = mysql_fetch_assoc($res7);					
						$gounpCntName = "";
						$currCnt = 0;
						if ($currUid == $row7["Group1Child"]) {
							$gounpCntName = "Group1Cnt";
							$currCnt = $row7["Group1Cnt"];
						}
						else if ($currUid == $row7["Group2Child"]) {
							$gounpCntName = "Group2Cnt";
							$currCnt = $row7["Group2Cnt"];
						}
						else if ($currUid == $row7["Group3Child"]) {
							$gounpCntName = "Group3Cnt";
							$currCnt = $row7["Group3Cnt"];
						}
						$currCnt += 1;
						$res8 = mysql_query("update User set $gounpCntName='$currCnt' where UserId='$parentId'");
						if (!$res8) {
							// !!! log
						}
						
						$currUid = $parentId;
						$parentId = $row7["ParentId"];
					}
				}
			}
			*/
		}
		else {
			echo json_encode(array('error'=>'true','error_code'=>'7','error_msg'=>'账号已经注册过了！','sql_error'=>mysql_error()));
			return;
		}
	}
		
	if (0 != $newuserid) {
		$result = mysql_query("select * from Credit where UserId='$newuserid'");
		if (!$result) {
			// !!! log
// 			echo json_encode(array('error'=>'true','error_code'=>'8','error_msg'=>'积分查询出错，请稍后重试！','sql_error'=>mysql_error()));
// 			return;
		}
		else {
			$num = mysql_num_rows($result);
			if ($num == 0) {
				$vault = 0;
				$dynVault = $dyNewUserVault;
				$result = mysql_query("insert into Credit (UserId, Vault, DVault)
					VALUES('$newuserid', '$vault', '$dynVault')");
				if (!$result) {
					// !!! log
// 					echo json_encode(array('error'=>'true','error_code'=>'9','error_msg'=>'新用户积分表插入失败，请稍后重试！','sql_error'=>mysql_error()));
// 					return;
				}
				else {
// 					echo "Register success<br />";
				}
			}					
			else {
				// !!! log
			}
		}
	}
	
	echo json_encode(array('error'=>'false', 'new_user_id'=>$newuserid));
	
	// 添加初始订单
	$res2 = mysql_query("insert into Transaction (UserId, ProductId, Price, Count, OrderTime, Status)
					VALUES('$newuserid', '1', '$refererConsumePoint', '3', '$now', '$OrderStatusDefault') ");
	$bInsertOrder = $res2 != false;
	
	// 更新推荐人credit，添加消耗记录,若失败不影响返回结果
	$leftCredit = $credit - $refererConsumePoint;
	mysql_query("update Credit set Credits='$leftCredit' where UserId='$userid'");
	// 修改用户的积分纪录，若失败不影响返回结果
	$result = mysql_query("insert into CreditRecord (UserId, Amount, CurrAmount, ApplyTime, AcceptTime, WithUserId, Type)
								VALUES($userid, $refererConsumePoint, $leftCredit, $now, $now, $newuserid, $codeRecommend)");
	// 更新推荐人的推荐人数，若失败不影响返回结果
	$result = mysql_query("select * from User where UserId='$userid'");
	if (!$result) {
	}
	else {
		$row = mysql_fetch_assoc($result);
		$count = $row["RecoCnt"];
		$lvl = $row["Lvl"];
		$count += 1;
		if ($lvl <= 1) {
			if ($count >= 3) {
				$lvl = 2;
			}
		}
		mysql_query("update User set RecoCnt='$count', Lvl='$lvl' where UserId='$userid'");
	}

	// 给上游用户分成	
	$referBonus = distributeReferBonus($con, $newuserid, 1);
	
	// 更新统计数据,在订单统计里返还积分到积分池，而在推荐统计里不做不回积分池，只增加推荐消耗积分总额及用户人数
	$bStaticBefore = false;
	insertOrderStatistics($refererConsumePoint, 1, $referBonus);
	insertRecommendStatistics($refererConsumePoint, $bStaticBefore);

	mysql_close($con);
	return;
}

?>