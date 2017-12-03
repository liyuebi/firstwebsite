<?php

include "database.php";

$con = mysql_connect("127.0.0.1:3306", "root", "123456789");
if (!$con)
{
	echo "Could not connect: " . mysql_error();
	return; 
}

if (mysql_query("create database mifeng_db", $con)) {
	echo "Database created";
	echo "<br>";
}
else {
	echo "Error creating database: " . mysql_error();
	echo "<br>";
}

$db_selected = mysql_select_db("mifeng_db", $con);
if (!$db_selected) {
	echo "select db failed: " , mysql_error();
	return;
}

$now = time();

// create client table and credit talbe and root user account
createClientTable();
createCreditTable();

$pwd = md5('000000');
$pwd = password_hash($pwd, PASSWORD_DEFAULT);
$time = time();
if (mysql_query("insert into ClientTable (UserId, PhoneNum, NickName, Password, RegisterTime)
					values('10000', '13812345678', 'peter', '$pwd', '$time')"))
{
	echo "root user created";
	echo "<br>";	
	
	if (mysql_query("insert into Credit (UserId, Credits)
			values(10000, 10000)"))
	{
		echo "root user credit created";
		echo "<br>";		
	}
	else {
		echo "Error creating root user credit: " . mysql_error();
		echo "<br>";
	}
}
else {
	echo "Error creating root user: " . mysql_error();
	echo "<br>";
}

// create admin table and default admin account
createAdminTable();
$pwd = md5("super_admin");
$pwd = password_hash($pwd, PASSWORD_DEFAULT);
if (mysql_query("insert into AdminTable (Name, Password, Priority)
					values('admin', '$pwd', '10')")) {
	echo "Default admin created";
	echo "<br>";	
}
else {
	echo "Error creating default admin: " . mysql_error();
	echo "<br>";
}
$pwd = md5('000000');
$pwd = password_hash($pwd, PASSWORD_DEFAULT);
mysql_query("insert into AdminTable (Name, Password, Priority)
					values('xieshuqian', '$pwd', '1')");

createCreditRecordTable();
createPntsRecordTable();

createCreditTradeTable();
createCreditBankTable();
createTransactionTable();
createOfflineShopTable();

createComplaintTable();

createStatisticsTable();
initGeneralStatisTable();

// 创建产品表，推入第一个产品
/*
$ret = createProductTable();
if ($ret) {
	$res = mysql_query("insert into Product (Price, ProductName, ProductDesc, AddTime, FirstImg)
							values('300', '茗菊春皇菊', '源自天然，传播健康', '$now', '2.jpg')");
	if (!$res) {
		echo "insert default product failed: " . mysql_error();
		echo "<br>";
	}
}
*/

?>