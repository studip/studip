const path = require('path');
const merge = require('webpack-merge');
const common = require('./webpack.common.js');

module.exports = merge(common, {
    mode: 'development',
    devtool: 'inline-source-map',
    output: {
        publicPath: 'http://localhost:8123/'
    },
    devServer: {
        contentBase: path.join(__dirname, './public/assets'),
        compress: true,
        port: 8123,
        historyApiFallback: true,
        https: false,
        headers: {
            'Access-Control-Allow-Origin': '*'
        }
    }
});
