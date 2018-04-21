<?php

include "database.php";

$con = mysqli_connect("127.0.0.1:3306", "root", "123456789");
if (!$con)
{
	echo "Could not connect: " . mysqli_connect_error();
	return; 
}

if (mysqli_query($con, "create database mifeng_db")) {
	echo "Database created";
	echo "<br>";
}
else {
	echo "Error creating database: " . mysqli_error($con);
	echo "<br>";
}

$db_selected = mysqli_select_db($con, "mifeng_db");
if (!$db_selected) {
	echo "select db failed: " , mysqli_error($con);
	return;
}

$now = time();

// create client table and credit talbe and root user account
createClientTable($con);
createCreditTable($con);

$pwd = md5('000000');
$pwd = password_hash($pwd, PASSWORD_DEFAULT);
$time = time();
if (mysqli_query($con, "insert into ClientTable (UserId, PhoneNum, NickName, Password, RegisterTime)
							values('10000', '13812345678', 'peter', '$pwd', '$time')"))
{
	echo "root user created";
	echo "<br>";	
	
	if (mysqli_query("insert into Credit (UserId, Credits)
			values(10000, 10000)"))
	{
		echo "root user credit created";
		echo "<br>";		
	}
	else {
		echo "Error creating root user credit: " . mysqli_error($con);
		echo "<br>";
	}
}
else {
	echo "Error creating root user: " . mysqli_error($con);
	echo "<br>";
}

// create admin table and default admin account
createAdminTable($con);
$pwd = md5("super_admin");
$pwd = password_hash($pwd, PASSWORD_DEFAULT);
if (mysqli_query($con, "insert into AdminTable (Name, Password, Priority)
					values('admin', '$pwd', '10')")) {
	echo "Default admin created";
	echo "<br>";	
}
else {
	echo "Error creating default admin: " . mysqli_error($con);
	echo "<br>";
}
$pwd = md5('000000');
$pwd = password_hash($pwd, PASSWORD_DEFAULT);
mysqli_query($con, "insert into AdminTable (Name, Password, Priority)
					values('xieshuqian', '$pwd', '1')");

createCreditRecordTable($con);
createPntsRecordTable($con);

createCreditTradeTable($con);
createCreditBankTable($con);
createTransactionTable($con);
createOfflineShopTable($con);
createProductPackTable($con);

createComplaintTable($con);

createStatisticsTable($con);
initGeneralStatisTable($con);

// 创建产品表，推入第一个产品
/*
$ret = createProductTable($con);
if ($ret) {
	$res = mysqli_query($con, "insert into Product (Price, ProductName, ProductDesc, AddTime, FirstImg)
							values('300', '茗菊春皇菊', '源自天然，传播健康', '$now', '2.jpg')");
	if (!$res) {
		echo "insert default product failed: " . mysqli_error($con);
		echo "<br>";
	}
}
*/

?>