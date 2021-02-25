var Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('js/bootstrap-datepicker', './assets/js/bootstrap-datepicker.js')
    .addEntry('js/bootstrap-datepicker.fr', './assets/js/bootstrap-datepicker.fr.js')
    .addEntry('js/Chart.bundle.min', './assets/js/Chart.bundle.min.js')
    .addEntry('js/html2pdf.bundle', './assets/js/html2pdf.bundle.js')
    .addEntry('js/app', './assets/js/app.js')
    .addEntry('app', './assets/app.js')
    .addEntry('bootstrap', './assets/bootstrap.js')

    .addStyleEntry('css/bootstrap-datepicker', './assets/css/bootstrap-datepicker.css')
    .addStyleEntry('css/Chart.min', './assets/css/Chart.min.css')

    .addStyleEntry('css/app', './assets/css/app.scss')
    .addStyleEntry('css/login', './assets/css/login.scss')
    .addStyleEntry('css/index', './assets/css/index.scss')
    .addStyleEntry('css/admin', './assets/css/admin.scss')
    .addStyleEntry('css/dashboard', './assets/css/dashboard.scss')
    .addStyleEntry('css/report', './assets/css/report.scss')
    .addStyleEntry('css/report_pdf', './assets/css/report_pdf.scss')
    .addStyleEntry('css/timeline', './assets/css/timeline.scss')

    .addEntry('recipes/main', './assets/js/recipes.js')
    .addEntry('recipe/main', './assets/js/recipe.js')

    // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
    .enableStimulusBridge('./assets/controllers.json')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    .enableTypeScriptLoader()

    // uncomment if you use React
    //.enableReactPreset()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()

    .enableVueLoader()
;

module.exports = Encore.getWebpackConfig();
