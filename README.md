EventFeed
========
##Description
Live visual feed for public events

Code quality suggestions are very welcome.

##Disclaimer
This application is being rewritten with Flask/Python. It is not in a totally functionnal state, and I will write better documentation and comments when it is up and running in its new language. The functional PHP code has been left mostly untouched.

##Usage
Requires Vagrant and Virtualbox.

Change directory to eventfeed root and run these commands

    vagrant up
    vagrant ssh
    cd /vagrant
    python eventfeed.py

You should now be able to acces the app at the following :

[Main interface for public viewing](http://192.168.56.101:5000/main)

[Mobile page for user submissions](http://192.168.56.101:5000)

[Main moderation interface](http://192.168.56.101:5000/moderation)

##TODO
* Finish code migration
* Documentation
* Code comments
* Rework front-end
    * Vanilla JS
* Implement minification,preprocessing, general watch tasks (Gulp or Grunt)
* Retire old code
* Look into better database solutions
* Login for moderation access
* See about websockets for main interface