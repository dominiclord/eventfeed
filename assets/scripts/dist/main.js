// Variable definitions
var split       = true,
    posts       = [],
    posts_queue = [],
    options     = [],
    request_url = '/main/posts/';

$(function(){
    load_approved();
    load_options();

    window.setInterval(function(){
        load_options();
    },10000);
    window.setTimeout( refresh, get_speed() );

    $('.post img').each(function(){
        fix_width($(this));
    });
});

function refresh(){
    show_new_posts();
    window.setTimeout( refresh, get_speed() );
}
function get_speed(){
    if(options.speed > 5000){
        return options.speed;
    }else{
        return 5000;
    }
}
function show_new_posts(){
    if( !is_empty( posts[0] ) ){

        var post  = posts[0],
            sPost = '<div data-timestamp="'+post.timestamp+'" class="post '+post.type+' new">';

        //Construction du post
        switch(post.type){
            case "text":
                sPost += '<p><strong>'+post.author+'&nbsp;:</strong> '+post.text+'</p></div>';
            break;
            case "hybrid":
                sPost += '<img src="../uploads/'+post.image+'"><div class="text"><p><strong>'+post.author+'&nbsp;:</strong> '+post.text+'</p></div></div>';
            break;
            case "image":
              sPost += '<img src="../uploads/'+post.image+'"></div>';
            break;
        }

        if(split === true){
            $('.column:first-child').append(sPost);
            fix_width($('.post.new img'));
            $('.column:first-child div:last-child').fadeIn().removeClass('new');
        }else{
            $('.column:last-child').append(sPost);
            $('.post.new img').each(function(){
                fix_width($(this));
            });
            $('.column:last-child div:last-child').fadeIn().removeClass('new');
        }
        split = !split;
        posts.splice(0,1);
        publish_post(post.id);
    }else{
        load_approved();
    }
}
function is_empty(map) {
    var empty = true;
    for(var key in map){
        if(map.hasOwnProperty(key)){
            empty = false;
            break;
        }
    }
    return empty;
}
function load_approved(){
    $.ajax({
        url      : request_url,
        type     : 'get',
        dataType : 'JSON',
        success  : function(response){
            posts = response.posts;
        }
    });
}
function load_options(){
    /*
    $.ajax({
        url:request_url,
        type:'post',
        dataType:'json',
        data:'action=load_options',
        success: function(data){
            params=data;
        }
    });
*/
}
function publish_post( post_id ){
    $.ajax({
        url      : request_url + post_id,
        type     : 'PUT',
        dataType : 'JSON',
        success  : function(response){
        }
    });
}

/* Fonction pour arranger la largeur des conteneurs d'images dépendemment de la grosseur des images */
function fix_width(image){
    //Landscape
    if(image.width() > image.height() || image.width() == image.height() ){
        image.parent().css('width','100%');
    }else if(image.width() < image.height()){
        image.parent().css('width','400px');
    }
}