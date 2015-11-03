/*global define*/
define([
    'jquery',
    'underscore',
    'backbone',
    'text!../../templates/moderation_post.mustache',
    'text!../../templates/moderation_form.mustache',
    'mustache',
    'common'
], function ($, _, Backbone, postTemplate, formTemplate, Mustache, Common) {
    'use strict';

    var ModerationPostView = Backbone.View.extend({

        tagName: 'li',
        className: 'c-post-list_item',

        // The DOM events specific to an item.
        events: {
            'click .js-editPost': 'edit',
            //'click .js-approve-post': 'approve',
            //'click .js-reject-post': 'reject'
            /*
            'dblclick label':   'edit',
            'click .destroy':   'clear',
            'keypress .edit':   'updateOnEnter',
            'keydown .edit':    'revertOnEscape',
            'blur .edit':       'close'
            */
        },

        // The TodoView listens for changes to its model, re-rendering. Since there's
        // a one-to-one correspondence between a **Todo** and a **TodoView** in this
        // app, we set a direct reference on the model for convenience.
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
            //this.listenTo(this.model, 'destroy', this.remove);
            /*
            this.listenTo(this.model, 'visible', this.toggleVisible);
            */
        },

        // Re-render the titles of the todo item.
        render: function () {
            this.$el.html(Mustache.to_html(postTemplate, this.model.toJSON()));

            //this.$el.toggleClass('completed', this.model.get('completed'));

            //this.toggleVisible();
            //this.$input = this.$('.edit');
            return this;
        },

        // Navigate router to edit the post
        edit: function (event) {
            var href = 'edit/' + this.model.id;

            Backbone.history.navigate(href, {trigger: true});

            //this.$el.addClass('editing');
            //this.$input.focus();
        },

        approve: function () {
            //this.$el.addClass('editing');
            //this.$input.focus();
        },

        // Close the `"editing"` mode, saving changes to the todo.
        close: function () {
            // var value = this.$input.val();
            // var trimmedValue = value.trim();

            // if (trimmedValue) {
            //     this.model.save({ title: trimmedValue });

            //     if (value !== trimmedValue) {
            //         // Model values changes consisting of whitespaces only are not causing change to be triggered
            //         // Therefore we've to compare untrimmed version with a trimmed one to chech whether anything changed
            //         // And if yes, we've to trigger change event ourselves
            //         this.model.trigger('change');
            //     }
            // } else {
            //     this.clear();
            // }

            // this.$el.removeClass('editing');
        },

        // If you hit `enter`, we're through editing the item.
        updateOnEnter: function (e) {
            // if (e.keyCode === Common.ENTER_KEY) {
            //     this.close();
            // }
        },

        // If you're pressing `escape` we revert your change by simply leaving
        // the `editing` state.
        revertOnEscape: function (e) {
            // if (e.which === Common.ESCAPE_KEY) {
            //     this.$el.removeClass('editing');
            //     // Also reset the hidden input back to the original value.
            //     this.$input.val(this.model.get('title'));
            // }
        }
    });

    return ModerationPostView;
});