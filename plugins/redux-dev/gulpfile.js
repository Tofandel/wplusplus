/**
 * Gulpfile.
 *
 * Gulp with WordPress.
 *
 * Implements:
 *      1. Live reloads browser with BrowserSync.
 *      2. CSS: Sass to CSS conversion, error catching, Autoprefixing, Sourcemaps,
 *         CSS minification, and Merge Media Queries.
 *      3. JS: Concatenates & uglifies Vendor and Custom JS files.
 *      4. Images: Minifies PNG, JPEG, GIF and SVG images.
 *      5. Watches files for changes in CSS or JS.
 *      6. Watches files for changes in PHP.
 *      7. Corrects the line endings.
 *      8. InjectCSS instead of browser page reload.
 *      9. Generates .pot file for i18n and l10n.
 *
 * @author Ahmad Awais (@ahmadawais)
 * @version 1.0.3
 */

/**
 * Configuration.
 *
 * Project Configuration for gulp tasks.
 *
 * In paths you can add <<glob or array of globs>>. Edit the variables as per your project requirements.
 */

// START Editing Project Variables.
// Project related.
var project                 = 'Redux Framework'; // Project Name.
var projectURL              = 'http://127.0.0.1/redux-demo'; // Project URL. Could be something like localhost:8888.
var productURL              = './'; // Theme/Plugin URL. Leave it like it is, since our gulpfile.js lives in the root folder.

// Translation related.
var text_domain             = 'redux-framework'; // Your textdomain here.
var destFile                = 'redux-framework.pot'; // Name of the transalation file.
var packageName             = 'redux-framework'; // Package name.
var bugReport               = 'https://redux.io/support'; // Where can users report bugs.
var lastTranslator          = 'Dovy Paukstys <dovy@redux.io>'; // Last translator Email ID.
var team                    = 'Team Redux <info@reduxframework.com>'; // Team's Email ID.
var translatePath           = './languages' // Where to save the translation files.

var styles = [
    {
        'path': './ReduxCore/assets/scss/vendor/elusive-icons/elusive-icons.scss',
        'dest': './ReduxCore/assets/css/vendor/'
    },
    {
        'path': './ReduxCore/assets/scss/vendor/select2/select2.scss',
        'dest': './ReduxCore/assets/css/vendor/'
    },

    {
        'path': './ReduxCore/assets/scss/vendor/jquery-ui-1.10.0.custom.scss',
        'dest': './ReduxCore/assets/css/vendor/'
    },
    {
        'path': './ReduxCore/assets/scss/vendor/nouislider.scss',
        'dest': './ReduxCore/assets/css/vendor/'
    },
    {
        'path': './ReduxCore/assets/scss/vendor/qtip.scss',
        'dest': './ReduxCore/assets/css/vendor/'
    },
    {
        'path': './ReduxCore/assets/scss/vendor/spectrum.scss',
        'dest': './ReduxCore/assets/css/vendor/'
    },
    {
        'path': './ReduxCore/assets/scss/vendor/vendor.scss',
        'dest': './ReduxCore/assets/css/'
    },
    {
        'path': './ReduxCore/assets/scss/color-picker.scss',
        'dest': './ReduxCore/assets/css/'
    },
    {
        'path': './ReduxCore/assets/scss/media.scss',
        'dest': './ReduxCore/assets/css/'
    },
    {
        'path': './ReduxCore/assets/scss/redux-admin.scss',
        'dest': './ReduxCore/assets/css/'
    },
    {
        'path': './ReduxCore/assets/scss/rtl.scss',
        'dest': './ReduxCore/assets/css/'
    },
    {
        'path': './ReduxCore/assets/scss/rtl.scss',
        'dest': './ReduxCore/assets/css/'
    },
	{
		'path': './ReduxCore/inc/welcome/css/redux-welcome.scss',
		'dest': './ReduxCore/inc/welcome/css/'
	},
]

// Style related.
var styleSRC                = './ReduxCore/assets/scss/redux-admin.scss'; // Path to main .scss file.
var styleDestination        = './ReduxCore/assets/css/'; // Path to place the compiled CSS file.

// JS Vendor related.
var jsVendorSRC             = './ReduxCore/assets/js/vendor/*.js'; // Path to JS vendor folder.
var jsVendorDestination     = './ReduxCore/assets/js/'; // Path to place the compiled JS vendors file.
var jsVendorFile            = 'redux-vendors'; // Compiled JS vendors file name.

// JS Custom related.
var jsReduxSRC              = './ReduxCore/assets/js/redux.js'; // Path to redux.js script.
var jsReduxDestination      = './ReduxCore/assets/js/'; // Path to place the compiled JS custom scripts file.
var jsReduxFile             = 'redux'; // Compiled JS custom file name.

