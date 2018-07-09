var gulp = require('gulp');
require('babel/register')({
  only: '/build/',
  optional: ['es7.classProperties'],
  stage: 1
});
require('require-dir')('build/tasks');

gulp.task('default', ['watch']);