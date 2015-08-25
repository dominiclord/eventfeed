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
            text: '',
            image: '',
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

        /**
         * Helper function to get around AJAX's file uploading limitations
         * @see    http://stackoverflow.com/a/17537376
         * @param  {File}     file     File object
         * @param  {Function} callback Function to run after upload is complete
         * @return {Event}             FileReader's ProgressEvent
         */
        readImage: function (file, callback) {
            // File API object for reading a file locally
            var reader = new FileReader();

            reader.onloadend = (function (self, callback) {
                return function (event) {
                    // Set the file data correctly on the Backbone model
                    self.set({image : event.target.result});
                    // Handle anything else you want to do after parsing the file and setting up the model.
                    callback();
                };
            })(this, callback);

            // Reads file into memory Base64 encoded
            reader.readAsDataURL(file);
        }
    });

    return Post;
});