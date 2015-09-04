/*global define*/
define([
    'jquery',
    'underscore',
    'backbone',
    'collections/postsCollection',
    'views/moderationPostsView',
    'mustache',
    'common'
], function ($, _, Backbone, PostsCollection, ModerationPostsView, Mustache, Common) {
    'use strict';

    // Our overall **ModerationView** is the top-level piece of UI.
    var ModerationView = Backbone.View.extend({

        el: '.js-moderation-view',

        events: {

        },

        initialize: function () {
            this.$postList = this.$('.js-post-list');

            this.listenTo(PostsCollection, 'reset', this.addAll);
            this.listenTo(PostsCollection, 'all', _.debounce(this.render, 0));

            PostsCollection.fetch({reset:true});
        },

        render: function () {
            if (PostsCollection.length) {
                //$('main').hide();

                /*
                this.$footer.html(this.template({
                    completed: completed,
                    remaining: remaining
                }));
                */
            }
        },

        // Add a single post item to the list by creating a view for it, and
        // appending its element to the `<ul>`.
        addOne: function (post) {
            var view = new ModerationPostsView({ model: post });
            this.$postList.append(view.render().el);
        },

        // Add all items in the **Posts** collection at once.
        addAll: function () {
            this.$postList.empty();
            PostsCollection.each(this.addOne, this);
        }

    });

    return ModerationView;
});