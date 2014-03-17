$(function(){
    var auteur = $('#auteur'),
        texte = $('#texte'),
        etat = $('#etat'),
        imageFile = $('#imagefile');
    $('body').append("<section id='container-succes'><h3>Merci! Votre statut a été envoyé.</h3><h3>Il passera à travers un processus de validation et s'affichera bientôt s'il est approuvé par le comité organisateur!</h3><h3>Bonne soirée!</h3><h3><a id='recommencer' href='#'>&laquo;Retour</a></h3></section>")
    $('#recommencer').click(function(ev){
        ev.preventDefault();
        showForm();
    });
    $('#btnCancel').click(function(ev){
        auteur.removeClass('error');
        texte.removeClass('error');
        $('#nocontent').hide();
    });
    $('#formEntry').submit(formSubmitted);
    function formSubmitted(ev){
        ev.preventDefault();
        var timestamp = $('#timestamp'),
            image = $('#image');
        var error = false;
        auteur.removeClass('error');
        texte.removeClass('error');
        $('#nocontent').hide();
        if(auteur.val().length==0){
            auteur.addClass('error');
            error = true;
        }
        if(texte.val().length==0 && imageFile.val() == ''){
            texte.addClass('error');
            $('#nocontent').fadeIn();
            error = true;
        }
        if(error == false){
            $(this).unbind('submit').submit()
        }
    }
    function resetForm(){
        auteur.val('').removeClass('error').blur();
        texte.val('').removeClass('error').blur();
        imageFile.val('').removeClass('error').blur();
        $('#progress .bar').css('width','0');
        $('#timestamp').val('');
        $('#image').val('');
    }
    function showSucces() {
        $('#formEntry').fadeOut('fast',function(){$('#container-succes').fadeIn('fast');});
    }
    function showForm() {
        $('#container-succes').fadeOut('fast',function(){$('#formEntry').fadeIn('fast');});
    }
    function soumettreStatut(statut){
        $.ajax({
            url:'soumettre.php',
            type:'post',
            dataType:'html',
            data:'mode=ajax&etat=Envoyer&auteur='+statut.auteur+'&texte='+statut.texte+'&timestamp='+statut.timestamp+'&image='+statut.image,
            success: function(msg){
                return true;
            }//,
            //error: function(xhr,code){
            //    alert(code);
            //    return false;
            //}
        });
        return true;
    }

});