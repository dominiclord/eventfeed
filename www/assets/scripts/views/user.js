/*global define*/
define([
    'jquery',
    'underscore',
    'backbone',
    'models/post',
    //'collections/todos',
    //'views/todos',
    //'text!templates/stats.html',
    'common'
//], function ($, _, Backbone, Todos, TodoView, statsTemplate, Common) {
], function ($, _, Backbone, PostModel, Common) {
    'use strict';

    // Our overall **UserView** is the top-level piece of UI.
    var UserView = Backbone.View.extend({

        // Instead of generating a new element, bind to the existing skeleton of
        // the App already present in the HTML.
        el: '.js-user-form',

        // Compile our stats template
        //template: _.template(statsTemplate),

        // Delegated events for creating new items, and clearing completed ones.
        events: {
            'submit': 'submitPostForm',
            'click .js-erase-form': 'clearForm',
            'change #post_image': 'displayImageName'
        },

        // At initialization we bind to the relevant events on the `Todos`
        // collection, when items are added or changed. Kick things off by
        // loading any preexisting todos that might be saved in *localStorage*.
        initialize: function () {
            this.$post_author      = this.$('#post_author');
            this.$post_text        = this.$('#post_text');
            this.$post_image       = this.$('#post_image');
            this.$post_image_label = this.$('#post_image_label');
            this.$no_content       = this.$('.js-no-content');
        },

        // Re-rendering the App just means refreshing the statistics -- the rest
        // of the app doesn't change.
        render: function () {
            /*
            var completed = Todos.completed().length;
            var remaining = Todos.remaining().length;

            if (Todos.length) {
                this.$main.show();
                this.$footer.show();

                this.$footer.html(this.template({
                    completed: completed,
                    remaining: remaining
                }));

                this.$('#filters li a')
                    .removeClass('selected')
                    .filter('[href="#/' + (Common.TodoFilter || '') + '"]')
                    .addClass('selected');
            } else {
                this.$main.hide();
                this.$footer.hide();
            }

            this.allCheckbox.checked = !remaining;
            */
        },

        // Generate the attributes for a new Todo item.
        newAttributes: function () {
            return {
                title: this.$input.val().trim(),
                order: Todos.nextOrder(),
                completed: false
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
                this.$el.unbind('submit').submit();
            }

            //Todos.create(this.newAttributes());
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
        }
    });

    return UserView;
});