function U(path, arr){
	var url = path;
	$.each(arr, function(i, v){
		url += url == path ? "/"+i+"^"+v : "^"+i+"^"+v;
	});
	return url;
}