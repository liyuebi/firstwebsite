<?php 
	
function connectToDB()
{
	$con = mysql_connect("127.0.0.1:3306", "root", "123456789");
	if (!$con)
	{
// 		die("Could not connect: " . mysql_error());
		echo "Could not connect: " . mysql_error();
	}
	return $con;
}
	
function createUserTable()
{
	$sql = "create table if not exists User
	(
		UserId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(UserId),
		PhoneNum varchar(15) NOT NULL,
		Name varchar(15) DEFAULT '',
		IDNum varchar(18) DEFAULT '',
		Password varchar(12) NOT NULL,
		PayPwd varchar(12) DEFAULT '',
		ReferreeId int DEFAULT 0,
		RecommendingCount int DEFAULT 0,
		RegisterTime int NOT NULL,
		LastLoginTime int DEFAULT 0,
		LastPayPwdModifyTime int DEFAULT 0,
		DefaultAddressId int DEFAULT 0
	) ENGINE=MEMORY AUTO_INCREMENT=100000 DEFAULT CHARSET=utf8 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC";
	$result = mysql_query($sql);
	if (!$result) {
		echo "create User table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createCreditTable()
{
	/*
	 * TotalRRwd: 总订单分成
	 * TotalBonus: 总分红
	 * LastCBTime: last collect bonus time 上次收获分红的时间
	 */
	$sql = "create table if not exists Credit
	(
		UserId int NOT NULL,
		PRIMARY KEY(UserId),
		Credits int DEFAULT 0,
		Vault int DEFAULT 0,
		DynamicVault int DEFAULT 0,
		TotalRecharge int DEFAULT 0,
		TotalWithdraw int DEFAULT 0,
		TotalConsumption int DEFAULT 0,
		TotalFee int DEFAULT 0,
		TotalRRwd int DEFAULT 0,
		TotalBonus int DEFAULT 0,
		MonthRecharge int DEFAULT 0,
		MonthWithdraw int DEFAULT 0,
		MonthConsumption int DEFAULT 0,
		DayRecharge int DEFAULT 0,
		DayWithdraw int DEFAULT 0,
		DayConsumption int DEFAULT 0,
		LastRechargeTime int DEFAULT 0,
		LastWithdrawTime int DEFAULT 0,
		LastConsumptionTime int DEFAULT 0,
		DayObtained int DEFAULT 0,
		LastObtainedTime int DEFAULT 0,
		CurrBonus int DEFAULT 0,
		LastCBTime int DEFAULT 0
	)";
	$result = mysql_query($sql);
	if (!$result) {
		echo "create User table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createAdminTable()
{
	$sql = "create table if not exists Admin
	(
		AdminId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(AdminId),
		Name varchar(30),
		Password varchar(12),
		Priority int
	)";
	$result = mysql_query($sql);
	if (!$result) {
		echo "create Admin table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createAddressTable()
{
	$sql = "create table if not exists Address
	(
		AddressId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(AddressId),
		UserId int,
		Receiver varchar(30) NOT NULL,
		PhoneNum varchar(15) NOT NULL,
		Address varchar(128) NOT NULL,
		ZipCode varchar(8) DEFAULT ''
	)";
	$result = mysql_query($sql);
	if (!$result) {
		echo "create Address table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createTranscationTable()
{
	$sql = "create table if not exists Transcation
	(
		OrderId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(OrderId),
		UserId int NOT NULL,
		ProductId int NOT NULL,
		Price int NOT NULL,
		Count int NOT NULL,
		Receiver varchar(30) DEFAULT '',
		PhoneNum varchar(15) DEFAULT '',
		Address varchar(128) DEFAULT '',
		ZipCode varchar(12) DEFAULT '',
		OrderTime int NOT NULL,
		DeliveryTime int DEFAULT 0,
		CompleteTime int DEFAULT 0,
		Status int
	)";

	$result = mysql_query($sql);
	if (!$result) {
		echo "create Transcation table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createCompanyTable()
{
	$sql = "create table if not exists Company
	(
		CompanyId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(CompanyId),
		Contacter varchar(30),
		PhoneNum varchar(15)
	)";
	
	$result = mysql_query($sql);
	if (!$result) {
		echo "create Company table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createProductTable()
{
	$sql = "create table if not exists Product
	(
		ProductId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(ProductId),
		CompanyId int,
		Price int NOT NULL,
		ProductName varchar(30),
		ProductDesc varchar(128),
		FirstImg varchar(128) DEFAULT '',
		SecondImg varchar(128) DEFAULT '',
		ExhibitImg varchar(128) DEFAULT '',
		AddTime int NOT NULL,
		LastModifyTime int DEFAULT 0,
		OffTime int DEFAULT 0, 
		LimitOneDay int DEFAULT 0,
		Status int NOT NULL DEFAULT 1
	)";
	$result = mysql_query($sql);
	if (!$result) {
		echo "create Product table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createProductDayBoughtTable()
{
	$sql = "create table if not exists ProductDayBought
	(
		IndexId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(IndexId),
		UserId int NOT NULL,
		ProductId int NOT NULL,
		LastBoughtTime int DEFAULT 0,
		Count int DEFAULT 0
	)";
	$result = mysql_query($sql);
	if (!$result) {
		echo "create ProductDayBought table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createRechgeTable()
{
	$sql = "create table if not exists RechargeApplication
	(
		IndexId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(IndexId),
		UserId int NOT NULL,
		Amount int NOT NULL,
		ApplyTime int NOT NULL,
		AcceptTime int DEFAULT 0,
		DeclineTime int DEFAULT 0,
		AdminId int DEFAULT 0
	)";
	$result = mysql_query($sql);
	if (!$result) {
		echo "create RechargeApplication table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createWithdrawTable()
{
	$sql = "create table if not exists WithdrawApplication
	(
		IndexId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(IndexId),
		UserId int NOT NULL,
		ApplyAmount int NOT NULL,
		ActualAmount int NOT NULL,
		ApplyTime int NOT NULL,
		AcceptTime int DEFAULT 0,
		DeclineTime int DEFAULT 0,
		AdminId int DEFAULT 0
	)";	
	$result = mysql_query($sql);
	if (!$result) {
		echo "create WithdrawApplication table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createCreditRecordTable()
{
	$sql = "create table if not exists CreditRecord
	(
		IndexId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(IndexId),
		UserId int NOT NULL,
		Amount int NOT NULL,
		CurrAmount int NOT NULL,
		HandleFee int DEFAULT 0,
		ApplyTime int NOT NULL,
		ApplyIndexId int DEFAULT 0,
		AcceptTime int DEFAULT 0,
		WithUserId int DEFAULT 0,
		Type int NOT NULL
	)";	
	$result = mysql_query($sql);
	if (!$result) {
		echo "create CreditRecord table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createStatisticsTable()
{
	/*
	 * Ye 	
	 * Mon
	 * Day 
	 * NSCount  - New User Count 新用户总数
	 * RRTotal  - Recommend Reward Total 返利总计，卖出的产品给上游用户的返利总计
	 * OrderNum - 成交的订单数量
	 * SPNum	- sell product num 卖出的产品数量，是每笔订单的产品数的总和
	 * TfTimes  - Transfer Times 转账次数
	 * TfFee	- Transfer Fee 转账手续费
	 * TfTotal	- Transfer Total 总的转账额度
	*/
	$sql = "create table if not exists Statistics
	(
		IndexId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(IndexId),
		Ye int NOT NULL,
		Mon int NOT NULL,
		Day int NOT NULL,
		RechargeTotal int DEFAULT 0,
		WithdrawTotal int DEFAULT 0,
		NSCount int DEFAULT 0,
		RRTotal int DEFAULT 0,
		BonusTotal int DEFAULT 0,
		RecommendFee int DEFAULT 0,
		WithdrawFee int DEFAULT 0,
		OrderGross int DEFAULT 0,
		OrderNum int DEFAULT 0,
		SPNum int DEFAULT 0,
		TfTimes int DEFAULT 0,
		TfFee int DEFAULT 0,
		TfTotal int DEFAULT 0
	)";
	$result = mysql_query($sql);
	if (!$result) {
		echo "create Statistics table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createTotalStatisTable()
{
	$sql = "create table if not exists TotalStatis
	(
		IndexId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(IndexId),
		CreditsPool	int DEFAULT 50000000,
		UserCount int DEFAULT 0,
		StaUserCount int DEFAULT 0,
		DyaUserCount int DEFAULT 0,
		BannedUserCount int DEFAULT 0,
		RechargeTotal int DEFAULT 0,
		WithdrawTotal int DEFAULT 0,
		TransferTotal int DEFAULT 0,
		WithdrawFee int DEFAULT 0,
		TransferFee int DEFAULT 0,
		RechargeTimes int DEFAULT 0,
		WithdrawTimes int DEFAULT 0,
		TransferTimes int DEFAULT 0,
		RecommendTotal int DEFAULT 0,
		RRTotal int DEFAULt 0,
		BonusTotal int DEFAULT 0,
		OrderGross int DEFAULT 0,
		OrderNum int DEFAULT 0,
		SPNum int DEFAULT 0
	)";
	$result = mysql_query($sql);
	if (!$result) {
		echo "create TotalStatis table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function createShortStatisTable()
{
	$sql = "create table if not exists ShortStatis
	(
		IndexId int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(IndexId),
		Recharge int DEFAULT 0,
		Withdraw int DEFAULT 0,
		Transfer int DEFAULT 0,
		OrderGross int DEFAULT 0,
		WithdrawFee int DEFAULT 0,
		TransferFee int DEFAULT 0,
		BonusTotal int DEFAULT 0,
		BonusLeft int DEFAULT 0,
		StaUserCount int DEFAULT 0,
		DynUserCount int DEFAULT 0,
		BonusPerSta int DEFAULT 0,
		BonusPerDya int DEFAULT 0,
		StaUserObtained int DEFAULT 0,
		DyaUserObtained int DEFAULT 0,
		LastCalcTime int DEFAULT 0
	)";
	$result = mysql_query($sql);
	if (!$result) {
		echo "create ShortStatis table error: " . mysql_error() . "<br>";
	}
	return $result;
}

function isInTheSameDay($time1, $time2)
{
	date_default_timezone_set('PRC');		// get local time
	$year1 = date("Y", $time1);
	$month1 = date("m", $time1);
	$day1 = date("d", $time1);

	$year2 = date("Y", $time2);
	$month2 = date("m", $time2);
	$day2 = date("d", $time2);
	
	return $year1 == $year2 && $month1 == $month2 and $day1 == $day2;
}

function isInTheSameMonth($time1, $time2)
{
	date_default_timezone_set('PRC');		// get local time
	$year1 = date("Y", $time1);
	$month1 = date("m", $time1);

	$year2 = date("Y", $time2);
	$month2 = date("m", $time2);
	
	return $year1 == $year2 && $month1 == $month2;
}

function isInTheSameYear($time1, $time2)
{
	date_default_timezone_set('PRC');		// get local time
	$year1 = date("Y", $time1);
	$year2 = date("Y", $time2);
	
	return $year1 == $year2;
}

function getMonthConsumption($userid)
{
	$ret = 0;
	$result = mysql_query("select * from Credit where UserId='$userid'");
	if (!$result && mysql_num_rows($result) <= 0) {
		return;
	}
	
	//  如果值为0，就设为零
	$row = mysql_fetch_assoc($result);
	if ($row["MonthConsumption"] == 0) {
		return 0;
	}

	$lasttime = $row["LastConsumptionTime"];
	if ($lasttime == 0) {
		return 0;
	}
	
	// set DayConsumption to 0 if LastConsumptionTime is not today
	if (!isInTheSameMonth($lasttime, time())) {
		mysql_query("update Credit set MonthConsumption=0 where UserId='$userid'");
		return 0;
	}
	
	return $row["MonthConsumption"];

}

function updateMonthConsumption($userid)
{
	$ret = 0;
	$result = mysql_query("select * from Credit where UserId='$userid'");
	if (!$result && mysql_num_rows($result) <= 0) {
		return;
	}

	$row = mysql_fetch_assoc($result);
	$lasttime = $row["LastConsumptionTime"];
	if ($lasttime == 0) {
		mysql_query("update Credit set MonthConsumption='$consumption' and LastConsumptionTime='$time' where UserId='$userid'");
		return;
	}	

	$dayConsumption = $row["MonthConsumption"];
	if (!isInTheSameMonth($lasttime, $time)) {
		$dayConsumption = 0;
	}
	
	$dayConsumption += $consumption;
	mysql_query("update Credit set isInTheSameMonth='$dayConsumption' and LastConsumptionTime='$time' where UserId='$userid'");
	return;

}

function getDayConsumption($userid)
{
	$ret = 0;
	$result = mysql_query("select * from Credit where UserId='$userid'");
	if (!$result && mysql_num_rows($result) <= 0) {
		return;
	}
	
	//  如果值为0，就设为零
	$row = mysql_fetch_assoc($result);
	if ($row["DayConsumption"] == 0) {
		return 0;
	}

	$lasttime = $row["LastConsumptionTime"];
	if ($lasttime == 0) {
		return 0;
	}
	
	// set DayConsumption to 0 if LastConsumptionTime is not today
	if (!isInTheSameDay($lasttime, time())) {
		mysql_query("update Credit set DayConsumption=0 where UserId='$userid'");
		return 0;
	}
	
	return $row["DayConsumption"];
}

function updateDayConsumption($userid, $consumption, $time) 
{
	$ret = 0;
	$result = mysql_query("select * from Credit where UserId='$userid'");
	if (!$result && mysql_num_rows($result) <= 0) {
		return;
	}

	$row = mysql_fetch_assoc($result);
	$lasttime = $row["LastConsumptionTime"];
	if ($lasttime == 0) {
		mysql_query("update Credit set DayConsumption='$consumption' and LastConsumptionTime='$time' where UserId='$userid'");
		return;
	}	

	$dayConsumption = $row["DayConsumption"];
	if (!isInTheSameDay($lasttime, $time)) {
		$dayConsumption = 0;
	}
	
	$dayConsumption += $consumption;
	mysql_query("update Credit set DayConsumption='$dayConsumption' and LastConsumptionTime='$time' where UserId='$userid'");
	return;
}

function getDayObtained($userid)
{
	$ret = 0;
	$result = mysql_query("select * from Credit where UserId='$userid'");
	if (!$result && mysql_num_rows($result) <= 0) {
		return;
	}
	
	//  如果值为0，就设为零
	$row = mysql_fetch_assoc($result);
	if ($row["DayObtained"] == 0) {
		return 0;
	}

	$lasttime = $row["LastObtainedTime"];
	if ($lasttime == 0) {
		return 0;
	}
	
	// set DayConsumption to 0 if LastConsumptionTime is not today
	if (!isInTheSameDay($lasttime, time())) {
		mysql_query("update Credit set DayConsumption=0 where UserId='$userid'");
		return 0;
	}
	
	return $row["DayObtained"];
}

function updateDayObtained($userid, $obtained, $time)
{
	$ret = 0;
	$result = mysql_query("select * from Credit where UserId='$userid'");
	if (!$result && mysql_num_rows($result) <= 0) {
		return;
	}

	$row = mysql_fetch_assoc($result);
	$lasttime = $row["LastObtainedTime"];
	if ($lasttime == 0) {
		mysql_query("update Credit set DayObtained='$obtained' and LastObtainedTime='$time' where UserId='$userid'");
		return;
	}	

	$dayObtained = $row["DayObtained"];
	if (!isInTheSameDay($lasttime, $time)) {
		$dayObtained = 0;
	}
	
	$dayObtained += $obtained;
	mysql_query("update Credit set DayConsumption='$dayObtained' and LastObtainedTime='$time' where UserId='$userid'");
	return;
}

function calcHandleFee($amount, $rate) {
	$fee = floor($amount * $rate);
	return $fee;
}

function getDayBoughtCount($userid, $productid)
{
	$count = 0;
	$result = mysql_query("select * from ProductDayBought where UserId='$userid' and ProductId='$productid'");
	if ($result && mysql_num_rows($result) > 0) {
		
		$row = mysql_fetch_assoc($result);
		$lTime = $row["LastBoughtTime"];
		$now = time();
		if (isInTheSameDay($lTime, $now)) {
			$count = $row["Count"];
		}
	}
	
	return $count;
}

function updateDayBoughtCount($userid, $productid, $count) 
{
	$result = createProductDayBoughtTable();
	if (!$result) {
		return;
	}
	
	$result = mysql_query("select * from ProductDayBought where UserId='$userid' and ProductId='$productid'");
	if ($result) {
		
		$now = time();
		if (mysql_num_rows($result) > 0) {
		
			$row = mysql_fetch_assoc($result);
			$lTime = $row["LastBoughtTime"];
			if (isInTheSameDay($lTime, $now)) {
				$count += $row["Count"];
			}
			
			mysql_query("update ProductDayBought set Count='$count', LastBoughtTime='$now' where UserId='$userid' and ProductId='$productid'");
		}
		else {
			mysql_query("insert into ProductDayBought (UserId, ProductId, Count, LastBoughtTime)
							VALUES('$userid', '$productid', '$count', '$now')");
		}
	}
}

function initGeneralStatisTable()
{
	$result = createTotalStatisTable();
	if ($result) {
		
		$res = mysql_query("select * from TotalStatis");
		if (!res) {
			echo "init general statis error: " . mysql_error() . "<br>";
		}
		else {
			if (mysql_num_rows($res) > 0) {
				// inited
			}
			else {
				$res1 = mysql_query("insert into TotalStatis (CreditsPool) VALUES('50000000')");
				if (!res1) {
					echo "insert into general statis error: " . mysql_error() . "<br>";
				}
			}
		}
	}
	
	$result = createShortStatisTable();
	if ($result) {
				$res = mysql_query("select * from ShortStatis");
		if (!res) {
			echo "init short statis error: " . mysql_error() . "<br>";
		}
		else {
			if (mysql_num_rows($res) > 0) {
				// inited
			}
			else {
				$res1 = mysql_query("insert into ShortStatis (LastCalcTime) VALUES('0')");
				if (!res1) {
					echo "insert into short statis error: " . mysql_error() . "<br>";
				}
			}
		}
	}
}




?>