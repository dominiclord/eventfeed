$(function(){

    var posts     = '',
        postsList = [],
        author_input     = $('#post_author'),
        text_input       = $('#post_text'),
        post_id_input    = $('#post_id');

    $('#post_form').on('submit',modify_post);

    function reset_form(){
        author.val('').removeClass('error').blur();
        text_input.val('').removeClass('error').blur();
        post_id_input.val('');
    }

    function show_posts() {
        $('#post_form').fadeOut('fast',function(){$('#posts').fadeIn('fast');});
    }

    function show_form() {
        $('#posts').fadeOut('fast',function(){$('#post_form').fadeIn('fast');});
    }

    function bind_post_events(){
        posts = document.querySelectorAll('#posts article');
        if(posts.length>0 || posts !== null){
            for (var i=0, len = posts.length; i < len; i++){
                document.querySelectorAll('#posts article h2')[i].addEventListener('click',toggle_single);
                document.querySelectorAll('#posts article .approve')[i].addEventListener('click',approve_post);
                document.querySelectorAll('#posts article .reject')[i].addEventListener('click',reject_post);
                document.querySelectorAll('#posts article .edit')[i].addEventListener('click',load_post);
            }
        }
    }

    function hasClass(element,cls){
        return(' '+element.className+' ').indexOf(' '+cls+' ')>-1;
    }

    function addClass(element,cls){
        element.className+=cls;
    }

    function removeClass(element,cls){
        element.className=element.className.replace(/(?:^|\s)active(?!\S)/,'');
    }

    function toggle_single(ev){
        var t=ev.target;
        if(hasClass(t.parentNode,'active')){
            toggle_all('off');
            removeClass(t.parentNode,'active');
        }else{
            toggle_all('off');
            addClass(t.parentNode,'active');
        }
    }

    function toggle_all(s){

        var len = posts.length;

        switch(s){
            case 'off' :
                for( var i = 0; i < len; i++ ) {
                    removeClass( posts[i], 'active' );
                }
            break;
            case 'on' :
                for( var j = 0; i < len; i++ ) {
                    addClass( posts[i], 'active' );
                }
            break;
            case 'maybe' :
                var actives = 0;

                for ( var k = 0; i < len; i++ ) {
                    if( hasClass( posts[i], 'active' ) ){
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

    function reject_post(ev){
        ev.preventDefault();

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

    function approve_post(ev){
        ev.preventDefault();

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

    function load_post(ev){
        ev.preventDefault();

        var element = $(this).closest('article'),
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

    function modify_post(ev){
        ev.preventDefault();

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

    document.querySelector('h1').addEventListener('click',function(){
        toggle_all('maybe');
    });

    $('#btnCancel').click(function(){
        show_posts();
        reset_form();
    });

    bind_post_events();
});