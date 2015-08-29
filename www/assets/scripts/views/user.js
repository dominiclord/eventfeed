/*global define*/
define([
    'jquery',
    'underscore',
    'backbone',
    'models/post',
    'text!../../templates/user_success.mustache',
    'mustache',
    'common'
//], function ($, _, Backbone, Todos, TodoView, statsTemplate, Common) {
], function ($, _, Backbone, PostModel, successTemplate, Mustache, Common) {
    'use strict';

    // Our overall **UserView** is the top-level piece of UI.
    var UserView = Backbone.View.extend({

        // Instead of generating a new element, bind to the existing skeleton of
        // the App already present in the HTML.
        el: '.js-user-form',

        // Delegated events for creating new items, and clearing completed ones.
        events: {
            'submit': 'submitPostForm',
            'click .js-erase-form': 'clearForm',
            'change #post_image': 'displayImageName'
        },

        initialize: function () {
            this.$post_author      = this.$('#post_author');
            this.$post_text        = this.$('#post_text');
            this.$post_image       = this.$('#post_image');
            this.$post_image_label = this.$('#post_image_label');
            this.$no_content       = this.$('.js-no-content');

            this.Post = new PostModel();

            this.listenTo(this.Post, 'sync', this.displayCreatedPost);

        },

        // Generate the attributes for a new Todo item.
        newAttributes: function () {
            return {
                author: this.$('#post_author').val(),
                text: this.$('#post_text').val(),
                image: this.$post_image[0].files[0]
            };
        },

        // On form submit, attempt to submit a post
        submitPostForm: function (e) {
            e.preventDefault();

            var error = false;

            this.$post_author.removeClass('has-error');
            this.$post_text.removeClass('has-error');

            this.$no_content.addClass('none');

            if (this.$post_author.val().length === 0) {
                this.$post_author.addClass('has-error');
                error = true;
            }

            if (this.$post_text.val().length === 0 && this.$post_image.val() === '') {
                this.$post_text.addClass('has-error');
                this.$no_content.removeClass('none');
                error = true;
            }

            if (error === false) {
                var attributes = this.newAttributes();

                this.Post.set(attributes);

                // If we have an image, make sure to convert it to base64 before attempting to save the data
                if (typeof(attributes.image) !== 'undefined') {
                    this.Post.readImage(attributes.image, function() {
                        this.Post.save();
                    });
                } else {
                    this.Post.save();
                }
            }

        },

        // Clear all form fields
        clearForm: function () {
            this.$post_author.val('').removeClass('has-error').blur();
            this.$post_text.val('').removeClass('has-error').blur();
            this.$post_image.val('').removeClass('has-error').blur();
            this.$post_image_label.text('Chose an image');
            this.$no_content.addClass('none');
            return false;
        },

        // Patching the default file input to make it prettier
        displayImageName: function () {
            var file_name = this.$post_image[0].files[0].name;
            this.$post_image_label.text(file_name);
        },

        displayCreatedPost: function () {
            var rendered = Mustache.to_html(successTemplate, this.Post.toJSON());

            this.$el.html(rendered);
        }
    });

    return UserView;
});