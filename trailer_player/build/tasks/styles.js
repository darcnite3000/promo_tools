import gulp from 'gulp';
import sass from 'gulp-sass';
import sourcemaps from 'gulp-sourcemaps';
import changed from 'gulp-changed';
import {styles} from '../paths';
import neat from 'node-neat';
import autoprefixer from 'gulp-autoprefixer';

export default gulp.task('styles', ()=>{
  return gulp.src(styles.globs)
    .pipe(changed(styles.output, {extension: '.css'}))
    .pipe(sourcemaps.init({loadMaps: true}))
      .pipe(sass({
        includePaths: neat.includePaths
      }).on('error',sass.logError))
      .pipe(autoprefixer())
    .pipe(sourcemaps.write({includeContent: false}))
    .pipe(gulp.dest(styles.output));
})