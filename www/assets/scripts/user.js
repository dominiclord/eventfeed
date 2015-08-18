//Load common code that includes config, then load the app logic for this page.
require(['./common','app/model/Post'], function (common, Post) {
    //requirejs(['app/user']);
    var posts = [
        new Post('Author name','The message','1895165465')
    ];

    for (var i = 0, len = posts.length; i < len; i++) {
        console.log(posts[i].get_author());
    }

});