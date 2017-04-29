
/*
jQuery(document).ready(function() {

    $('.page-container form').submit(function(){
        var username = $(this).find('.username').val();
        var password = $(this).find('.password').val();
        if(username == '') {
            $(this).find('.error').fadeOut('fast', function(){
                $(this).css('top', '27px');
            });
            $(this).find('.error').fadeIn('fast', function(){
                $(this).parent().find('.username').focus();
            });
            return false;
        }
		
        if(password == '') {
            $(this).find('.error').fadeOut('fast', function(){
                $(this).css('top', '96px');
            });
            $(this).find('.error').fadeIn('fast', function(){
                $(this).parent().find('.password').focus();
            });
            return false;
        }
    });

    $('.page-container form .username, .page-container form .password').keyup(function(){
        $(this).parent().find('.error').fadeOut('fast');
    });

});
*/

function logout()
{
	$.post("../php/login.php", {"func":"logout"}, function(data){
		location.href="../index.html";
	});
}


function setCookie(name, value, expireHours)
{
	if (0 == value.length)
		return;
		
	var cookieStr = name + "=" + escape(value);
	
	if (0 < expireHours)
	{
		var date = new Date();
		date.setTime(date.getTime() + expireHours * 3600 * 1000);
		cookieStr = cookieStr + ";expire" + date.toGMTString();
	}
	
	document.cookie = cookieStr;
}

function getCookie(name)
{
	if (document.cookie.length>0) {
		var c_start = document.cookie.indexOf(name + "=");
		if (-1 != c_start) {
			c_start = c_start + name.length + 1;
			var c_end = document.cookie.indexOf(";", c_start);
			if (-1 == c_end) {
				c_end = document.cookie.length;
			}
			return unescape(document.cookie.substring(c_start, c_end));
		}
		return "";
	}
	return "";
}

function deleteCookie(name)
{
	var date = new Date();
	date.setTime(date.getTime() - 10000);
	document.cookie = name + "=invalid;expire=" + date.toGMTString();
}

function isLogined() 
{
/*
	var cookie = getCookie("User");
	if (0 >= cookie.length)	
		return false;
	
	var data = 'user=' + cookie;
	$.getJSON("isLogined.php", data, function(json){
		if (json.isLogin == "true")
			return true;
		else  
			return false;
	});
*/
	var cookie = getCookie("isLogin");
	if (0 >= cookie.length)
		return false;
		
	return "true" == cookie;
}

function isNotLoginAndJump()
{
	if (!isLogined()) {
		location.href = "../index.html";
		return true;
	}
	return false;
}

function onlyNumber(e)
{
	var keynum;
	var keychar;
	var numcheck;
	
	if (window.event) {
		keynum = e.keyCode;
	}
	else if (e.which) {
		keynum = e.which;
	}
	
	keychar = String.fromCharCode(keynum);
	numcheck = /\d/;
	var v = numcheck.test(keychar);
	return v;
}

function onlyCharAndNum(e)
{
	var keynum;
	var keychar;
	var check = /[a-zA-Z0-9]/;
	
	if (window.event) {
		keynum = e.keyCode;
	}
	else if (e.which) {
		keynum = e.which;
	}
	keychar = String.fromCharCode(keynum);
	return check.test(keychar);
}

function noNumbers(e)
{
	var keynum;
	var keychar;
	var numcheck;
	
	if (window.event) {
		keynum = e.keyCode;
	}
	else if (e.which) {
		keynum = e.which;
	}
	
	keychar = String.fromCharCode(keynum);
	numcheck = /\d/;
	return !numcheck.test(keychar);
}

function isPhoneNumValid(str)
{
	var phoneNumReg = /^1\d{10}$/;
	var val = phoneNumReg.test(str);
	return val;
}

function isZipCodeValid(str)
{
	var zipcodeReg = /d{6}/;
	return zipcodeReg.test(str);
}

function isIDNumValid(str)
{
	// 身份证可能为15位或18位，18位时最后一位可能为数字、x或X
	var idNumReg = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;
	return idNumReg.test(str);
}

function isPayPwdValid(str)
{
	var payPwdReg = /[a-zA-Z0-9]{6,12}/;
	return payPwdReg.test(str);	
}

function isValidNum(str)
{
	var numReg = /^\d+$/;
	return numReg.test(str);
}




