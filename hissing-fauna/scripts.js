$(function(){

    var diaryEntries     = '',
        diaryEntriesList = [],
        author           = $('#post_author'),
        text            = $('#post_text'),
        post_id          = $('#post_id');

    $('#formEntry').on('submit',modify_post);

    function reset_form(){
        author.val('').removeClass('error').blur();
        text.val('').removeClass('error').blur();
        post_id.val('');
    }

    function show_posts() {
        $('#formEntry').fadeOut('fast',function(){$('#entries').fadeIn('fast');});
    }

    function show_form() {
        $('#entries').fadeOut('fast',function(){$('#formEntry').fadeIn('fast');});
    }

    function bind_post_events(){
        diaryEntries=document.querySelectorAll('#entries article');
        if(diaryEntries.length>0||diaryEntries !== null){
            for (var i=0;i<diaryEntries.length;i++){
                document.querySelectorAll('#entries article h2')[i].addEventListener('click',toggle_single);
                document.querySelectorAll('#entries article .approve')[i].addEventListener('click',approve_post);
                document.querySelectorAll('#entries article .remove')[i].addEventListener('click',reject_post);
                document.querySelectorAll('#entries article .edit')[i].addEventListener('click',load_post);
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
            toggle_all("off");
            removeClass(t.parentNode,'active');
        }else{
            toggle_all("off");
            addClass(t.parentNode,'active');
        }
    }

    function toggle_all(s){
        switch(s){
            case "off":
                for(var i=0;i<diaryEntries.length;i++){
                    removeClass(diaryEntries[i],'active');
                }
            break;
            case "on":
                for(var i=0;i<diaryEntries.length;i++) {
                    addClass(diaryEntries[i],'active');
                }
            break;
            case "maybe":
                var actives=0;

                for (var i=0;i<diaryEntries.length;i++) {
                    if(hasClass(diaryEntries[i],'active')){
                        actives++;
                    }
                }
                if(actives>0){
                    toggle_all("off");
                }else{
                    toggle_all("on");
                }
            break;
        }
    }

    function reject_post(ev){
        ev.preventDefault();

        var statut = $(this).closest('article'),
            key    = statut.attr('rel');

        $.ajax({
            url      : 'requete.php',
            type     : 'post',
            dataType : 'html',
            data     : {
                etat      : 'Rejeter',
                timestamp : key
            },
            success  : function(msg){
                statut.fadeOut();
            },
            error    : function(xhr,code){
                alert(code);
            }
        });
    }

    function approve_post(ev){
        ev.preventDefault();

        var statut = $(this).closest('article'),
            key    = statut.attr('rel');

        $.ajax({
            url      : 'requete.php',
            type     : 'post',
            data     : {
                etat      : 'Approuver',
                timestamp : key
            },
            success  : function(msg){
                statut.fadeOut();
            },
            error    : function(xhr,code){
                alert(code);
            }
        });
    }

    function load_post(ev){
        ev.preventDefault();

        var element = $(this).closest('article'),
            key     = element.attr('data-id'),
            statut  = {};

        $.ajax({
            url      : '/posts/' + key,
            type     : 'get',
            dataType : 'json',
            data     : {},
            success  : function( response ){
                display_post( key, response );
            },
            error    : function(xhr,code){
               alert(code);
            }
        });
    }

    function display_post( key, data ){
        post_id.val(key);
        author.val(data.author);
        text.val(data.text);
        show_form();
    }

    function modify_post(ev){
        ev.preventDefault();

        var key   = post_id.val(),
            error = false;

        author.removeClass('error');
        text.removeClass('error');

        if(author.val().length === 0){
            author.addClass('error');
            error = true;
        }

        if(text.val().length === 0){
            text.addClass('error');
            error = true;
        }

        if(error){
            alert('You must fill all fields');
            return;
        }

        if(error === false){

            $.ajax({
                url  : '/posts/' + key,
                type : 'put',
                data : {
                    author : author.val(),
                    text   : text.val()
                },
                success : function(msg){
                    var post = $('article[data-id='+key+']');
                    $('.sAuteur',post).text(author.val());
                    $('.sTexte',post).text(text.val());
                    show_posts();
                    reset_form();
                },
                error   : function(xhr,code){
                   alert(code);
                }
            });
        }
    }

    document.querySelector('h1').addEventListener('click',function(){
        toggle_all("maybe");
    });

    $('#btnCancel').click(function(){
        show_posts();
        reset_form();
    });

    bind_post_events();
});