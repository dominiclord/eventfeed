$(function(){

    var diaryEntries     = '',
        diaryEntriesList = [],
        auteur           = $('#auteur'),
        texte            = $('#texte'),
        formMode         = $('#formMode');

    $('#formEntry').submit(modifierStatut);

    function resetForm(){
        auteur.val('').removeClass('error').blur();
        texte.val('').removeClass('error').blur();
        formMode.val('');
    }

    function showEntries() {
        $('#formEntry').fadeOut('fast',function(){$('#entries').fadeIn('fast');});
    }

    function showForm() {
        $('#entries').fadeOut('fast',function(){$('#formEntry').fadeIn('fast');});
    }

    function activateEntries(){
        diaryEntries=document.querySelectorAll('#entries article');
        if(diaryEntries.length>0||diaryEntries !== null){
            for (var i=0;i<diaryEntries.length;i++){
                document.querySelectorAll('#entries article h2')[i].addEventListener('click',toggleSingle);
                document.querySelectorAll('#entries article .approve')[i].addEventListener('click',approuverStatut);
                document.querySelectorAll('#entries article .remove')[i].addEventListener('click',rejeterStatut);
                document.querySelectorAll('#entries article .edit')[i].addEventListener('click',chargerStatut);
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

    function toggleSingle(ev){
        var t=ev.target;
        if(hasClass(t.parentNode,'active')){
            toggleAll("off");
            removeClass(t.parentNode,'active');
        }else{
            toggleAll("off");
            addClass(t.parentNode,'active');
        }
    }

    function toggleAll(s){
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
                    toggleAll("off");
                }else{
                    toggleAll("on");
                }
            break;
        }
    }

    function rejeterStatut(ev){
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

    function approuverStatut(ev){
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

    function chargerStatut(ev){
        ev.preventDefault();

        var element = $(this).closest('article'),
            key     = element.getAttribute('data-id'),
            statut  = {};

        $.ajax({
            url      : '/posts',
            type     : 'get',
            dataType : 'json',
            data     : {
                id : key
            },
            success  : function(msg){
                afficherStatut(key,msg);
            },
            error    : function(xhr,code){
               alert(code);
            }
        });
    }

    function afficherStatut(key,donnees){
        formMode.val(key);
        auteur.val(donnees.auteur);
        texte.val(donnees.texte);
        showForm();
    }

    function modifierStatut(ev){
        ev.preventDefault();

        var key = formMode.val(),
            error = false;

        auteur.removeClass('error');
        texte.removeClass('error');

        if(auteur.val().length === 0){
            auteur.addClass('error');
            error = true;
        }

        if(texte.val().length === 0){
            texte.addClass('error');
            error = true;
        }

        if(error){
            alert('You must fill all fields');
            return;
        }

        if(error === false){

            texte.val();

            var postData = {
                timestamp : key,
                auteur    : auteur.val(),
                texte     : texte.val()
            };

            $.ajax({
                url     : 'requete.php',
                type    : 'post',
                data    : {
                    etat : postData
                },
                success : function(msg){
                    var statut = $('article[rel='+key+']');
                    $('.sAuteur',statut).text(auteur.val());
                    $('.sTexte',statut).text(texte.val());
                    showEntries();
                    resetForm();
                },
                error   : function(xhr,code){
                   alert(code);
                }
            });
        }
    }

    document.querySelector('h1').addEventListener('click',function(){
        toggleAll("maybe");
    });

    $('#btnCancel').click(function(){
        showEntries();
        resetForm();
    });

    activateEntries();
});