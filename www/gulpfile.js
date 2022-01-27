var gulp = require('gulp'),
    connect = require('gulp-connect-php'),
    browserSync = require('browser-sync');


/* Essa Task, disponibiliza o projeto para acesso pela rede local */
gulp.task('rede', function() {
  connect.server({}, function (){
   browserSync.init({
        proxy: 'http://4tibia.local:80/'
      });
    });
});