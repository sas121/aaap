$ = jQuery.noConflict();
$(document).ready(function() {

    //Setting position of submenu for last menu item
    var last_menu_item = $('#menu-header-menu li:last-child');
    var last_menu_link = last_menu_item.find('.main_menu_link');
    var submenu_offset = last_menu_link.outerWidth();
    var last_menu_items_count = last_menu_item.find('li').length;
    if (last_menu_items_count <= 3)
        submenu_offset = 0;
    else if (last_menu_items_count > 6)
        submenu_offset = submenu_offset * 3;

    last_menu_item.find('div').css('left', '-' + submenu_offset + 'px');


    //News changer for header section
    var timeOffset = 10000;
    var animTime = 800;
    var next_selector;
    setInterval(function() {
        var current_news = $('.show_news');
        var next_news = $('.show_news').next();
        current_news.fadeOut(animTime).removeClass('show_news').addClass('hide_news');
        if (next_news.length === 0) {
            next_selector = $('.news_holder .news:first-child');
        } else {
            next_selector = next_news;
        }
        next_selector.fadeIn(animTime).removeClass('hide_news').addClass('show_news');

    }, timeOffset);


    $('.input_wrapper select option:first-child').attr('value', "");


    /**
     * Set fancy select box
     */
    $('#filter_member').select2({
        width: '100%'

    });

    $('.input_wrapper .left select').select2({
        width: '100%',
        minimumResultsForSearch: -1,
        dropdownCssClass: 'left_dropdown'
    });

    $('.input_wrapper .right select').select2({
        width: '100%',
        minimumResultsForSearch: -1,
        dropdownCssClass: 'right_dropdown'
    });


    /**
     * Filter doctors with selected value from select with filter_member id
     */
    $('#filter_member').change(function() {
        var selected = $(this).val();

        if (selected == '') {
            $('.people_info').show();
            return;
        }

        var prepare = selected.toLowerCase();
        var prepare2 = prepare.replace(/\s/g, '_');



        $('.people_info.' + prepare2 + '').show();
        $('.people_info:not(.' + prepare2 + ')').hide();

    });

    /**
     * Add target blank to first menu item(Login) in small top menu
     */
    $('#menu-small-top-menu li:first-child a').attr('target', '_blank');

    /**
     * Handle hiding/showing input label on input focus/blur 
     */

    $('.input_wrapper input, .input_wrapper textarea').focus(function() {
        var input_val = $(this).val();
        if (input_val === '') {
            $(this).closest('div').find('label').hide();
        }
    });

    $('.input_wrapper input, .input_wrapper textarea').blur(function() {
        var input_val = $(this).val();
        if (input_val === '') {
            $(this).closest('div').find('label').show();
        }
    });

    $('.input_wrapper label').click(function() {
        $(this).parent().find('input,textarea').trigger('focus');
    });

    /**
     * Turn of autocomplete on all input fields and textareas
     */
    $('input,textarea').attr('autocomplete', 'off');


    /**
     * Validate form before do redirection 
     */
    $('#disclaimer').validate({
        rules: {
            year: "required",
            month: "required",
            day: "required"
        },
        errorPlacement: function(label, element) {
            label.addClass('error');
//            label.insertAfter(element);
//            $(element).parent().prepend('<div class="error_indicator"></div>');
        },
        success: function(error, element) {
            $(element).parent().find('.error_indicator').remove();
        }
    });


    $('#disclaimer').submit(function(e) {
        var agree = $('#agree').is(':checked');
        if (!agree) {
            $('.error_placment').html('Disclaimer field is required');
            e.preventDefault();
        } else {
            $('.error_placment').html('');
        }
    });
});


