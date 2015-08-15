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

        var post   = posts[0],
            post_html  = '<article class="c-main-post -' + post.type + '" data-timestamp="' + post.timestamp + '">';

        // Post injection
        if (post.has_image) {
            post_html += '<img class="c-main-post_image" src="../uploads/' + post.image + '" alt="">';
        }

        post_html += '<div class="c-main-post_content">';

        if (post.has_text) {
            post_html += '<p class="c-main-post_text h3">' + post.text + '</p>';
        }

        post_html += '<p class="c-main-post_author h4">' + post.author + '</p>';
        post_html += '</div>';
        post_html += '</article>';

        if(split === true){
            $('.js-main-column:first-child').prepend(post_html);
        }else{
            $('.js-main-column:last-child').prepend(post_html);
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