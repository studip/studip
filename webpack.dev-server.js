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
//        contentBase: path.join(__dirname, './public/assets'),
        compress: true,
        port: config.port,
        historyApiFallback: true,
        https: config.protocol === 'https',
        headers: {
            'Access-Control-Allow-Origin': '*'
        },
        // Serve static files with appropriate headers
        before: (app, server) => {
            ['flash', 'fonts', 'images', 'sounds'].forEach(type => {
                app.use(
                    `/${path.basename(__dirname)}/${type}/`,
                    express.static(path.join(__dirname, `./public/assets/${type}/`), {
                        setHeaders: (res, path) => {
                            res.set('Access-Control-Allow-Origin', '*');
                        }
                    })
                );
            });

            ['ckeditor', 'mathjax'].forEach(vendor => {
                app.use(
                    `/${path.basename(__dirname)}/javascripts/${vendor}`,
                    express.static(path.join(__dirname, `./public/assets/javascripts/${vendor}/`), {
                        setHeaders: (res, path) => {
                            res.set('Access-Control-Allow-Origin', '*');
                        }
                    })
                );
            });
        }
    }
});
