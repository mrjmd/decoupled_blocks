// First gulp tasks.

var gulp = require('gulp');
var del = require('del');
var exec = require('child_process').exec;

// Clean the temp dirs.
function clean(){
    return del([
        'components/**/*.js',
        'components/**/*.js.map',
        'assets/classes/*.js',
        'assets/classes/*.js.map',
        'assets/app/app.js',
        'assets/app/app.js.map'
    ]);
}

// Run angular's template compiler.
function precompile(cb){
    exec('./node_modules/.bin/ngc -p components', function(err, stdout, stderr){
        console.log(stdout);
        console.log(stderr);
        cb(err);
    });
}

// Run tsc.
function compile(cb){
    exec('./node_modules/.bin/tsc', function(err, stdout, stderr){
        console.log(stdout);
        console.log(stderr);
        cb(err);
    });
}

// Copy the sources next to the generated.
function copy_src(){
    return gulp.src(['components/**/*.ts'])
        .pipe(gulp.dest('generated'));
}

// Task definitions.
gulp.task('compile', compile);
gulp.task('precompile', ['copy:src'], precompile);
gulp.task('copy:src', ['clean'], copy_src);
gulp.task('clean', clean);
gulp.task('watch', function() {
    gulp.watch(['**/*.ts'], ['compile']);
});
