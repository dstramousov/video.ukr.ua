/*
function gebo(id) {document.getElementById(id).className='dv0';}
function gebi(id) {document.getElementById(id).className='dv1';}
function gebr(id) {document.getElementById(id).style.color='red';}
function gebd(id) {document.getElementById(id).style.color='black';}

var n = 0;
function cont_up(idx)  {
    n = n - 25;
    document.getElementById(idx).style.top=n+'px';
    }
    
function cont_dwn(idx)  {
    n = n + 25;
    document.getElementById(idx).style.top=n+'px';
}
*/


var st = 1;

function check_check(idf, idh)
    {
    var mm = document.getElementById(idf).checked;
    if (mm == true) {
    st = 1;
    document.getElementById(idh).className = 'custom_check_on';
    }
    else {
    st = -1;
    document.getElementById(idh).className = 'custom_check_off';
    }
}

function custom_chekcbox(idy, idz) {
    st = st * -1;
if (st > 0) {
    document.getElementById(idy).checked='checked';
    document.getElementById(idz).className = 'custom_check_on';
    };

if (st < 0) {
    document.getElementById(idy).checked='';
    document.getElementById(idz).className = 'custom_check_off';
    };

}

function toggle_ex(id, clss) {document.getElementById(id).className=clss;}
