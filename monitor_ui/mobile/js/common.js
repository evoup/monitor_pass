// common init ///////////////////////////////////////////////////////////////////////////////

$(function(){
	$('#home').click(function(){
		location.href = 'http://' + domainURI() +'/view/monitorui/mobile/index.html?version=' + url_params('version');
		return false;
	});
}) 