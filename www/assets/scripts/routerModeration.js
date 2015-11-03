// Filename: router.js
define([
    'jquery',
    'underscore',
    'backbone',
], function ($, _, Backbone) {

    var moderationRouter = Backbone.Router.extend({
        routes: {
            // Pages
            '': 'index',
            'edit/:id': 'editModerationPost'
        }
    });


    var initialize = function(options){

        var appView = options.appView,
            router = new moderationRouter();

        router.on('route:index', function () {
        });

        router.on('route:editModerationPost', function (id) {
            console.log('shit');
            console.log(id);
        });

        Backbone.history.start({
            pushState: true,
            root: '/moderation'
        });
    };

    return {
        initialize: initialize
    };
});