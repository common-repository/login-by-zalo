var f =  document.getElementById('loginform');
var t = document.getElementsByClassName('login-zalo-wrap');
if (f.length) {
	f.insertAdjacentElement('afterbegin', t[0]); 
}