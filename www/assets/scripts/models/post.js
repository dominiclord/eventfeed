define(function () {

    function Post(author, message, timestamp) {
        this.author = author;
        this.message = message;
        this.timestamp = timestamp;
    }

    Post.prototype = {
        get_author: function () {
            return this.author;
        }
    };

    return Post;
});