import gulp from 'gulp';
import gutil from 'gulp-util';
import webpack from 'webpack';
import WebpackDevServer from 'webpack-dev-server';
import webpackConfig from '../../webpack.config.js';

gulp.task('webpack:dev', ['webpack-dev-server']);


gulp.task('webpack:build', (callback)=>{
  var myConfig = Object.create(webpackConfig);
  myConfig.plugins = myConfig.plugins.concat(
    new webpack.DefinePlugin({
      'process.env': {
        'NODE_ENV': JSON.stringify('production')
      }
    }),
    new webpack.optimize.DedupePlugin(),
    new webpack.optimize.UglifyJsPlugin()
  );

  webpack(myConfig, (err, stats)=>{
    if(err) throw new gutil.PluginError('webpack:build', err);
    gutil.log('[webpack:build]', stats.toString({colors: true}));
    callback();
  })
});

var myDevConfig = Object.create(webpackConfig);
myDevConfig.devtool = 'sourcemap';
myDevConfig.debug = true;

var devCompiler = webpack(myDevConfig);

gulp.task('webpack:build-dev', (callback)=>{
  devCompiler.run((err,stats)=>{
    if(err) throw new gutil.PluginError('webpack:build-dev', err);
    gutil.log('[webpack:build-dev]', stats.toString({colors: true}));
    callback();
  });
});

gulp.task('webpack-dev-server', (callback)=>{
  var myConfig = Object.create(webpackConfig);
  myConfig.devtool = 'eval';
  myConfig.debug = true;

  new WebpackDevServer(webpack(myConfig), {
    publicPath: `${myConfig.output.publicPath}`,
    contentBase: 'public/',
    stats: {colors: true},
  }).listen(8080,'localhost',(err)=>{
    if(err) throw new gutil.PluginError('webpack-dev-server', err);
    gutil.log('[webpack-dev-server]', 'http://localhost:8080/webpack-dev-server/');
  })
})
