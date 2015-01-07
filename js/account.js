$(function(){
	// 住所入力
	$(window).load(function(){
		loadCookies($('#accountForm'), false, 'test_');
	});

	$('#accountForm').submit(function(){
		if ($('input[name="saveCookie"]', this).attr('checked')){
			setCookies($(this), 'test_');
		}else{
			clearCookies($(this), 'test_');
		}
	});



	$('.copyAccount').click(function(){
		$('input[name="reciever_name"]').val($('input[name="order_name"]').val());
		$('input[name="reciever_charge"]').val($('input[name="order_charge"]').val());
		$('input[name="reciever_zipcode"]').val($('input[name="order_zipcode"]').val());
		$('select[name="reciever_pref"]').val($('select[name="order_pref"]').val());
		$('input[name="reciever_city"]').val($('input[name="order_city"]').val());
		$('input[name="reciever_town"]').val($('input[name="order_town"]').val());
		$('input[name="reciever_build_name"]').val($('input[name="order_build_name"]').val());
		$('input[name="reciever_phone"]').val($('input[name="order_phone"]').val());

	});


	$('#backStep').click(function(){
		$('form#backForm').submit();
	});
	var reqMark = '<span style="font-weight: bold; color: red;">*</span>';
	$('.required').append(reqMark);
	// 郵便番号検索追加
	$('.search-address').on('click', function(){
		var zipCode = $('[name=order_zipcode]').val();
		searchAddress(zipCode, 'order_pref', 'order_city', 'order_town');
	});

	$('.search-rcv-address').on('click', function(){
		var zipCode = $('[name=reciever_zipcode]').val();
		searchAddress(zipCode, 'reciever_pref', 'reciever_city', 'reciever_town');
	});

	var searchAddress = function(zipCode, formPref, formCity, formTown) {
		if (zipCode) {
			$.ajax({
				url: "/zip_search.php",
				dataType: "jsonp",
				data: {"zipcode": zipCode},
				beforeSend: function(){
					$('.loading').show();
				}
			})
			.done(function(d, s, e) {
				if (d.status == 200 && d.results){
					result = d.results[0];
					$('[name=' + formPref + ']').val(result.prefcode);
					$('[name=' + formCity + ']').val(result.address2);
					$('[name=' + formTown + ']').val(result.address3)
				}
			})
			.fail(function(d, s, e) {
			})
			.always(function(d, s, e){
				$('.loading').hide();
			});
		}
	};
});


loadCookies = function(form, overWrite ,namePrefix){
		var prefix = getPrefix(namePrefix);
		if (form.is('form')){
			$(selectors, form).each(function(){
				var storeVal = $.cookie(prefix + $(this).attr('name'));
				if ($(this).is(':checkbox')){
					if (storeVal == 1){
						if (overWrite || !$(this).attr('checked')){
							$(this).attr('checked', 'checked');
						}
					}
				}else if($(this).is(':radio')){
					if (overWrite || $(this).val() == storeVal){
						$(this).attr('checked', 'checked');
					}
				}else if($(this).is('select')){
					if (overWrite || $(this).val() == 0){
						$(this).val(storeVal);
					}
				}else if(overWrite ||
						!$(this).val() && $(this).val().length == 0){
					$(this).val(storeVal);
				}

			});

		}
	}


	setCookies = function(form, namePrefix){
		var prefix = getPrefix(namePrefix);
		if (form.is('form')){
			$(selectors, form).each(function(){
				//console.log($(this).attr('name') + ':' + $(this).val());
				var val;
				if ($(this).is(':checkbox')){

					if ($(this).attr('checked')){
						val = 1;
					}else { val = 0; }
					//$.cookie(prefix + $(this).attr('name'), val);
				}else if($(this).is(':radio')){
					if ($(this).is(':checked')){
						//$.cookie(prefix + $(this).attr('name'), val);
						val = $(this).val();
					}else{
						return true;
					}
				}else if($(this).is('select')){
					val = $(':selected', this).val();
					//$.cookie(prefix + $(this).attr('name'), val);
				}else{
					val = $(this).val();
					//$.cookie(prefix + $(this).attr('name'), $(this).val());
				}
				$.cookie(prefix + $(this).attr('name'), val, { expires: 300 });
			});
		}
	}
	clearCookies = function(form, namePrefix){
		var prefix = getPrefix(namePrefix);
		if (form.is('form')){
			$(selectors, form).each(function(){
				$.cookie(prefix + $(this).attr('name'), null);
			});
		}
	}
	var selectors = ':text, textarea, :radio, select, :checkbox';
	getPrefix = function(namePrefix){
		return !namePrefix ? '' : namePrefix;
	}
