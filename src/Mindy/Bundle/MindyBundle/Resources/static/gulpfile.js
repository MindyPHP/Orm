'use strict';

var gulp = require('gulp'),
    concat = require('gulp-concat'),
    clean = require('gulp-clean'),
    sass = require('gulp-sass'),
    autoprefixer = require('gulp-autoprefixer'),
    flatten = require('gulp-flatten'),
    csso = require('gulp-csso'),
    browserSync = require('browser-sync').create();

var dst = {
    ckeditor: '../public/ckeditor/',
    js: '../public/js',
    css: '../public/css',
    images: '../public/images',
    fonts: '../public/fonts'
};

var paths = {
    ckeditor: './ckeditor/**/*',
    images: './images/**/*{.jpg,.jpeg,.png}',
    fonts: './fonts/*/fonts/*{.otf,.eot,.ttf,.svg,.woff,.woff2}',
    css: [
        './scss/**/*.scss',
        './fonts/**/*.css'
    ],
    views: '../templates/**/*'
};

let sassOptions = {};

gulp.task('ckeditor', function () {
    return gulp.src(paths.ckeditor)
        .pipe(gulp.dest(dst.ckeditor));
});

gulp.task('images', function () {
    return gulp.src(paths.images)
        .pipe(gulp.dest(dst.images))
        .pipe(browserSync.stream());
});

gulp.task('fonts', function () {
    return gulp.src(paths.fonts)
        .pipe(flatten())
        .pipe(gulp.dest(dst.fonts));
});

gulp.task('css', function () {
    return gulp.src(paths.css)
        .pipe(sass(sassOptions).on('error', sass.logError))
        .pipe(autoprefixer())
        .pipe(csso())
        .pipe(concat('admin.css'))
        .pipe(gulp.dest(dst.css))
        .pipe(browserSync.stream());
});

gulp.task('watch', ['default'], function () {
    browserSync.init({
        proxy: "localhost:8000",
        open: false
    });

    gulp.watch(dst.js).on('change', browserSync.reload);
    gulp.watch(paths.images, ['images']).on('change', browserSync.reload);
    gulp.watch(paths.css, ['css']);
    gulp.watch(paths.fonts, ['fonts']);
    gulp.watch(paths.views).on('change', browserSync.reload);
});

gulp.task('default', function () {
    return gulp.start('css', 'images', 'fonts');
});
