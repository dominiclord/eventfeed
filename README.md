EventFeed
========
##Description
Live visual feed for public events

## Disclaimer
This application is being rewritten from a simpler and badly organized 2012 version. It's still quite a ways away from v1.0

## How to install

To start a Charcoal project with this Boilerplate, simply:

1. **Clone the repositoy**
   - `$ git clone https://github.com/dominiclord/eventfeed`
2. **Set up a database storage**
   - Using [`eventfeed.sql`](eventfeed.sql)
3. **Install dependencies**
   - `$ composer install`


You should now be able to acces the app at the following :

- example.com : Interface for user submissions
- example.com/**main** : Main interface for public viewing
- example.com/**moderation** : Main moderation interface

## Dependencies

EventFeed depends on:

- PHP 5.5+
- [Slim](http://www.slimframework.com/) (mainly used for routing)
- [Mustache](https://github.com/bobthecow/mustache.php) (templating)
- [Slim-Mustache](https://github.com/Dearon/Slim-Mustache) (Mustache integration in Slim)
- [notorm](https://github.com/vrana/notorm) (database management)

See [`composer.json`](composer.json) for details

## Build systems

- [Composer](https://getcomposer.org/)
- [Grunt](http://gruntjs.com/)

## TODO
* Missing feed options management and loading
* Cleanup old code
* Backend
    * Implement a better code structure
    * Database class
    * Login for moderation access
    * Split data access to an API
* Frontend
    * Implement a better code structure
    * Build system
    * Redesign
    * Websockets for main interface
* Much more documentation / comments