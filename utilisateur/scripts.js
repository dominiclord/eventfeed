$(function(){
    var auteur = $('#auteur'),
        texte = $('#texte'),
        imageFile = $('#imagefile');
    $('#btnCancel').click(function(ev){
        auteur.val('').removeClass('error').blur();
        texte.val('').removeClass('error').blur();
        imageFile.val('').removeClass('error').blur();
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
            $(this).unbind('submit').submit();
            $('#overlay').show();
        }
    }
});