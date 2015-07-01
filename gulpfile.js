var gulp        = require('gulp'),
    autoprefixer= require('gulp-autoprefixer'),
    cache       = require('gulp-cache'),
    concat      = require('gulp-concat'),
    cssmin      = require('gulp-cssmin'),
    ignore      = require('gulp-ignore'),
    imagemin    = require('gulp-imagemin'),
    notify      = require('gulp-notify'),
    rename      = require('gulp-rename'),
    sass        = require('gulp-sass'),
    uglify      = require('gulp-uglify'),
    pngcrush    = require('imagemin-pngcrush');

gulp.task('styles', function() {
  return gulp.src('src/sass/styles.scss')
    .pipe(sass({errLogToConsole:true}))
    .pipe(autoprefixer('last 2 version', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1', 'ios 5', 'android 4'))
    .pipe(cssmin())
    .pipe(rename({suffix:'.min'}))
    .pipe(gulp.dest('static/css'))
    .pipe(notify({ message: 'Styles task complete' }));
});
gulp.task('scripts', function() {
    return gulp.src(['src/js/lib/*.js'])
        .pipe(concat('app.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest('public/assets/js'))
        .pipe(notify({ message: 'Scripts task complete' }));
});
gulp.task('crush', function() {
  return gulp.src(['public/assets/images/*'])
    .pipe(imagemin({
        progressive: true,
        svgoPlugins: [{removeViewBox: false}],
        use: [pngcrush()],
        optimizationLevel: 7
    }))
    .pipe(gulp.dest('public/assets/crushed'))
    .pipe(notify({ message: 'Crushing complete' }));
});
gulp.task('watch', function() {
  // Watch .scss files
  gulp.watch('public/assets/css/**/*.scss', ['styles']);
  // Watch .js files
  gulp.watch('public/assets/js/lib/*.js', ['scripts']);
});
gulp.task('default', function() {
    gulp.start('styles','scripts','watch');
});