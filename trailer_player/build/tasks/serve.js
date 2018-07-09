import gulp from 'gulp';
import browserSync from 'browser-sync';
import history from 'connect-history-api-fallback';

gulp.task('serve', ['watch'], (done)=>{
  browserSync({
    open: false,
    port: 9000,
    files: ['public/*.html', 'public/**/*.css', 'public/**/*.js'],
    server: {
      baseDir: './public',
      middleware:[
        (req,res,next)=>{
          res.setHeader('Access-Control-Allow-Origin', '*');
          next();
        },
        history()
      ]
    }
  }, done);
})