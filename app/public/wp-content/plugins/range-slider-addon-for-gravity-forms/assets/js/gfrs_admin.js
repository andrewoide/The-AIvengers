; (function ($) {
    $(document).ready(function () {
        jQuery("#pcafe_tab_box div.tab_item").hide();
        jQuery("#pcafe_tab_box div:first").show();
        jQuery(".pcafe_menu_wrap li:first").addClass("active");

        // Change tab class and display content
        jQuery(".pcafe_menu_wrap a").on("click", function (event) {
            event.preventDefault();
            jQuery(".pcafe_menu_wrap li").removeClass("active");
            jQuery(this).parent().addClass("active");
            jQuery("#pcafe_tab_box div.tab_item").hide();
            jQuery(jQuery(this).attr("href")).show();
        });

        $('.p__install').on('click', function (e) { 
            $(this).find('.p_btn_text').text('Installing...');
            $(this).find('.loader').addClass('active');
        });

        $('.p__activate').on('click', function (e) { 
            $(this).find('.p_btn_text').text('Activating...');
            $(this).find('.loader').addClass('active');
        });
    });
})(jQuery);