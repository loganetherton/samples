@servers(['development' => 'dev@meerkat.appraisalscope.com', 'staging' => 'staging@meerkat.appraisalscope.com'])

@task('deploy:dev', ['on' => 'development'])
    cd code;
    git pull origin {{ $branch }};
    cd backend;
    composer install;
	composer dump-autoload -o;
    phpunit;
    php artisan migrate;
    php artisan optimize;
    cd ../frontend;
    bower install;
    npm install;
    grunt test;
    grunt build;
@endtask

@task('deploy:staging', ['on' => 'staging'])
    cd code;
    git pull origin {{ $branch }};
    cd backend;
    composer install;
	composer dump-autoload -o;
    phpunit;
    php artisan migrate;
    php artisan optimize;
    cd ../frontend;
    bower install;
    npm install;
    grunt test;
    grunt build;
@endtask