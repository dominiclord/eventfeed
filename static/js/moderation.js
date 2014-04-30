// Variable definitions
var posts='',
    postsArray = new Array,
    author,
    text,
    formMode,
    requesturl = 'request';
$(function(){
    // --- APPLICATION INITIALIZATION ---

    author = $('#author')
    text = $('#text')
    formMode = $('#formMode')

    $('#postform').submit(editPost);
    document.querySelector('h1').addEventListener('click',function(){toggleAll("maybe")});
    $('#btnCancel').click(function(){showPosts();resetForm();});
    activatePosts();
});
function resetForm(){
    author.val('').removeClass('error').blur();
    text.val('').removeClass('error').blur();
    formMode.val('');
}
function showPosts(){
    $('#postform').fadeOut('fast',function(){$('#posts').fadeIn('fast');});
}
function showForm(){
    $('#posts').fadeOut('fast',function(){$('#postform').fadeIn('fast');});
}
// --- Interaction with posts ---
function activatePosts(){
    posts=document.querySelectorAll('#posts article');
    if(posts.length>0||posts !== null){
        for(var i=0;i<posts.length;i++){
            document.querySelectorAll('#posts article h2')[i].addEventListener('click',toggleSingle);
            document.querySelectorAll('#posts article .approve')[i].addEventListener('click',approvePost);
            document.querySelectorAll('#posts article .remove')[i].addEventListener('click',rejectPost);
            document.querySelectorAll('#posts article .edit')[i].addEventListener('click',loadPost);
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
        for(var i=0;i<posts.length;i++){
            removeClass(posts[i],'active');
        }
      break;
      case "on":
        for(var i=0;i<posts.length;i++) {
            addClass(posts[i],'active');
        }
      break;
      case "maybe":
        var actives=0;
        for (var i=0;i<posts.length;i++) {
            if(hasClass(posts[i],'active')){
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
/*******************************************************************************************************************/
function rejectPost(ev){
    ev.preventDefault();
    var post = $(this).closest('article'),
        key = post.attr('rel');
    $.ajax({
        url:requesturl,
        type:'post',
        dataType:'html',
        data:'etat=Rejeter&timestamp='+key,
        success: function(msg){
            post.fadeOut();
        },
        error: function(xhr,code){
            alert(code);
        }
    });
}
function approvePost(ev){
    ev.preventDefault();
    var el = $(this).closest('article'),
        key = el.attr('rel');
    $.ajax({
        url:requesturl,
        type:'post',
        dataType:'json',
        data:'state=approve&timestamp='+key,
        success: function(msg){
            el.fadeOut();
        },
        error: function(xhr,code){
            alert(code);
        }
    });
}
function loadPost(ev){
    ev.preventDefault();
    var el = $(this).closest('article'),
        key = el.attr('rel');
    $.ajax({
        url:requesturl,
        type:'post',
        dataType:'json',
        data:'state=load&timestamp='+key+'',
        success:function(msg){
            showPost(key,msg)
        },
        error:function(xhr,code){
           alert(code);
        }
    });
}
function showPost(key,data){
    formMode.val(key)
    author.val(data.author);
    text.val(data.text);
    showForm();
}
function editPost(ev){
    ev.preventDefault();
    var key = formMode.val(),
        error = false;
    author.removeClass('error');
    text.removeClass('error');
    if(author.val().length==0){
      author.addClass('error');
      error = true;
    }
    if(error){
      alert('You must fill all fields');
      return;
    }
    if(error == false){
        var postData = {
            "timestamp":key,
            "author":author.val(),
            "text":text.val()
        }
        $.ajax({
            url:requesturl,
            type:'post',
            data:'state=edit&timestamp='+key+'&author='+author.val()+'&text='+text.val(),
            success: function(msg){
                var post = $('article[rel='+key+']');
                $('.author',post).text(author.val());
                $('.text',post).text(text.val());
                showPosts();
                resetForm();
            },
            error: function(xhr,code){
               alert(code);
            }
        });
    }
}