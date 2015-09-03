/*global define*/
define([
    'jquery',
    'underscore',
    'backbone',
    'models/postModel',
    'mustache',
    'common'
], function ($, _, Backbone, PostModel, Mustache, Common) {
    'use strict';

    // Our overall **ModerationView** is the top-level piece of UI.
    var ModerationView = Backbone.View.extend({

        el: '.js-moderation-view',

        events: {
        },

        initialize: function () {
        },

        // On form submit, attempt to save a post
        submitPostForm: function (e) {

        }
    });

    return ModerationView;
});