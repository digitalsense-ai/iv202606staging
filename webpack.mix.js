const { EnvironmentPlugin, IgnorePlugin } = require('webpack');
const mix = require('laravel-mix');
const glob = require('glob');
const path = require('path');

/*
 |--------------------------------------------------------------------------
 | Configure mix
 |--------------------------------------------------------------------------
 */

mix.options({
  resourceRoot: process.env.ASSET_URL || undefined,
  processCssUrls: false,
  postCss: [require('autoprefixer')]
});

/*
 |--------------------------------------------------------------------------
 | Configure Webpack
 |--------------------------------------------------------------------------
 */

mix.webpackConfig({
  output: {
    publicPath: process.env.ASSET_URL || undefined,
    libraryTarget: 'umd'
  },
  plugins: [
    new IgnorePlugin({
      checkResource(resource, context) {
        return [
          path.join(__dirname, 'resources/assets/vendor/libs/@form-validation')
          // Add more paths to ignore as needed
        ].some(pathToIgnore => resource.startsWith(pathToIgnore));
      }
    }),
    new EnvironmentPlugin({
      // Application's public url
      BASE_URL: process.env.ASSET_URL ? `${process.env.ASSET_URL}/` : '/'
    })
  ],
  module: {
    rules: [
      {
        test: /\.es6$|\.js$/,
        include: [
          path.join(__dirname, 'node_modules/bootstrap/'),
          path.join(__dirname, 'node_modules/popper.js/'),
          path.join(__dirname, 'node_modules/shepherd.js/')
        ],
        loader: 'babel-loader',
        options: {
          presets: [['@babel/preset-env', { targets: 'last 2 versions, ie >= 10' }]],
          plugins: [
            '@babel/plugin-transform-destructuring',
            '@babel/plugin-proposal-object-rest-spread',
            '@babel/plugin-transform-template-literals'
          ],
          babelrc: false
        }
      }
    ]
  },
  externals: {
    jquery: 'jQuery',
    moment: 'moment',
    jsdom: 'jsdom',
    velocity: 'Velocity',
    hammer: 'Hammer',
    pace: '"pace-progress"',
    chartist: 'Chartist',
    'popper.js': 'Popper',

    // blueimp-gallery plugin
    './blueimp-helper': 'jQuery',
    './blueimp-gallery': 'blueimpGallery',
    './blueimp-gallery-video': 'blueimpGallery'
  }
});

/*
 |--------------------------------------------------------------------------
 | Vendor assets
 |--------------------------------------------------------------------------
 */

function mixAssetsDir(query, cb) {
  (glob.sync('resources/assets/' + query) || []).forEach(f => {
    f = f.replace(/[\\\/]+/g, '/');
    cb(f, f.replace('resources/assets/', 'public/assets/'));
  });
}

/*
 |--------------------------------------------------------------------------
 | Configure sass
 |--------------------------------------------------------------------------
 */

const sassOptions = {
  precision: 5
};

// Core stylesheets
mixAssetsDir('vendor/scss/**/!(_)*.scss', (src, dest) =>
  mix.sass(src, dest.replace(/(\\|\/)scss(\\|\/)/, '$1css$2').replace(/\.scss$/, '.css'), { sassOptions })
);

// Core javaScripts
mixAssetsDir('vendor/js/**/*.js', (src, dest) => mix.js(src, dest));

// Libs
mixAssetsDir('vendor/libs/**/*.js', (src, dest) => mix.js(src, dest));
mixAssetsDir('vendor/libs/**/!(_)*.scss', (src, dest) =>
  mix.sass(src, dest.replace(/\.scss$/, '.css'), { sassOptions })
);
mixAssetsDir('vendor/libs/**/*.{png,jpg,jpeg,gif}', (src, dest) => mix.copy(src, dest));
// Copy task for form validation plugin as premium plugin don't have npm package
mixAssetsDir('vendor/libs/formvalidation/dist', (src, dest) => mix.copyDirectory(src, dest));
mixAssetsDir('vendor/libs/@form-validation/umd', (src, dest) => mix.copyDirectory(src, dest));

// Fonts
mixAssetsDir('vendor/fonts/*/*', (src, dest) => mix.copy(src, dest));
mixAssetsDir('vendor/fonts/!(_)*.scss', (src, dest) =>
  mix.sass(src, dest.replace(/(\\|\/)scss(\\|\/)/, '$1css$2').replace(/\.scss$/, '.css'), { sassOptions })
);

/*
 |--------------------------------------------------------------------------
 | Application assets
 |--------------------------------------------------------------------------
 */

mixAssetsDir('js/**/*.js', (src, dest) => mix.scripts(src, dest));
mixAssetsDir('css/**/*.css', (src, dest) => mix.copy(src, dest));
// laravel working crud app related js
//mix.js('resources/js/laravel-user-management.js', 'public/js/');

