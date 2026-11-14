var gulp = require('gulp'),
    plumber = require('gulp-plumber'),
    concat = require('gulp-concat'),
    sass = require('gulp-sass')(require('sass')),
    sassGlob = require('gulp-sass-glob'),
    minifyCss = require('gulp-clean-css'),
    stripCssComments = require('gulp-strip-css-comments').default;

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

// copy the pdf.js files the viewer loads out of the pdfjs-dist package, so a package bump changes
// only package-lock.json and these generated files (binary files, so no text encoding)
function pdfjs() {
    return gulp.src([
        'build/pdf.mjs',
        'build/pdf.worker.mjs',
        'web/pdf_viewer.mjs',
        'web/pdf_viewer.css',
        'web/images/**',
        'cmaps/**',
        'iccs/**',
        'standard_fonts/**',
        'wasm/**',
        'LICENSE'
    ], { cwd: 'node_modules/pdfjs-dist', base: 'node_modules/pdfjs-dist', encoding: false })
        .pipe(gulp.dest('assets/lib/pdfjs'))
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
    style, admin, adminScripts, pdfjs
));
