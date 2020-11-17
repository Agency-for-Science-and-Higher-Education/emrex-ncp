'use strict';

import plugins  from 'gulp-load-plugins';
import gulp     from 'gulp';
import rimraf   from 'rimraf';
import yaml     from 'js-yaml';
import fs       from 'fs';

// Load all Gulp plugins into one variable
const $ = plugins();

// Load settings from settings.yml
const { COMPATIBILITY, PORT, UNCSS_OPTIONS, PATHS } = loadConfig();

function loadConfig() {
  let ymlFile = fs.readFileSync('config.yml', 'utf8');
  return yaml.load(ymlFile);
}

// Build the "dist" folder by running all of the below tasks
gulp.task('build',
 gulp.series(clean, gulp.parallel(sass, javascript, images, copy)));

// Build the site, run the server, and watch for file changes
gulp.task('default',
  gulp.series('build', watch));

// Delete the "dist" folder
// This happens every time a build starts
function clean(done) {
  rimraf(PATHS.dist, done);
}

// Copy files out of the assets folder
// This task skips over the "img", "js", and "scss" folders, which are parsed separately
function copy() {
  return gulp.src(PATHS.assets)
    .pipe(gulp.dest(PATHS.dist + '/'));
}

// Compile Sass into CSS
function sass() {
  return gulp.src(['src/scss/app.scss', 'src/css/*.css'])
    .pipe($.sourcemaps.init())
    .pipe($.sass({
      includePaths: PATHS.sass
    }).on('error', $.sass.logError))
    .pipe($.autoprefixer({
      browsers: COMPATIBILITY
    }))
    //.pipe($.uncss(UNCSS_OPTIONS))
    .pipe($.cssnano())
    .pipe($.concat('emrex.css'))
    .pipe(gulp.dest(PATHS.dist + '/css'));
}

// Combine JavaScript into one file
function javascript() {
    return gulp.src(PATHS.javascript)
        .pipe($.babel())
        .pipe($.concat('emrex.js'))
        .pipe($.uglify({mangle: false}))
        .pipe(gulp.dest(PATHS.dist + '/js'));
}

// Copy images to the "dist" folder
function images() {
  return gulp.src('src/img/**/*')
    .pipe(gulp.dest(PATHS.dist + '/img'));
}

// Watch for changes to static assets, views, Sass, and JavaScript
function watch() {
  gulp.watch(PATHS.assets, gulp.series(copy));
  gulp.watch('src/scss/**/*.scss', sass);
  gulp.watch('src/js/**/*.js', gulp.series(javascript));
  gulp.watch('src/img/**/*', gulp.series(images));
}
