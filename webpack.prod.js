const CssMinimizerPlugin = require('css-minimizer-webpack-plugin')
const TerserPlugin = require('terser-webpack-plugin')
const common = require('./webpack.common.js')
const webpack = require('webpack')
const { merge } = require('webpack-merge')

module.exports = merge(common, {
    mode: 'production',
    stats: 'errors-only',
    devtool: 'source-map',
    output: {
        publicPath: undefined
    },
    optimization: {
        minimize: true,
        minimizer: [
            new TerserPlugin(
                {
                    extractComments: false
                }
            ),
            new CssMinimizerPlugin({
                minimizerOptions: {
                    preset: [ 'default', { discardComments: { removeAll: true } } ],
                },
            }),
        ]
    }
})
