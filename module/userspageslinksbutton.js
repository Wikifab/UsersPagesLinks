(function() {
	

	function linkAdded( page, type) {
		$(".addUsersPagesLinksButton[data-page='"+page+"'][data-linkstype='"+type+"']").hide();
		$(".rmUsersPagesLinksButton[data-page='"+page+"'][data-linkstype='"+type+"']").show();
	};
	function linkRemoved(page, type) {
		$(".addUsersPagesLinksButton[data-page='"+page+"'][data-linkstype='"+type+"']").show();
		$(".rmUsersPagesLinksButton[data-page='"+page+"'][data-linkstype='"+type+"']").hide();
	};



	$('.addUsersPagesLinksButton').click(function() {
		
		var page = $(this).attr('data-page');
		var type = $(this).attr('data-linkstype');
		
		// fonction to do second request to execute follow action
		function ajaxPageslinkQuery(jsondata) {
			var token = jsondata.query.tokens.csrftoken;
			$.ajax({
				type: "POST",
				url: mw.util.wikiScript('api'),
				data: { action:'userspageslinks', format:'json', upl_action: 'add', token: token, page: page, type: type},
			    dataType: 'json',
			    success: function (jsondata) {
					if(jsondata.userspageslinks.success == 1) {
						linkAdded(page, type);
					}
			}});
		};
		
		// first request to get token
		$.ajax({
			type: "GET",
			url: mw.util.wikiScript('api'),
			data: { action:'query', format:'json',  meta: 'tokens', type:'csrf'},
		    dataType: 'json',
		    success: ajaxPageslinkQuery
		});
	});


	$('.rmUsersPagesLinksButton').click(function() {

		
		var page = $(this).attr('data-page');
		var type = $(this).attr('data-linkstype');
		
		// fonction to do second request to execute follow action
		function ajaxPagesLinksQuery(jsondata) {
			var token = jsondata.query.tokens.csrftoken;
			$.ajax({
				type: "POST",
				url: mw.util.wikiScript('api'),
				data: { action:'userspageslinks', format:'json', upl_action: 'remove', token: token, page: page, type: type},
			    dataType: 'json',
			    success: function (jsondata) {
					if(jsondata.userspageslinks.success == 1) {
						linkRemoved(page, type);
					}
			}});
		};
		
		// first request to get token
		$.ajax({
			type: "GET",
			url: mw.util.wikiScript('api'),
			data: { action:'query', format:'json',  meta: 'tokens', type:'csrf'},
		    dataType: 'json',
		    success: ajaxPagesLinksQuery
		});
	});
})();
