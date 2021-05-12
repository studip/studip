const webpack = require("webpack");
const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const VueLoaderPlugin = require('vue-loader/lib/plugin');

const assetsPath = path.resolve(__dirname, "resources/assets/javascripts");

const fs = require('fs');
const version = fs.readFileSync(path.resolve(__dirname, 'VERSION'), 'utf-8')
    .trim()
    .split(' ')
    .slice(1)
    .join(' ');

module.exports = {
    entry: {
        "studip-base": assetsPath + "/entry-base.js",
        "studip-admission": assetsPath + "/entry-admission.js",
        "studip-statusgroups": assetsPath + "/entry-statusgroups.js",
        "studip-wysiwyg": assetsPath + "/entry-wysiwyg.js",
        "studip-installer": assetsPath + "/entry-installer.js",
        "print": path.resolve(__dirname, "resources/assets/stylesheets") + "/print.less",
        "webservices": path.resolve(__dirname, "resources/assets/stylesheets") + "/webservices.scss"
    },
    output: {
        path: path.resolve(__dirname, "public/assets"),
        chunkFilename: `javascripts/[id].chunk.js`,
        filename: "javascripts/[name].js"
    },
    module: {
        rules: [
            {
                test: /\.css$/,
                use: [
                    {
                        loader: MiniCssExtractPlugin.loader
                    },
                    {
                        loader: "css-loader",
                        options: {
                            url: false,
                            importLoaders: 1
                        }
                    },
                    {
                        loader: "postcss-loader"
                    }
                ]
            },
            {
                test: /\.scss$/,
                use: [
                    {
                        loader: MiniCssExtractPlugin.loader
                    },
                    {
                        loader: "css-loader",
                        options: {
                            url: false,
                            importLoaders: 2
                        }
                    },
                    {
                        loader: "postcss-loader"
                    },
                    {
                        loader: "sass-loader"
                    }
                ]
            },
            {
                test: /\.less$/,
                use: [
                    {
                        loader: MiniCssExtractPlugin.loader
                    },
                    {
                        loader: "css-loader",
                        options: {
                            url: false,
                            importLoaders: 2
                        }
                    },
                    {
                        loader: "postcss-loader"
                    },
                    {
                        loader: "less-loader",
                        options: {
                            lessOptions: {
                                relativeUrls: false
                            }
                        }
                    }
                ]
            },
            {
                test: /\.js$/,
                exclude: /node_modules|ckeditor/,
                use: {
                    loader: 'babel-loader'
                }
            },
            {
                test: /\.vue$/,
                loader: 'vue-loader'
            }
        ]
    },
    plugins: [
        new VueLoaderPlugin(),
        new MiniCssExtractPlugin({
            filename: "stylesheets/[name].css",
            chunkFilename: `stylesheets/[name]-${version.replace(/\W/g, '-')}.css`
        }),
    ],
    resolve: {
        alias: {
            'vue$': 'vue/dist/vue.esm.js',
            'jquery-ui/data': 'jquery-ui/ui/data',
            'jquery-ui/disable-selection': 'jquery-ui/ui/disable-selection',
            'jquery-ui/focusable': 'jquery-ui/ui/focusable',
            'jquery-ui/form': 'jquery-ui/ui/form',
            'jquery-ui/ie': 'jquery-ui/ui/ie',
            'jquery-ui/keycode': 'jquery-ui/ui/keycode',
            'jquery-ui/labels': 'jquery-ui/ui/labels',
            'jquery-ui/jquery-1-7': 'jquery-ui/ui/jquery-1-7',
            'jquery-ui/plugin': 'jquery-ui/ui/plugin',
            'jquery-ui/safe-active-element': 'jquery-ui/ui/safe-active-element',
            'jquery-ui/safe-blur': 'jquery-ui/ui/safe-blur',
            'jquery-ui/scroll-parent': 'jquery-ui/ui/scroll-parent',
            'jquery-ui/tabbable': 'jquery-ui/ui/tabbable',
            'jquery-ui/unique-id': 'jquery-ui/ui/unique-id',
            'jquery-ui/version': 'jquery-ui/ui/version',
            'jquery-ui/widget': 'jquery-ui/ui/widget',
            'jquery-ui/widgets/mouse': 'jquery-ui/ui/widgets/mouse',
            'jquery-ui/widgets/draggable': 'jquery-ui/ui/widgets/draggable',
            'jquery-ui/widgets/droppable': 'jquery-ui/ui/widgets/droppable',
            'jquery-ui/widgets/resizable': 'jquery-ui/ui/widgets/resizable',
            '@': path.resolve(__dirname, 'resources')
        },
        fallback: {
            'stream': require.resolve("stream-browserify"),
            'buffer': require.resolve("buffer/")
        }
    }
};
