define([
    'underscore',
    'backbone'
], function (_, Backbone) {
    'use strict';

    var Post = Backbone.Model.extend({
        // Default attributes for the todo
        // and ensure that each todo created has `title` and `completed` keys.
        defaults: {
            title: '',
            completed: false,
            author: '',
            message: '',
            timestamp: 0
        },

        url: function () {
            return this.urlRoot;
        },

        urlRoot: '/posts',

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