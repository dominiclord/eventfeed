define([
    'underscore',
    'backbone'
], function (_, Backbone) {
    'use strict';

    var Post = Backbone.Model.extend({
        // Default attributes for the todo
        // and ensure that each todo created has `title` and `completed` keys.
        defaults: {
            author: '',
            message: '',
            timestamp: ''
        },

        urlRoot: '/api/v1/posts',

        url: function () {
            return this.urlRoot;
        },

        // Toggle the `completed` state of this todo item.
        toggle: function () {
            this.save({
                completed: !this.get('completed')
            });
        },

        get_author: function () {
            return this.author;
        }

    });

    return Post;
});