/*global require*/
'use strict';

// Require.js allows us to configure shortcut alias
require.config({
    // The shim config allows us to configure dependencies for
    // scripts that do not call define() to register a module
    shim: {
        underscore: {
            exports: '_'
        },
        backbone: {
            deps: [
                'underscore',
                'jquery'
            ],
            exports: 'Backbone'
        }
    },
    paths: {
        jquery: '../../../node_modules/jquery/dist/jquery',
        underscore: '../../../node_modules/underscore/underscore',
        backbone: '../../../node_modules/backbone/backbone',
        text: '../../../node_modules/requirejs-text/text',
        mustache: '../../../node_modules/mustache/mustache.min'
    }
});

require([
    'backbone',
    'views/moderationView'
], function (Backbone, ModerationView) {
    // Initialize the application view
    new ModerationView();
});