// Images related.
var imagesSRC               = './ReduxCore/assets/img/raw/**/*.{png,jpg,gif,svg}'; // Source folder of images which should be optimized.
var imagesDestination       = './ReduxCore/assets/img/'; // Destination folder of optimized images. Must be different from the imagesSRC folder.

// Watch files paths.
var styleWatchFiles         = './ReduxCore/assets/css/**/*.scss'; // Path to all *.scss files inside css folder and inside them.
var vendorJSWatchFiles      = './ReduxCore/assets/js/vendor/*.js'; // Path to all vendor JS files.
var reduxJSWatchFiles       = './ReduxCore/assets/js/redux/*.js'; // Path to all custom JS files.
var projectPHPWatchFiles    = './**/*.php'; // Path to all PHP files.


// Browsers you care about for autoprefixing.
// Browserlist https        ://github.com/ai/browserslist
const AUTOPREFIXER_BROWSERS = [
    'last 2 version',
    '> 1%',
    'ie > 10',
    'ie_mob > 10',
    'ff >= 30',
    'chrome >= 34',
    'safari >= 7',
    'opera >= 23',
    'ios >= 7',
    'android >= 4',
    'bb >= 10'
  ];

// STOP Editing Project Variables.

/**
 * Load Plugins.
 *
 * Load gulp plugins and assing them semantic names.
 */
var gulp         = require('gulp'); // Gulp of-course

// CSS related plugins.
var sass         = require('gulp-sass'); // Gulp pluign for Sass compilation.
sass.compiler    = require('node-sass');

var minifycss    = require('gulp-uglifycss'); // Minifies CSS files.
var autoprefixer = require('gulp-autoprefixer'); // Autoprefixing magic.
var mmq          = require('gulp-merge-media-queries'); // Combine matching media queries into one media query definition.

// JS related plugins.
var concat       = require('gulp-concat'); // Concatenates JS files
var uglify       = require('gulp-uglify'); // Minifies JS files

// Image realted plugins.
var imagemin     = require('gulp-imagemin'); // Minify PNG, JPEG, GIF and SVG images with imagemin.

// Utility related plugins.
var rename       = require('gulp-rename'); // Renames files E.g. style.css -> style.min.css
var lineec       = require('gulp-line-ending-corrector'); // Consistent Line Endings for non UNIX systems. Gulp Plugin for Line Ending Corrector (A utility that makes sure your files have consistent line endings)
var filter       = require('gulp-filter'); // Enables you to work on a subset of the original files by filtering them using globbing.
var sourcemaps   = require('gulp-sourcemaps'); // Maps code in a compressed file (E.g. style.css) back to itâ€™s original position in a source file (E.g. structure.scss, which was later combined with other css files to generate style.css)
var notify       = require('gulp-notify'); // Sends message notification to you
var browserSync  = require('browser-sync').create(); // Reloads browser and injects CSS. Time-saving synchronised browser testing.
var reload       = browserSync.reload; // For manual browser reload.
var wpPot        = require('gulp-wp-pot'); // For generating the .pot file.
var sort         = require('gulp-sort'); // Recommended to prevent unnecessary changes in pot-file.
var fs           = require('fs');
var path         = require('path');
var merge        = require('merge-stream');
var del          = require('del');
var sassPackager = require('gulp-sass-packager');

/**
 * Task: `browser-sync`.
 *
 * Live Reloads, CSS injections, Localhost tunneling.
 *
 * This task does the following:
 *    1. Sets the project URL
 *    2. Sets inject CSS
 *    3. You may define a custom port
 *    4. You may want to stop the browser from openning automatically
 */
gulp.task( 'browser-sync', function() {
  browserSync.init( {

    // For more options
    // @link http://www.browsersync.io/docs/options/

    // Project URL.
    proxy: projectURL,

    // `true` Automatically open the browser with BrowserSync live server.
    // `false` Stop the browser from automatically opening.
    open: true,

    // Inject CSS changes.
    // Commnet it to reload browser for every CSS change.
    injectChanges: true,

    // Use a specific port (instead of the one auto-detected by Browsersync).
    // port: 7000,

  } );
});

function getFolders(dir) {
    return fs.readdirSync(dir)
        .filter(function(file) {
            return fs.statSync(path.join(dir, file)).isDirectory();
        });
}


