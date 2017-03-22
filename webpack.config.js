
var path = require('path');

module.exports = {
    entry: './client/index.js',
    output: {
        filename: 'bundle.js',
        path: path.resolve(__dirname, 'www')
    },
    module: {
        loaders: [
            {
                exclude: /(node_modules|bower_components)/,
                test: /\.jsx?$/,
                loader: 'babel-loader'
            }
        ]
    },
    devtool: 'source-map'
};