// Intravat app related js
mix.js('resources/js/dv-chat-talk.js', 'public/js/');
//mix.js('resources/js/dv-client-form-validation.js', 'public/js/');
mix.js('resources/js/dv-company-form-validation-lazy.js', 'public/js/');
//mix.js('resources/js/dv-client-history.js', 'public/js/');
mix.js('resources/js/dv-company-comment.js', 'public/js/');
//mix.js('resources/js/dv-clients.js', 'public/js/');
mix.js('resources/js/dv-companies-lazy.js', 'public/js/');
mix.js('resources/js/dv-comments.js', 'public/js/');
mix.js('resources/js/dv-common.js', 'public/js/');
mix.js('resources/js/dv-confirm-numbers.js', 'public/js/');
//mix.js('resources/js/dv-contacts.js', 'public/js/');
mix.js('resources/js/dv-contacts-lazy.js', 'public/js/');
mix.js('resources/js/dv-email-template-editors.js', 'public/js/');
mix.js('resources/js/dv-erp-load.js', 'public/js/');
mix.js('resources/js/dv-history.js', 'public/js/');
mix.js('resources/js/dv-vatreturn-notes.js', 'public/js/');
mix.js('resources/js/dv-importreconciliation-notes.js', 'public/js/');
//mix.js('resources/js/dv-import-vat.js', 'public/js/');
mix.js('resources/js/dv-import-vat-lazy.js', 'public/js/');
//mix.js('resources/js/dv-import-vat-files.js', 'public/js/');
mix.js('resources/js/dv-import-vat-files-lazy.js', 'public/js/');
//mix.js('resources/js/dv-invoices.js', 'public/js/');
mix.js('resources/js/dv-invoices-lazy.js', 'public/js/');
mix.js('resources/js/dv-modal-assign-client.js', 'public/js/');
mix.js('resources/js/dv-modal-assign-client-user.js', 'public/js/');
mix.js('resources/js/dv-modal-assign-team-user.js', 'public/js/');
mix.js('resources/js/dv-modal-assign-vat-reg.js', 'public/js/');
//mix.js('resources/js/dv-modal-select-vat-account-nos.js', 'public/js/');
//mix.js('resources/js/dv-my-tasks.js', 'public/js/');
mix.js('resources/js/dv-all-tasks-lazy.js', 'public/js/');
mix.js('resources/js/dv-payment-info-form-validation.js', 'public/js/');
//mix.js('resources/js/dv-submitting-fields.js', 'public/js/');
mix.js('resources/js/dv-submitting-fields-lazy.js', 'public/js/');
//mix.js('resources/js/dv-users.js', 'public/js/');
mix.js('resources/js/dv-users-lazy.js', 'public/js/');
//mix.js('resources/js/dv-vat-registration-main.js', 'public/js/');
mix.js('resources/js/dv-vat-registration-main-lazy.js', 'public/js/');
//mix.js('resources/js/dv-vatreturn-files.js', 'public/js/');
mix.js('resources/js/dv-vatreturn-files-lazy.js', 'public/js/');
mix.js('resources/js/dv-upload.js', 'public/js/');
mix.js('resources/js/dv-bulk-upload.js', 'public/js/');
mix.js('resources/js/dv-compliance.js', 'public/js/');
mix.js('resources/js/dv-reminder.js', 'public/js/');
mix.js('resources/js/dv-taskdate.js', 'public/js/');

mix.js('resources/js/dv-register.js', 'public/js/');
mix.js('resources/js/dv-register-file-upload-lazy.js', 'public/js/');
mix.js('resources/js/dv-connection.js', 'public/js/');

//mix.js('resources/js/dv-excel-column-template.js', 'public/js/');
mix.js('resources/js/dv-excel-column-template-new.js', 'public/js/');

mix.js('resources/js/dv-modal-select-account-nos.js', 'public/js/');
mix.js('resources/js/dv-modal-select-client-vatnos.js', 'public/js/');

mix.js('resources/js/dv-declarations.js', 'public/js/');
mix.js('resources/js/dv-declaration-comment.js', 'public/js/');

mix.js('resources/js/dv-mailbox-files.js', 'public/js/');

mix.js('resources/js/dv-cargo-declaration-files.js', 'public/js/');

mix.js('resources/js/dv-preview-report.js', 'public/js/');

mix.js('resources/js/dv-vatcheck.js', 'public/js/');

mix.js('resources/js/dv-global-search.js', 'public/js/');

mix.js('resources/js/dv-anyexcel-template.js', 'public/js/');
mix.js('resources/js/dv-anyexcel-template-others.js', 'public/js/');

mix.copy('node_modules/boxicons/fonts/*', 'public/assets/vendor/fonts/boxicons');
mix.copy('node_modules/@fortawesome/fontawesome-free/webfonts/*', 'public/assets/vendor/fonts/fontawesome');
mix.copy('node_modules/katex/dist/fonts/*', 'public/assets/vendor/libs/quill/fonts');
mix.js('resources/js/app.js', 'public/js/alpine.js');
mix.js('node_modules/popper.js/dist/popper.js', 'public/js').sourceMaps();//oxygen - npm run dev
mix.version();

/*
 |--------------------------------------------------------------------------
 | Browsersync Reloading
 |--------------------------------------------------------------------------
 |
 | BrowserSync can automatically monitor your files for changes, and inject your changes into the browser without requiring a manual refresh.
 | You may enable support for this by calling the mix.browserSync() method:
 | Make Sure to run `php artisan serve` and `yarn watch` command to run Browser Sync functionality
 | Refer official documentation for more information: https://laravel.com/docs/9.x/mix#browsersync-reloading
 */

mix.browserSync('http://127.0.0.1:8000/');
