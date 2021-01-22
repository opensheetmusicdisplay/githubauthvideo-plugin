const path = require('path');

module.exports = [
{
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
},
{
  entry: {
            settings: './src/admin/settings.js'
        },
  output: {
    filename: '[name].min.js',
    path: path.resolve(__dirname, 'build/admin'),
  },
  optimization: {
    minimize: true
  }
}
];