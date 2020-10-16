jQuery( function( $ ) {
	'use strict';

	setTimeout(function(){
			$('#cc').inputmask("9999 9999 9999 9999");
			$('#expiration').inputmask("99/99");
		}, 3000
	);
	$(document).on('focusout', 'input[name="ccnumber"]', function() {
		var valid_card_number = $(this).val().replace(/\D/g, "");
		if ( valid_card_number.length < 16 ) {
			$("<div class='stripe-source-errors' role='alert'><ul class='woocommerce_error woocommerce-error wc-stripe-error'><li>The card number is incomplete.</li></ul></div>").insertAfter('input[name="ccnumber"]');
			$('button[name="woocommerce_checkout_place_order"]').attr('disabled','disabled');
		} else {
			$(this).next().remove();
			$('button[name="woocommerce_checkout_place_order"]').removeAttr('disabled');
		}
	});
	$(document).on('focusout', 'input[name="exp-date"]', function() {
		var valid_card_exp_date = $(this).val().replace(/\D/g, "");
		if ( valid_card_exp_date.length < 4 ) {
			$("<div class='stripe-source-errors' role='alert'><ul class='woocommerce_error woocommerce-error wc-stripe-error'><li>The card's expiration date is incomplete.</li></ul></div>").insertAfter('.form-row-last');
			$('button[name="woocommerce_checkout_place_order"]').attr('disabled','disabled');
		} else {
			$(this).next().remove();
			$('button[name="woocommerce_checkout_place_order"]').removeAttr('disabled');
		}
	});
	$(document).on('focusout', '#vantiv-cvc-element', function() {
		var valid_card_exp_date = $(this).val().replace(/\D/g, "");
		if ( valid_card_exp_date.length < 3 ) {
			$("<div class='stripe-source-errors' role='alert'><ul class='woocommerce_error woocommerce-error wc-stripe-error'><li>The card's security code is incomplete.</li></ul></div>").insertAfter('.form-row-last');
			$('button[name="woocommerce_checkout_place_order"]').attr('disabled','disabled');
		} else {
			$(this).next().remove();
			$('button[name="woocommerce_checkout_place_order"]').removeAttr('disabled');
		}
	});
} );
