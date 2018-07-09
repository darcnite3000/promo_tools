import gulp from 'gulp';
import {styles} from '../paths';

gulp.task('watch',['webpack:build-dev','styles'], ()=>{
  gulp.watch(['app/**/*'], ['webpack:build-dev']);
  gulp.watch(styles.watch, ['styles']);
});

gulp.task('watch-pub',['webpack:build','styles'], ()=>{
  gulp.watch(['app/**/*'], ['webpack:build']);
  gulp.watch(styles.watch, ['styles']);
});