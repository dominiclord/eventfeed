define([
    'underscore',
    'backbone'
], function (_, Backbone) {
    'use strict';

    var Post = Backbone.Model.extend({
        // Default attributes for the post
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

        validate: function (attrs, options) {
            var response = {
                status: 'OK',
                fields: []
            };

            if (attrs.author.length === 0) {
                response.fields.push('author');
                response.status = 'ERROR';
            }

            if (attrs.text.length === 0 && typeof(attrs.image === 'undefined')) {
                response.status = 'ERROR';

                if (attrs.text.length === 0) {
                    response.fields.push('text');
                } else {
                    response.fields.push('image');
                }
            }

            if (response.status !== 'OK') {
                return response;
            }

        },

        /*
        // Toggle the `completed` state of this todo item.
        toggle: function () {
            this.save({
                completed: !this.get('completed')
            });
        },
        */

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