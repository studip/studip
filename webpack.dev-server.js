const express = require('express');
const path = require('path');
const merge = require('webpack-merge');
const common = require('./webpack.common.js');

const config = require("./config/webpack.dev-server.config.json");

module.exports = merge(common, {
    mode: 'development',
    devtool: 'inline-source-map',
    output: {
        publicPath: `${config.protocol}://${config.host}:${config.port}/${path.basename(__dirname)}`
    },
    devServer: {
        compress: true,
        port: config.port,
        historyApiFallback: true,
        https: config.protocol === 'https',
        headers: {
            'Access-Control-Allow-Origin': '*'
        },
        // Serve static files with appropriate headers
        before: (app, server) => {
            app.use(
                `/${path.basename(__dirname)}/`,
                express.static(path.join(__dirname, './public/assets/'), {
                    setHeaders: (res, path) => {
                        res.set('Access-Control-Allow-Origin', '*');
                    }
                })
            );
        }
    }
});
