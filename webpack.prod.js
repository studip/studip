const webpack = require('webpack')
const { merge } = require('webpack-merge')
const common = require('./webpack.common.js')
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin")
const TerserPlugin = require("terser-webpack-plugin")

module.exports = merge(common, {
    mode: 'production',
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
