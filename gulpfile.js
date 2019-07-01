var gulp = require('gulp'),
    coffee = require('gulp-coffee'),
    plumber = require('gulp-plumber'),
    rename = require('gulp-rename'),
    concat = require('gulp-concat'),
    sass = require('gulp-sass'),
    sassGlob = require('gulp-sass-glob');
minifyCss = require('gulp-clean-css'),
    stripCssComments = require('gulp-strip-css-comments'),
    jshint = require('gulp-jshint');

// handle styles
function style() {
    return gulp.src('assets/css/sass/main.scss')
        .pipe(plumber({
            errorHandler: function (error) {
                console.log(error.message);
                this.emit('end');
            }
        }))
        .pipe(sassGlob())
        .pipe(sass())
        .pipe(stripCssComments())
        .pipe(minifyCss())
        .pipe(gulp.dest('assets/css'))
};
function admin() {   
    return gulp.src('admin/css/sass/admin.scss')
        .pipe(plumber({
            errorHandler: function (error) {
                console.log(error.message);
                this.emit('end');
            }
        }))
        .pipe(sassGlob())
        .pipe(sass())
        .pipe(stripCssComments())
        .pipe(minifyCss())
        .pipe(gulp.dest('admin/css'))
};

function frontEndScripts() {
    return gulp.src('assets/js/src/**/*.js')
        .pipe(plumber({
            errorHandler: function (error) {
                console.log(error.message);
                this.emit('end');
            }
        }))
        .pipe(concat('main.js'))
        .pipe(gulp.dest('assets/js/build/'))
};

function adminScripts() {
    return gulp.src('admin/js/src/**/*.js')
        .pipe(plumber({
            errorHandler: function (error) {
                console.log(error.message);
                this.emit('end');
            }
        }))
        .pipe(concat('main.js'))
        .pipe(gulp.dest('admin/js/build/'))
};

gulp.task('default', gulp.parallel(
    style, admin, frontEndScripts, adminScripts
));