function process_scss(source, dest, add_min) {
    var process = gulp.src( source )
        .pipe( sourcemaps.init() )
        .pipe( sass( {
            errLogToConsole: true,
            // outputStyle: 'compact',
            //outputStyle: 'compressed',
            // outputStyle: 'nested',
            outputStyle: 'expanded',
            precision: 10
        } ) )
        .on('error', console.error.bind(console))
        .pipe( sourcemaps.write( { includeContent: false } ) )
        .pipe( sourcemaps.init( { loadMaps: true } ) )
        .pipe( autoprefixer( AUTOPREFIXER_BROWSERS ) )

        .pipe( sourcemaps.write ( './' ) )
        .pipe( lineec() ) // Consistent Line Endings for non UNIX systems.
        .pipe( gulp.dest( dest ) )

        .pipe( filter( '**/*.css' ) ) // Filtering stream to only css files
        .pipe( mmq( { log: true } ) ) // Merge Media Queries only for .min.css version.

        .pipe( browserSync.stream() ) // Reloads style.css if that is enqueued.

    if (add_min) {
        process = process
            .pipe( rename( { suffix: '.min' } ) )
            .pipe( minifycss( {
                maxLineLen: 0
            }))
            .pipe( lineec() ) // Consistent Line Endings for non UNIX systems.
            .pipe( gulp.dest( dest ) )

            .pipe( filter( '**/*.css' ) ) // Filtering stream to only css files
            .pipe( browserSync.stream() );// Reloads style.min.css if that is enqueued.
    }
    return process
}

/**
 * Task: `styles`.
 *
 * Compiles Sass, Autoprefixes it and Minifies CSS.
 *
 * This task does the following:
 *    1. Gets the source scss file
 *    2. Compiles Sass to CSS
 *    3. Writes Sourcemaps for it
 *    4. Autoprefixes it and generates style.css
 *    5. Renames the CSS file with suffix .min.css
 *    6. Minifies the CSS file and generates style.min.css
 *    7. Injects CSS or reloads the browser via browserSync
 */
 gulp.task('styles', function () {
     // Core styles
     var core = styles.map(function(file){
        return process_scss(file.path, file.dest, true);
     });

     // Colors
     var color_dirs = getFolders('ReduxCore/assets/scss/colors/');
     var colors = color_dirs.map(function(folder) {
         var the_path = './ReduxCore/assets/css/colors/' + folder + '/'
         return process_scss('./ReduxCore/assets/scss/colors/' + folder + '/colors.scss', the_path, true);
     });

     // Fields
     var field_dirs = getFolders('ReduxCore/inc/fields/');
     var fields = field_dirs.map(function(folder) {
         var the_path = './ReduxCore/inc/fields/'+folder+'/'
         return process_scss(the_path+'field_'+folder+'.scss', the_path);
     });

     // Extensions
     var extension_dirs = getFolders('ReduxCore/inc/extensions/');
     var extensions = extension_dirs.map(function(folder) {
         var the_path = './ReduxCore/inc/extensions/'+folder+'/'
         return process_scss(the_path+'extension_'+folder+'.scss', the_path);
     });


     var redux_files = gulp.src(['ReduxCore/inc/fields/**/*.scss'])
         .pipe(sassPackager({
             // packageJSON: './sass-config.json'
         }))
         .pipe(concat('redux-fields.min.scss'))
         .pipe( sass( {
             errLogToConsole: true,
             // outputStyle: 'compact',
             outputStyle: 'compressed',
             // outputStyle: 'nested',
             // outputStyle: 'expanded',
             precision: 10
         } ) )
         .on('error', console.error.bind(console))
         .pipe( sourcemaps.write( { includeContent: false } ) )
         .pipe( sourcemaps.init( { loadMaps: true } ) )
         .pipe( autoprefixer( AUTOPREFIXER_BROWSERS ) )
         .pipe( sourcemaps.write ( './' ) )
         .pipe( lineec() ) // Consistent Line Endings for non UNIX systems.
         .pipe(gulp.dest('ReduxCore/assets/css/'))

     return merge(core, fields, extensions, redux_files); //.pipe(notify( { message: 'TASK: "styles" Completed!', onLast: true } ));

 });

 gulp.task( 'fieldsJS', function() {
    var field_dirs = getFolders('ReduxCore/inc/fields');
    var fields = field_dirs.map(function(folder) {
        var the_path = './ReduxCore/inc/fields/' + folder + '/';
        
        gulp.src( the_path + '/' + 'field_' + folder + '.js' )
        .pipe( lineec() ) // Consistent Line Endings for non UNIX systems.
        .pipe( gulp.dest( the_path ) )
        .pipe( rename( {
          basename: 'field_' + folder,
          suffix: '.min'
        }))
        .pipe( uglify() )
        .pipe( lineec() ) // Consistent Line Endings for non UNIX systems.
        .pipe( gulp.dest( the_path ) )
     });
     // .pipe( notify( { message: 'TASK: "fieldJs" Completed!', onLast: true } ) );
});

