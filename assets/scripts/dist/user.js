$(function(){
    var post_author = $('#post_author'),
        post_text   = $('#post_text'),
        post_image  = $('#post_image');

    $('.js-input-file').on('change', function(event) {
        var file_name = $(this)[0].files[0].name;
        $(this).siblings('.js-input-file-path').text(file_name);
    });

    $('.js-erase-form').on('click', function() {
        post_author.val('').removeClass('error').blur();
        post_text.val('').removeClass('error').blur();
        post_image.val('').removeClass('error').blur();
        $('.js-input-file-path').text('');
        $('.js-no-content').removeClass('none');
    });

    $('#formEntry').on('submit', function(event) {
        event.preventDefault();

        var error = false;

        post_author.removeClass('error');
        post_text.removeClass('error');
        $('.js-no-content').removeClass('none');

        if (post_author.val().length === 0) {
            post_author.addClass('error');
            error = true;
        }

        if (post_text.val().length === 0 && post_image.val() === '') {
            post_text.addClass('error');
            $('.js-no-content').addClass('none');
            error = true;
        }

        if (error === false) {
            $(this).unbind('submit').submit();
            $('#overlay').show();
        }
    });
});