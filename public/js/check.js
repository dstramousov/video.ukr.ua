function checkPassword(strng) {
    var error = "";
    if (strng == "") {
        error = "Введите пароль.\n";
    }

    var illegalChars = /[\W_]/; // allow only letters and numbers
    if ((strng.length < 6) || (strng.length > 8)) {
        error = "Неправильная длина пароля.\n";
    }
    else if (illegalChars.test(strng)) {
        error = "Недопустимые символы.\n";
    } else if (!((strng.search(/[a-z]+/) > -1)
                && (strng.search(/[A-Z]+/) > -1)
                && (strng.search(/[0-9]+/) > -1))) {
                    error = "The password must contain at least one uppercase letter, one lowercase letter, and one numeral.\n";
                }


    return error;
}

function getYear(d){ return (d < 1000) ? d + 1900 : d; }
function isDate (year, month, day) {
//    alert(year + month + day);
  //  month = month - 1; // javascript month range : 0- 11
    var tempDate = new Date(year,month,day);
    if ( (getYear(tempDate.getYear()) == year) &&
    (month == tempDate.getMonth()) &&
    (day == tempDate.getDate()) )
    return true;
    else
    return false
}


function checkUserParams(errors){

    var msg = validateUsername($('nickname'), errors);
    if(msg != ''){
        $('div_nickname').innerHTML = msg;
        init_toggle('nickname');
        $('nickname').focus();
        return false;
    } else {
        $('div_nickname').innerHTML = '';
    }

    var res = check_email($('email').value);
    if(!res){
        $('email').focus();
        $('email_div').innerHTML = errors.incorrectEmail;
        init_toggle('email');
        return false;
    } else { 
        $('email_div').innerHTML = ''; 
    }

    pass    = $('reg_password_id').value.length;
    cpass   = $('cpassword').value.length;

    if(pass < 6)        {  $('reg_password_id').focus(); init_toggle('reg_password_id'); $('div_password').innerHTML = errors.incorrectPassLen; return false; } else { $('div_password').innerHTML = '';}
    if(cpass < 6)       {  $('cpassword').focus(); init_toggle('cpassword'); $('div_cpassword').innerHTML = errors.incorrectPassLen; return false; } else { $('div_cpassword').innerHTML = '';}
    if(pass != cpass)   {  $('cpassword').focus(); $('div_cpassword').innerHTML = errors.incorrectPassDifferent;  return false; } else { $('div_password').innerHTML = ''; $('div_cpassword').innerHTML = ''}

    if (isDate($('idbyear').value,$('idbmonth').value,$('idbday').value)){
        var myDate=new Date();
        myDate.setFullYear($('idbyear').value,$('idbmonth').value,$('idbday').value);
        var input = document.createElement("input");
        input.setAttribute("type", "hidden");
        input.setAttribute("name", "birthday");
        input.setAttribute("value", myDate);
        document.getElementById("register-form").appendChild(input);
        $('div_birtday').innerHTML = '';
    } else {
        $('div_birtday').innerHTML = errors.incorrectBirthDate;
        return false;
    }

    if($('captcha')!=null){

	    if($('captcha').value == ''){
	        $('captcha').focus(); 
	        $('div_captcha').innerHTML = errors.incorrectCaptcha;
	        init_toggle('captcha');
	        return false;
	    } else {
	        $('div_captcha').innerHTML = '';
	    }
    
	    if(!$('agr').checked){
	        $('agr_div').innerHTML = errors.incorrectAgr;
	        return false;
	    } else {
	        $('agr_div').innerHTML = '';
	    }
    }
    
    $('register-form').submit();
    
}

function check_email(str) {
    var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    if (!filter.test(str)) { return false; } else { return true; }
}


function validateUsername(fld, errors) {
    var error = "";
    var illegalChars = /\W/; // allow letters, numbers, and underscores
 
    if (fld.value == "") {
        error = errors.incorrectName;
    } else if ((fld.value.length < 2) || (fld.value.length > 15)) {
        error = errors.incorrectNameLen;
    } else if (illegalChars.test(fld.value)) {
        error = errors.incorrectNameChars;
    } else {
        fld.style.background = 'White';
    } 

    return error;
}