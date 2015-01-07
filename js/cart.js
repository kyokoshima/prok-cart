$(function(){
	recalc();

	$("input[type=text][name^='qty']").change(function(){
		var index = getIndexByName($(this));
		setQty(index, $(this).val());
		recalc();
	});

	$('form[name=orderForm]').submit(function(){
		if (!location.host.match(/localhost/i)){
			var url = location.href;
			if (location.protocol == 'http:'){
				url = url.replace('http', 'https');
				$(this).attr('action', url);
			}
		}
	});

	$('#recalc').click(function(){
		//recalc();
		$('#recalcForm').empty();
		$('input[name^="qty"]').each(function(){
			$('<input>'
			, {name:$(this).attr('name'), value:$(this).val()})
				.appendTo($('#recalcForm'));

		});
		$('#recalcForm').append($('<input>', {name:'step', value:'recalcOrder'}));
		$('#recalcForm').submit();

	});

	$('#close').click(function(){
		window.close();
	});
});

recalc = function(){
	$('.qtySelect').val(function(){
		var index = getIndexById($(this));
		return getQty(index);
	});
	$('.priceData').text(function(){
		var index = getIndexById($(this));
		return formatPrice(getPrice(index))
	});
	$('.subTotalData').text(function(){
		var index = getIndexById($(this));
		var price = getPrice(index);
		var qty = getQty(index);

		if (price && qty){
			return formatPrice(price * qty);
		}
	});

	$('.totalData').text(function(){
		var total = 0;
		$("input[name^='product_code']").each(function(){
			var index = getIndexByName($(this));
			var price = getPrice(index);
			var qty = getQty(index);
			total += price * qty;
		});
		var tax = Math.floor(total * (taxRatio / 100));
		$('#product-total').text(formatPrice(total));
		$('#tax').text(formatPrice(tax));
		return formatPrice(parseInt(total + tax));
	});


}


getIndexById = function(obj){
	if (obj){
		var id = obj.attr('id');
		return id.split('-')[1];
	}
}
getIndexByName = function(obj){
	if (obj){
		var name = obj.attr('name');
		name = name.split('[')[1];
		return name.replace(']', '');
	}

}
getPrice = function(index){
	return getVal('price', index);
};

getQty = function(index){
	return getVal('qty', index);
}

getVal = function(name, index){
	if (index){
		return $("input[name='" + name + "[" + index + "]']").val();
	}
}

setQty = function(index, val){
	setVal('qty', index, val);
}

setVal = function(name, index, val){
	if (index && val){
		return $("input[name='" + name + "[" + index + "]']").val(val);
	}
}

formatPrice = function(val){
	if (val){
    	var num = new String(val).replace(/,/g/"");
    	while(num != (num =num.replace(/^(-?\d+)(\d{3})/,"$1,$2")));
    	return "¥" + num;
	}
}
