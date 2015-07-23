$(function(){
    var author = $('#author'),
        text = $('#text'),
        imageFile = $('#imagefile');
    $('#btnCancel').click(function(ev){
        author.val('').removeClass('error').blur();
        text.val('').removeClass('error').blur();
        imageFile.val('').removeClass('error').blur();
        $('#nocontent').hide();
    });
    $('#formEntry').submit(formSubmitted);
    function formSubmitted(ev){
        ev.preventDefault();
        var timestamp = $('#timestamp'),
            image = $('#image');
        var error = false;
        author.removeClass('error');
        text.removeClass('error');
        $('#nocontent').hide();
        if(author.val().length==0){
            author.addClass('error');
            error = true;
        }
        if(text.val().length==0 && imageFile.val() == ''){
            text.addClass('error');
            $('#nocontent').fadeIn();
            error = true;
        }
        if(error == false){
            $(this).unbind('submit').submit();
            $('#overlay').show();
        }
    }
});