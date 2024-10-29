jQuery(document).ready(function($){
	$('#adn_status_update').click(function () {
		var data = {
			action: 'adnstatus-update',
			accesstoken: $('#adn_accesstoken').val(),
			status_code:  $('#adn_status_code').val(),
			status: $('#adn_status').val(),
			adnStatusNonce: ADNStatus.adnStatusNonce
		};
		$("#adn_result").html("Saving...");
	   	jQuery.post( ADNStatus.ajaxurl, data, function(response) {
				if(response == '200')
				{
					$("#adn_result").html("Saved");
				}
				else
				{
					$("#adn_result").html("Error");
				}
        	} );

	});
});