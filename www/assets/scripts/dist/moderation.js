var author_input  = $('#post_author'),
    text_input    = $('#post_text'),
    post_id_input = $('#post_id'),
    post_image    = $('#post_image');

function reset_form(){
    author.val('').removeClass('error').blur();
    text_input.val('').removeClass('error').blur();
    post_id_input.val('');
}

function show_posts() {
    $('.js-moderation-form').fadeOut('fast', function() {
        $('.js-post-list-container').fadeIn('fast');
    });
}

function show_form() {
    $('.js-post-list-container').fadeOut('fast', function() {
        $('.js-moderation-form').fadeIn('fast');
    });
}

function bind_post_events() {
    $('.js-edit-post').on('click',load_post);
    $('.js-approve-post').on('click',approve_post);
    $('.js-reject-post').on('click',reject_post);
}

function reject_post(event) {
    event.preventDefault();

    var element = $(this).closest('.js-post'),
        post_id = element.attr('data-id');

    $.ajax({
        url     : '/posts/' + post_id,
        type    : 'put',
        data    : {
            status : 'rejected'
        },
        success : function(msg){
            element.fadeOut();
        },
        error   : function(xhr,code){
           alert(code);
        }
    });
}

function approve_post(event) {
    event.preventDefault();

    var element = $(this).closest('.js-post'),
        post_id = element.attr('data-id');

    $.ajax({
        url     : '/posts/' + post_id,
        type    : 'put',
        data    : {
            status : 'approved'
        },
        success : function(msg){
            element.fadeOut();
        },
        error   : function(xhr,code){
           alert(code);
        }
    });
}

function load_post(event) {
    event.preventDefault();

    var element = $(this).closest('.js-post'),
        post_id = element.attr('data-id');

    $.ajax({
        url      : '/posts/' + post_id,
        type     : 'get',
        dataType : 'JSON',
        success  : function( response ){
            display_post( post_id, response );
        },
        error    : function(xhr,code){
           alert(code);
        }
    });
}

function modify_post(event) {
    event.preventDefault();

    var post_id = post_id_input.val(),
        error   = false;

    author_input.removeClass('error');
    text_input.removeClass('error');

    if(author_input.val().length === 0){
        author_input.addClass('error');
        error = true;
    }

    if(text_input.val().length === 0){
        text_input.addClass('error');
        error = true;
    }

    if (error) {
        alert('You must fill all fields');
        return;
    }

    if (error === false) {

        $.ajax({
            url  : '/posts/' + post_id,
            type : 'put',
            data : {
                author : author_input.val(),
                text   : text_input.val()
            },
            success : function(msg){

                var post = $('[data-id='+post_id+']');

                $('.js-author', post).text(author_input.val());
                $('.js-text', post).text(text_input.val());

                show_posts();
                reset_form();
            },
            error   : function(xhr,code){
               alert(code);
            }
        });
    }
}

function display_post( post_id, data ){
    post_id_input.val(post_id);
    author_input.val(data.author);
    text_input.val(data.text);
    post_image.attr('src', '/uploads/' + data.image);
    show_form();
}

$(function(){
    $('.js-moderation-form').on('submit',modify_post);

    $('.js-button-cancel').on('click',function(){
        show_posts();
        reset_form();
    });

    bind_post_events();
});