/*global define */
define([
    'underscore',
    'backbone',
    'models/post'
], function (_, Backbone, Post) {
    'use strict';

    var PostsCollection = Backbone.Collection.extend({
        // Reference to this collection's model.
        model: Post,

        // Filter down the list of all todo items that are finished.
        completed: function () {
            return this.where({completed: true});
        },

        // Filter down the list to only todo items that are still not finished.
        remaining: function () {
            return this.where({completed: false});
        }
    });

    return new PostsCollection();
});