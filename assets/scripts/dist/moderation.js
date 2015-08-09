var $posts,
    opened_class  = 'is-opened',
    author_input  = $('#post_author'),
    text_input    = $('#post_text'),
    post_id_input = $('#post_id');

function reset_form(){
    author.val('').removeClass('error').blur();
    text_input.val('').removeClass('error').blur();
    post_id_input.val('');
}

function show_posts() {
    $('.js-moderation-form').fadeOut('fast', function() {
        $('.js-post-list').fadeIn('fast');
    });
}

function show_form() {
    $('.js-post-list').fadeOut('fast', function() {
        $('.js-moderation-form').fadeIn('fast');
    });
}

function bind_post_events() {

    $posts = $('.js-moderation-post');

    $('.js-moderation-post_header').on('click',toggle_single);

    $('.js-edit-post').on('click',load_post);
    $('.js-approve-post').on('click',approve_post);
    $('.js-reject-post').on('click',reject_post);
}

function toggle_single(event) {

    var $this = $(this),
        $parent = $this.parent();

    if ($parent.hasClass(opened_class)) {
        toggle_all('off');
        $parent.removeClass(opened_class);
    } else {
        toggle_all('off');
        $parent.addClass(opened_class);
    }
}

function toggle_all(state) {

    var len = $posts.length;

    switch (state) {
        case 'off' :
            for (var i = 0; i < len; i++) {
                $($posts[i]).removeClass(opened_class);
            }
        break;
        case 'on' :
            for (var j = 0; j < len; j++) {
                $($posts[j]).addClass(opened_class);
            }
        break;
        case 'maybe' :
            var actives = 0;

            for (var k = 0; k < len; k++) {
                if( $($posts[k]).hasClass(opened_class) ){
                    actives++;
                }
            }

            if( actives > 0 ){
                toggle_all('off');
            }else{
                toggle_all('on');
            }

        break;
    }
}

function reject_post(event) {
    event.preventDefault();

    var element = $(this).closest('article'),
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

    var element = $(this).closest('article'),
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

    var $element = $(this).closest('.js-moderation-post'),
        post_id = $element.attr('data-id');

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

    if(error){
        alert('You must fill all fields');
        return;
    }

    if(error === false){

        $.ajax({
            url  : '/posts/' + post_id,
            type : 'put',
            data : {
                author : author_input.val(),
                text   : text_input.val()
            },
            success : function(msg){

                var post = $('article[data-id='+post_id+']');

                $('.sAuteur',post).text(author_input.val());
                $('.sTexte',post).text(text_input.val());

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
    show_form();
}

$(function(){
    $('.js-moderation-form').on('submit',modify_post);

    $('.js-button-cancel').on('click',function(){
        show_posts();
        reset_form();
    });

    $('.js-moderation-toggle-all').on('click',function(){
        toggle_all('maybe');
    });

    bind_post_events();
});