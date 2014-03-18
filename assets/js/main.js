window.alert = function() {};
$(function () {
    var split = true;
    var posts = [];
    var posts_queue = [];
    var params = [];
    chargerApprouves();
    chargerParametres(true);

    window.setInterval(function() {
        chargerParametres();
    },10000);

    /*function lancerRefresh(){
        window.setInterval(function() {
            afficherNouveauStatut();
            alert(getVitesse());
        },getVitesse());
    }*/
    function lancerRefresh() {
        afficherNouveauStatut();
        window.setTimeout(lancerRefresh, getVitesse());
    }
    window.setTimeout(lancerRefresh, getVitesse());
    function getVitesse(){
        if(params.vitesse > 5000){
            return params.vitesse;
        }else{
            return 5000;
        }
    }
    function afficherNouveauStatut(){
        if(!isEmpty(posts)){
            var post = posts[0];
            var sPost = '<div data-timestamp="'+post.timestamp+'" class="post '+post.type+' new">';
            //Construction du post
            switch(post.type){
                case "text":
                    sPost += '<p><strong>'+post.author+'&nbsp;:</strong> '+post.text+'</p></div>';
                break;
                case "hybrid":
                    sPost += '<img src="../utilisateur/uploads/'+post.image+'"><div class="texte"><p><strong>'+post.author+'&nbsp;:</strong> '+post.text+'</p></div></div>';
                break;
                case "image":
                  sPost += '<img src="../utilisateur/uploads/'+post.image+'"></div>';
                break;
            }
            if(split == true){
                $('.column:first-child').append(sPost);
                widthFix($('.post.new img'));
                $('.column:first-child div:last-child').fadeIn().removeClass('new');
            }else{
                $('.column:last-child').append(sPost);
                $('.post.new img').each(function(){widthFix($(this));});
                $('.column:last-child div:last-child').fadeIn().removeClass('new');
            }
            split=!split;
            posts.splice(0,1);
            publierStatut(post.timestamp);
        }else{
            chargerApprouves();
        }
    }
    function isEmpty(map) {
        var empty = true;
        for(var key in map){
            if(map.hasOwnProperty(key)){empty=false;break;}
        }
        return empty;
    }
    function chargerApprouves(){
        $.ajax({
            url:'/principale/requete.php',
            type:'post',
            dataType:'json',
            data:'etat=Charger',
            success: function(donnees){
                posts=donnees;
            }
        });
    }
    function chargerParametres(premier){
        premier = typeof premier !== 'undefined' ? premier : false;
        $.ajax({
            url:'/principale/requete.php',
            type:'post',
            dataType:'json',
            data:'etat=Parametres',
            success: function(donnees){
                params=donnees;
            }
        });
    }
    function publierStatut(key){
        $.ajax({
            url:'/principale/requete.php',
            type:'post',
            data:'etat=Publier&timestamp='+key,
            success: function(donnees){}
        });
    }
    $('.post img').each(function(){
        widthFix($(this));
    });
});
/* Fonction pour arranger la largeur des conteneurs d'images dépendemment de la grosseur des images */
function widthFix(image){
    //Landscape
    if(image.width() > image.height() || image.width() == image.height() ){
        image.parent().css('width','100%');
    }else if(image.width() < image.height()){
        image.parent().css('width','400px');
    }
}