<?php

//  给上游玩家分享注册资金
function distributeReferBonus($con, $userid, $count)
{
	$ret = 0;
	// 没有有效的数据库连接，返回
	if (!$con) {
		return $ret;
	}
	
	{
		$res1 = mysql_query("select * from User where UserId='$userid'");
		if ($res1 && mysql_num_rows($res1) > 0) {
			$row1 = mysql_fetch_assoc($res1);
			$referId = $row1["ReferreeId"];	// 推荐人
			$refeeId = $userid; // 被推荐人
			$idx = 1;
			
			$id2 = $referId;
			while($idx <= 13) {
				
				// 没有上游用户了,则退出
				if ($id2 == "0") {
					break;
				}
		
				$recommendCount = 1;
				$id3 = 0;
				// 得到推荐人的推荐人数以及他的推荐人
				$res3 = mysql_query("select * from User where UserId='$id2'");
				if ($res3 && mysql_num_rows($res3) > 0) {
					$row3 = mysql_fetch_assoc($res3);
					
					$id3 = $row3["ReferreeId"];
					$recommendCount = $row3["RecommendingCount"];
				}
				
				$res2 = mysql_query("select * from Credit where UserId='$id2'");
				if ($res2 && mysql_num_rows($res2) > 0) {
					$row2 = mysql_fetch_assoc($res2);
					$val1 = $row2["Credits"];
					$val2 = $row2["DayObtained"];
					$val3 = $row2["LastObtainedTime"];
					
					$add = 0;
					if ($idx >= 1 && $idx <= 3) {	// 1 到 3 层
						// 推荐了人即可拿到
						$add = 4;
					}
					else if ($idx >= 5 && $idx <= 8) { // 5 到 8 层
						// 推荐人数大于5即可拿到
						if ($recommendCount > 5) {
							$add = 3;
						}
					}
					else if ($idx >= 9 && $idx <= 13) { // 9 到 13 层
						// 推荐人数大于9即可拿到
						if ($recommendCount > 9) {
							$add = 2;
						}
					}
					$add *= $count;	// 分成是根据盒数确定
					
					if ($add > 0) {
						// must be a dynamic user
						$val4 = $row2["Vault"] + $row2["DynamicVault"];
					
						if ($val4 < $add) {
							$add = $val4;
							$val4 = 0;
						}
						else {
							$val4 -= $add;
						}
						
						if ($add > 0) {
							$time = time();
							if (isInTheSameDay($val3, $time)) {
								$val2 += $add;
							}
							else {
								$val2 = $add;
							}
							$val1 += $add;
		
							mysql_query("update Credit set Credits='$val1', Vault='$val4', DynamicVault='0', DayObtained='$val2', LastObtainedTime='$time'  where UserId='$id2'");

							include "constant.php";
							mysql_query("insert into CreditRecord (UserId, Amount, CurrAmount, ApplyTime, ApplyIndexId, Type, AcceptTime, WithUserId)
											VALUES('$id2', '$add', '$val1', '$time', '0', '$codeBonus', '$time', '$refeeId')");
							$ret += $add;											
						}
					}
				}
				
				if ($id3 == 0) {
					break;
				}
				else {
					$id2 = $id3;
				}
				
				++$idx;
			}
		}
	}
	return $ret;
}

function isValidAddress($receiver, $phone, $address, &$error_str)
{
	if ($receiver == '') {
		$error_str = '输入的姓名无效！';
		return false;
	}
	
	if ($address == '') {
		$error_str = '输入的地址无效！';
		return false;
	}
	
	include_once "regtest.php";
	if (!isValidCellPhoneNum($phone)) {
		$error_str = '输入的电话号码有误，请重新输入！';
		return false;
	}
	
	return true;
}

function addOneAddress($con, $userid, $receiver, $phone, $address, $isDefault, &$error_str)
{	
	$result = createAddressTable();
	if (!$result) {
		$error_str = "创建地址表失败，请稍后重试！";
		return false;
	}
	else {
		$result = mysql_query("insert into Address (UserId, Receiver, PhoneNum, Address)
		 	VALUES('$userid', '$receiver', '$phone', '$address')");
		if (!$result) {
			$error_str = "插入地址失败，请稍后重试！";
			return false;
		}
		else {
			// 更新默认地址,出错了不做处理
			if ($isDefault) {
				$addId = mysql_insert_id();
				mysql_query("update User set DefaultAddressId='$addId' where UserId='$userid'");
			}
		}
	}
	return true;
}
	
?>