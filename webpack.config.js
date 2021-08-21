const Encore = require('@symfony/webpack-encore');
const webpack = require("webpack");

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
    .addEntry('app', './assets/app.js')
    .addEntry('datatables', './assets/js/theme/datatables.js')
    .addEntry('zzz', './assets/js/theme/zzz.js')

    .addEntry('app_user_settings_apitokens', './assets/js/user/settings/apitokens.js')

    .addEntry('omh', './assets/js/omh.js')
    .addEntry('omh_user_settings_households', './assets/js/user/settings/households.js')

    .addEntry('housekeepingbook_periodicbooking_index', './assets/js/housekeepingbook/periodicbooking_index.js')
    .addEntry('housekeepingbook_periodicbooking_form', './assets/js/housekeepingbook/periodicbooking_form.js')

    .addEntry('housekeepingbook_accountholder_index', './assets/js/housekeepingbook/accountholder_index.js')
    .addEntry('housekeepingbook_bookingcategory_index', './assets/js/housekeepingbook/bookingcategory_index.js')

    .addEntry('housekeepingbook_asset_account_index', './assets/js/housekeepingbook/account/asset_index.js')
    .addEntry('housekeepingbook_asset_account_form', './assets/js/housekeepingbook/account/asset_form.js')

    .addEntry('housekeepingbook_revenue_account_index', './assets/js/housekeepingbook/account/revenue_index.js')
    .addEntry('housekeepingbook_expense_account_index', './assets/js/housekeepingbook/account/expense_index.js')

    .addEntry('housekeepingbook_deposit_transaction_index', './assets/js/housekeepingbook/transaction/deposit_index.js')
    .addEntry('housekeepingbook_withdrawal_transaction_index', './assets/js/housekeepingbook/transaction/withdrawal_index.js')
    .addEntry('housekeepingbook_transfer_transaction_index', './assets/js/housekeepingbook/transaction/transfer_index.js')

    .addEntry('housekeepingbook_deposit_transaction_form', './assets/js/housekeepingbook/transaction/deposit_form.js')
    .addEntry('housekeepingbook_withdrawal_transaction_form', './assets/js/housekeepingbook/transaction/withdrawal_form.js')
    .addEntry('housekeepingbook_transfer_transaction_form', './assets/js/housekeepingbook/transaction/transfer_form.js')

    .addEntry('housekeepingbook_periodic_deposit_transaction_index', './assets/js/housekeepingbook/periodictransaction/deposit_index.js')
    .addEntry('housekeepingbook_periodic_withdrawal_transaction_index', './assets/js/housekeepingbook/periodictransaction/withdrawal_index.js')
    .addEntry('housekeepingbook_periodic_transfer_transaction_index', './assets/js/housekeepingbook/periodictransaction/transfer_index.js')

    .addEntry('housekeepingbook_periodic_deposit_transaction_form', './assets/js/housekeepingbook/periodictransaction/deposit_form.js')
    .addEntry('housekeepingbook_periodic_withdrawal_transaction_form', './assets/js/housekeepingbook/periodictransaction/withdrawal_form.js')
    .addEntry('housekeepingbook_periodic_transfer_transaction_form', './assets/js/housekeepingbook/periodictransaction/transfer_form.js')

    .addEntry('housekeepingbook_report_current_period', './assets/js/housekeepingbook/report/currentperiod.js')

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
    //.enableTypeScriptLoader()

    // uncomment if you use React
    //.enableReactPreset()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    .autoProvidejQuery()

    // workaround to import Moment.js
    //      Module build failed: Module not found:
    //      "./node_modules/moment/min/moment.min.js" contains a reference to the file "./locale".
    .addPlugin(
        new webpack.IgnorePlugin({
            resourceRegExp: /^\.\/locale$/,
            contextRegExp: /moment$/,
        })
    )
    .addPlugin(
        new webpack.ProvidePlugin({
            moment: "moment"
        })
    )
;

module.exports = Encore.getWebpackConfig();
