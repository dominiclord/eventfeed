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
        el: '.js-user-view',

        // Delegated events for creating new posts and other visual changes
        events: {
            'submit .js-user-form'  : 'submitPostForm',
            'click  .js-erase-form' : 'clearForm',
            'change #post_image'    : 'displayImageName'
        },

        initialize: function () {
            this.$user_form        = this.$('.js-user-form');

            this.$post_author      = this.$('#post_author');
            this.$post_text        = this.$('#post_text');
            this.$post_image       = this.$('#post_image');
            this.$post_image_label = this.$('#post_image_label');

            this.$no_content       = this.$('.js-no-content');

            this.$loader           = this.$('.js-loader');
            this.$loader_box       = this.$('.js-loader-box');

            this.Post = new PostModel();

            this.listenTo(this.Post, 'sync', this.displaySyncResponse);
            this.listenTo(this.Post, 'request', this.displayLoader);
            this.listenTo(this.Post, 'invalid', this.displayFormErrors);
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
            var self = this;

            e.preventDefault();

            // Clean the form of errors
            self.$post_author.removeClass('has-error');
            self.$post_text.removeClass('has-error');
            this.$no_content.addClass('none');

            var attributes = self.newAttributes();

            self.Post.set(attributes);

            // If we have an image, make sure to convert it to base64 before attempting to save the data
            if (typeof(attributes.image) !== 'undefined') {
                self.Post.readImage(attributes.image, function() {
                    self.Post.save();
                });
            } else {
                self.Post.save();
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

        /**
         * Display erros in form on `invalid` event
         * @param  {Object}  model  A copy of the submitted model
         * @param  {Object}  error  A custom response object
         */
        displayFormErrors: function (model, error) {
            this.$no_content.removeClass('none');

            for (var i = 0, len = error.fields.length; i < len; i++) {
                this['$post_' + error.fields[i]].addClass('has-error');
            }
        },

        displayLoader: function (model, response, options) {
            $('body').addClass('is-transmitting-post');
            //this.$loader_box.addClass('animated');
        },

        displaySyncResponse: function () {
            var self = this;

            window.setTimeout(function(){
                $('body')
                    .addClass('is-transmission-completed')
                    .one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend',
                        function() {
                            ('body')
                                .removeClass('is-transmitting-post is-transmission-completed')
                                .unbind('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend');
                        });
                //self.$loader_box.removeClass('fadeInUp animated');
            }, 2000);

            window.setTimeout(function(){
                var rendered = Mustache.to_html(successTemplate, self.Post.toJSON());
                self.$user_form.html(rendered);
            }, 1000);
        }
    });

    return UserView;
});