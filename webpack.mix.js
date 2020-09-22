const { mix } = require("laravel-mix");
require("laravel-mix-merge-manifest");

var publicPath = "../../../public/themes/velocity/assets";

if (mix.inProduction()) {
    publicPath = 'publishable/assets';
}

mix.setPublicPath(publicPath).mergeManifest();
mix.disableNotifications();

mix
    .js(
        __dirname + "/src/Resources/assets/js/app.js",
        "js/kewi.js"
    )

    .copyDirectory(__dirname + "/src/Resources/assets/icomoon", publicPath + "/icomoon")
    .copyDirectory(__dirname + "/src/Resources/assets/fonts", publicPath + "/fonts")

    .sass(
        __dirname + '/src/Resources/assets/sass/admin.scss',
        __dirname + '/' + publicPath + '/css/kewi-admin.css'
    )
    .sass(
        __dirname + '/src/Resources/assets/sass/app.scss',
        __dirname + '/' + publicPath + '/css/kewi.css', {
            includePaths: ['node_modules/bootstrap-sass/assets/stylesheets/'],
        }
    )

    .options({
        processCssUrls: false
    });

if (mix.inProduction()) {
    mix.version();
}
