var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function (mix) {
    var bower = 'bower_components',
        node = 'node_modules';

    mix.sass('app.scss');

    mix.styles([
        'bootstrap/dist/css/bootstrap.css',
        bower + '/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css'
    ], 'public/css/vendor.css', bower);

    mix.babel(['/global/*.js', '/*/**/*.js', 'main.js'], 'public/js/app.js');

    mix.scripts([
        bower + '/jquery/dist/jquery.js',
        bower + '/jquery-pjax/jquery.pjax.js',
        bower + '/bootstrap/dist/js/bootstrap.js',
        node + '/vue/dist/vue.js',
        node + '/vue-resource/dist/vue-resource.min.js',
        bower + '/moment/min/moment-with-locales.min.js',
        bower + '/moment-timezone/builds/moment-timezone-with-data.min.js',
        bower + '/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js'
    ], 'public/js/vendor.js', './');

    mix.version(['css/app.css', 'js/app.js', 'css/vendor.css', 'js/vendor.js']);

    mix.copy(bower+'/bootstrap/dist/fonts', 'public/fonts')
       .copy(bower+'/bootstrap/dist/fonts', 'public/build/fonts');

    mix.browserSync({
        // Uncomment the following line and set the hostname you're using to run
        // the application if it's not running using the default homestead hostname
        // proxy: 'homestead.app'
    });
});
