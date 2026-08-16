(function($) {
"use strict";
	jQuery(document).ready(function($){
		setTimeout(function(){ 
			$(".elementor").not(".elementor-location-popup").each(function(e){
		    	$(this).find(".elementor-field-range-slider").ionRangeSlider();
		    }) 
		 }, 200);
	    jQuery( document ).on( 'elementor/popup/show', () => {
			$(".elementor-field-range-slider").ionRangeSlider();
		} );
		$("input").on("done_load_repeater",function(e,item){
			$(".elementor-field-range-slider").ionRangeSlider();
		})
	})
})(jQuery);