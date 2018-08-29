Hello, my name is Logan Etherton. Welcome to my samples!

I have placed in this repository directories containing code which I have created over the years. For the most part, I was the primary developer, or else the lead developer, for most of these. Although these files are no longer under NDA, they are copyrighted, and not intended for use or distribution.

`cardquiry-gcmgr-backend` is a Node server written using Express. Although it was originally written in ES5, it was eventually transitioned to ES6/7, and finally to TypeScript. It contains a wrapper for Mocha/Chai unit tests, and has been battle-tested and has successfully served many million requests over the years. The application utilizes MongoDB for the primary database, connected via Mongoose.

`valuepad-frontend` is a React application which served as the frontend for a PHP REST backend. It resides behind a simple Express server. Routing is handled byReact Router, and the data layer is handled by Redux.

`valuepad-backend` is a PHP REST application written in Laravel. There is very complete test coverage using PHPUnit. The application communited with a MySQL-compatible database (in production, it was Aurora, and in development, it was MySQL).

`daemon` contains a PHP daemon which utilized the Yii2 library and is intended to serve as a base class against which all application daemons are created.

`deployment` contains a sample Ansible deployment script that initiates, builds, and run multiple services and applications intended to work in tandem. It contains setup scripts for frontend servers, backend servers, Elasticsearch servers, ElasticSearch scripts, MongoDB servers, and Redis servers.
