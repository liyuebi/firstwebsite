<?php

function isValidNum($str)
{
	return preg_match("/^\d+$/", $str);
}

function isVaildDecimal($str)
{
	return preg_match("/^[0-9]+(.[0-9]{1,2})?$/", $str);
}

function isValidCellPhoneNum($str)
{
	return preg_match("/^1\d{10}$/", $str);
}

function isValidIdNum($str)
{
	return preg_match("/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/", $str);
}

function isValidZipCode($str)
{
	
}

function isValidLoginPwd($str)
{
	return preg_match("/^[a-zA-Z0-9]{6,12}$/", $str);	
}

function isValidPayPwd($str)
{
	return preg_match("/^[a-zA-Z0-9]{6,12}$/", $str);
}

function isValidMoneyAmount($str)
{
	return preg_match("/^[1-9]\d*$/", $str);
}

function isValidUserName($str)
{
	return preg_match("/^[a-zA-Z0-9]{4,12}$/", $str);
}
	
?>