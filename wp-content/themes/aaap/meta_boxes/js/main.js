$ = jQuery.noConflict();
$(document).ready(function() {
    var _custom_media = true,
            _orig_send_attachment = wp.media.editor.send.attachment;

    $(document).on('click', '.middle_box_image', function(e) {
        var send_attachment_bkp = wp.media.editor.send.attachment;
        var button = $(this);
        var id = button.attr('id');
        _custom_media = true;
        wp.media.editor.send.attachment = function(props, attachment) {

            if (_custom_media) {
                button.html('Remove image');
                button.attr('class', 'remove_middle_box_image');
                $("#" + id + '_url').val(attachment.url);
                $("#" + id + '_id').val(attachment.id);
//                button.attr('disabled', 'disabled');
                $('#' + id).before('<img src="' + attachment.url + '" class="prev_image" />');
            } else {
                return _orig_send_attachment.apply(this, [props, attachment]);
            }
            ;
        };
        wp.media.editor.open(button);
        return false;
    });



    $(document).on('click', '.remove_middle_box_image', function() {
        var remove_button = $(this);
        var id = remove_button.attr('id');
        $("#" + id + '_url').val('');
        $("#" + id + '_id').val('');
        $('.prev_image').remove();
        remove_button
                .attr('class', 'middle_box_image')
                .html('Add Image');
    });



    /**
     * Trigger popup for choosing image in people custom post
     */
    $(document).on('click', '.people_image', function(e) {
        var send_attachment_bkp = wp.media.editor.send.attachment;
        var button = $(this);
        var id = button.attr('id');
        _custom_media = true;
        wp.media.editor.send.attachment = function(props, attachment) {
            if (_custom_media) {
                if (id != 'pdf') {
                    $("#" + id + '_id').val(attachment.id);
                    $('#' + id).before('<img src="' + attachment.url + '" class="prev_image" />');
                    button.attr('class', 'remove_people_image button-primary button-large');
                    var button_string = 'Remove Image';
                } else {
                    var button_string = 'Remove PDF';
                    $('#' + id).before('<img src="' + base_url + '/images/admin/pdf.jpg" class="prev_image pdf_preview" />');
                    $('#' + id + '_link').val(attachment.url);
                    button.attr('class', 'remove_pdf_image button-primary button-large');
                }
                button.html(button_string);

                $("#" + id + '_url').val(attachment.url);


            } else {
                return _orig_send_attachment.apply(this, [props, attachment]);
            }
            ;
        };
        wp.media.editor.open(button);
        return false;
    });



    $(document).on('click', '.remove_people_image', function() {
        var remove_button = $(this);
        var id = remove_button.attr('id');
        $("#" + id + '_url').val('');
        $("#" + id + '_id').val('');
        $('.prev_image').remove();
        remove_button
                .attr('class', 'people_image button-primary button-large')
                .html('Add Image');
    });

    $(document).on('click', '.remove_pdf_image', function() {
        var remove_button = $(this);
        var id = remove_button.attr('id');
        $("#" + id + '_url').val('');
        $("#" + id + '_link').val('');
        $('.pdf_preview').remove();
        remove_button
                .attr('class', 'people_image button-primary button-large')
                .html('Add PDF');
    });




    /**
     * Hide input label on focus
     */
    $('.meta_box_input').focus(function() {
        var input_val = $(this).val();
        if (input_val === '') {
            $(this).parent().find('label').hide();
        }
    });

    $('.meta_box_input').blur(function() {
        var input_val = $(this).val();
        if (input_val === '') {
            $(this).parent().find('label').show();
        }
    });

    $('.meta_box_label').click(function() {
        $(this).parent().find('.meta_box_input').trigger('focus');
    });



    /**
     * Validate name on people page
     */

    $('#title').addClass('required');
    $('#post').validate({
        rules: {
            title: "required"
        },
        errorPlacement: function(label, element) {
            label.addClass('error');
            label.insertAfter(element);
            $(element).parent().prepend('<div class="error_indicator"></div>');

        },
        success: function(error, element) {
            $(element).parent().find('.error_indicator').remove();

        }
    });


    /**
     * Select type (Link/PDF)
     */
    $('#select_type').change(function() {
        var type_value = $(this).val();
        $('.selection').hide();
        $('.' + type_value + '_wrapper').show();
    });

    

    var fixHelperModified = function(e, tr) {
        var $originals = tr.children();
        var $helper = tr.clone();
        $helper.children().each(function(index) {
            $(this).width($originals.eq(index).width());
        });
        return $helper;
    };
          updateIndex = function(e, ui) {
                var indexes = new Array();
                
                
                $("#the-list tr").each(function(i) {
                    var tr_data = {};
                    tr_data['post_id'] = $(this).attr('id').substring(5);
                    tr_data['home_index'] = i;
                    indexes.push(tr_data);
                });
                
                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        action: 'update_indexes',
                        data: indexes
                    },
                    success: function(data) {

                        console.log(data);

                    }
                });

            };
            /**
     * Sort post
     */
    $("#the-list").sortable({
        helper: fixHelperModified,
        stop: updateIndex
    }).disableSelection();

    $("#the-list tr").css('cursor', 'move');




});





