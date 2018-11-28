const webpack = require('webpack')
const merge = require('webpack-merge')
const common = require('./webpack.common.js')
const UglifyJsPlugin = require("uglifyjs-webpack-plugin")
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin")

module.exports = merge(common, {
    mode: 'production',
    devtool: 'source-map',
    output: {
        publicPath: undefined
    },
    optimization: {
        minimizer: [
            new UglifyJsPlugin({
                cache: true,
                parallel: true,
                sourceMap: true
            }),
            new OptimizeCSSAssetsPlugin({
                cssProcessorOptions: {
                    discardComments: {
                        removeAll: true
                    }
                }
            })
        ]
    }
})
