var gulp = require('gulp'),
    coffee = require('gulp-coffee'),
    plumber = require('gulp-plumber'),
    rename = require('gulp-rename'),
    concat = require('gulp-concat'),
    sass = require('gulp-sass'),
    sassGlob = require('gulp-sass-glob');
minifyCss = require('gulp-minify-css'),
    stripCssComments = require('gulp-strip-css-comments'),
    jshint = require('gulp-jshint');

// handle styles
gulp.task('styles', function () {
    gulp.src(['assets/css/sass/main.scss',
        ])
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
    gulp.src(['admin/css/sass/admin.scss',
        ])
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
});

gulp.task('front-end-scripts', function () {
    return gulp.src('assets/js/src/**/*.js')
        .pipe(plumber({
            errorHandler: function (error) {
                console.log(error.message);
                this.emit('end');
            }
        }))
        .pipe(concat('main.js'))
        .pipe(gulp.dest('assets/js/build/'))
});

gulp.task('admin-scripts', function () {
    return gulp.src('admin/js/src/**/*.js')
        .pipe(plumber({
            errorHandler: function (error) {
                console.log(error.message);
                this.emit('end');
            }
        }))
        .pipe(concat('main.js'))
        .pipe(gulp.dest('admin/js/build/'))
});

gulp.task('default', [
    'styles',
    'front-end-scripts',
    'admin-scripts'
]);
