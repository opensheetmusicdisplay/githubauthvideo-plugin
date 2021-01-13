const path = require('path');

module.exports = {
  entry: {
            player: './src/player/player.js'
        },
  output: {
    filename: '[name].min.js',
    path: path.resolve(__dirname, 'build/player'),
  },
  optimization: {
    minimize: true
  }
};