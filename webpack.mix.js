const { mix } = require("laravel-mix");
require("laravel-mix-merge-manifest");

var publicPath = "../../../public/themes/velocity/assets";

if (mix.inProduction()) {
    publicPath = 'publishable/assets';
}

mix.setPublicPath(publicPath).mergeManifest();
mix.disableNotifications();

mix
    .sass(
        __dirname + '/src/Resources/assets/sass/payment.scss',
        __dirname + '/' + publicPath + '/css/payment.css'
    )

    .options({
        processCssUrls: false
    });

if (mix.inProduction()) {
    mix.version();
}
