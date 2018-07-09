var path = require('path');
var webpack = require('webpack');

module.exports = {
  entry: {
    trailer: ['./app/bootstrap.js']
  },
  output: {
    path: path.join(__dirname, 'public', 'assets'),
    publicPath: 'assets/',
    filename: '[name].js',
    chunkFilename: '[chunkhash].js'
  },
  module: {
    loaders: [
    {
      test: /\.jsx?/,
      exclude: /(node_modules|bower_components)/,
      loader: 'babel',
      query: {
        optional: ['runtime', 'es7.classProperties'],
        stage: 1
      }
    }
    ]
  },
  resolve: {},
  plugins: [

  ]
};