/**
  * Task: `reduxCombineModules`.
  *
  * Concatenate redux.js modules into master redux.js file.
  * reduxJS task is dependant upon this task to properly compete.
  *
  * This task does the following:
  *     1. Gets the source folder for Redux JS javascrip modules.
  *     2. Concatenates all the files and generates redux.js
  */
gulp.task('reduxCombineModules', function() {
    gulp.src( jsReduxSRC ) 
    .pipe( rename( {
        basename: jsReduxFile,
        suffix: '.min'
    }))
    .pipe( uglify() )
    .pipe( lineec() )
    .pipe( gulp.dest( jsReduxDestination ) )
})

 /**
  * Task: `reduxJS`.
  *
  * Concatenate redux.js modules into master file, then minifies & uglifies.
  *
  * This task does the following:
  *     1. Runs reduxCombineModules task
  *     2. Renames redux.js with suffix .min.js
  *     3. Uglifes/Minifies the JS file and generates redux.min.js
  */
 gulp.task( 'reduxJS', ['reduxCombineModules'], function() {
    gulp.src( reduxJSWatchFiles )
    .pipe( concat( jsReduxFile + '.js' ) )
    .pipe( lineec() )
    .pipe( gulp.dest( jsReduxDestination ) )
});

 /**
  * Task: `vendorJS`.
  *
  * Concatenate and uglify vendor JS scripts.
  *
  * This task does the following:
  *     1. Gets the source folder for JS vendor files
  *     2. Concatenates all the files and generates vendors.js
  *     3. Renames the JS file with suffix .min.js
  *     4. Uglifes/Minifies the JS file and generates vendors.min.js
  */
 gulp.task( 'vendorsJS', function() {
  gulp.src( jsVendorSRC )
    .pipe( concat( jsVendorFile + '.js' ) )
    .pipe( lineec() ) // Consistent Line Endings for non UNIX systems.
    .pipe( gulp.dest( jsVendorDestination ) )
    .pipe( rename( {
      basename: jsVendorFile,
      suffix: '.min'
    }))
    .pipe( uglify() )
    .pipe( lineec() ) // Consistent Line Endings for non UNIX systems.
    .pipe( gulp.dest( jsVendorDestination ) )
    //.pipe( notify( { message: 'TASK: "vendorsJS" Completed!', onLast: true } ) );
 });

  /**
  * Task: `images`.
  *
  * Minifies PNG, JPEG, GIF and SVG images.
  *
  * This task does the following:
  *     1. Gets the source of images raw folder
  *     2. Minifies PNG, JPEG, GIF and SVG images
  *     3. Generates and saves the optimized images
  *
  * This task will run only once, if you want to run it
  * again, do it with the command `gulp images`.
  */
 gulp.task( 'images', function() {
  gulp.src( imagesSRC )
    .pipe( imagemin( {
          progressive: true,
          optimizationLevel: 3, // 0-7 low-high
          interlaced: true,
          svgoPlugins: [{removeViewBox: false}]
        } ) )
    .pipe(gulp.dest( imagesDestination ))
    .pipe( notify( { message: 'TASK: "images" Completed!', onLast: true } ) );
 });

 /**
  * WP POT Translation File Generator.
  *
  * * This task does the following:
  *     1. Gets the source of all the PHP files
  *     2. Sort files in stream by path or any custom sort comparator
  *     3. Applies wpPot with the variable set at the top of this file
  *     4. Generate a .pot file of i18n that can be used for l10n to build .mo file
  */
 gulp.task( 'translate', function () {
     return gulp.src( projectPHPWatchFiles )
         .pipe(sort())
         .pipe(wpPot( {
             domain        : text_domain,
             destFile      : destFile,
             package       : packageName,
             bugReport     : bugReport,
             lastTranslator: lastTranslator,
             team          : team
         } ))
        .pipe(gulp.dest(translatePath))
        .pipe( notify( { message: 'TASK: "translate" Completed!', onLast: true } ) )

 });

 /**
  * Watch Tasks.
  *
  * Watches for file changes and runs specific tasks.
  */
gulp.task( 'default', ['styles', 'vendorsJS', 'reduxJS', 'fieldsJS', 'images'], function () {
//    gulp.watch( projectPHPWatchFiles, reload ); // Reload on PHP file changes.
//    gulp.watch( styleWatchFiles, [ 'styles' ] ); // Reload on SCSS file changes.
//    gulp.watch( vendorJSWatchFiles, [ 'vendorsJS', reload ] ); // Reload on vendorsJs file changes.
//    gulp.watch( reduxJSWatchFiles, [ 'reduxJS', reload ] ); // Reload on reduxJS file changes.
});