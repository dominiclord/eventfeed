/*global define */
define([
    'underscore',
    'backbone',
    'models/postModel'
], function (_, Backbone, postModel) {
    'use strict';

    var PostsCollection = Backbone.Collection.extend({
        // Reference to this collection's model.
        model: postModel,

        url: function () {
            return '/api/v1/posts';
        }
    });

    return new PostsCollection();
});