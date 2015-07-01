// Variable definitions
var split = true,
    posts = [],
    posts_queue = [],
    options = [],
    requesturl = 'request';

$(function(){
    loadApproved();
    loadOptions(true);

    window.setInterval(function(){
        loadOptions();
    },10000);
    window.setTimeout(refresh, getspeed());

    $('.post img').each(function(){
        widthFix($(this));
    });
});

function refresh(){
    showNewPosts();
    window.setTimeout(refresh, getspeed());
}
function getspeed(){
    if(options.speed > 5000){
        return options.speed;
    }else{
        return 5000;
    }
}
function showNewPosts(){
    if(!isEmpty(posts[0])){
        var post = posts[0],
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
        publishPost(post.timestamp);
    }else{
        loadApproved();
    }
}
function isEmpty(map) {
    var empty = true;
    for(var key in map){
        if(map.hasOwnProperty(key)){empty=false;break;}
    }
    return empty;
}
function loadApproved(){
    $.ajax({
        url:requesturl,
        type:'post',
        dataType:'json',
        data:'action=loadapproved',
        success:function(data){
            posts=data.posts;
        }
    });
}
function loadOptions(first){
    first = typeof first !== 'undefined' ? first : false;
    $.ajax({
        url:requesturl,
        type:'post',
        dataType:'json',
        data:'action=loadoptions',
        success: function(data){
            params=data;
        }
    });
}
function publishPost(key){
    $.ajax({
        url:requesturl,
        type:'post',
        data:'action=publishpost&timestamp='+key,
        success:function(data){

        }
    });
}

/* Fonction pour arranger la largeur des conteneurs d'images dépendemment de la grosseur des images */
function widthFix(image){
    //Landscape
    if(image.width() > image.height() || image.width() == image.height() ){
        image.parent().css('width','100%');
    }else if(image.width() < image.height()){
        image.parent().css('width','400px');
    }
}