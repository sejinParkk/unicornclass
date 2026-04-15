//콤마찍기
function comma(str) {
	str = String(str);
	return str.replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');
}

//콤마풀기
function uncomma(str) {
	str = String(str);
	return str.replace(/[^\d]+/g, '');
}

//값 입력시 콤마찍기
function inputNumberFormat(obj) {
	if(obj.value == ""){
		obj.value = '0';
	}else{
		let cleanValue = uncomma(obj.value).replace(/^0+/, '') || '0';
        obj.value = comma(parseInt(cleanValue));
	}
}