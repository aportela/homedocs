const webpack = require('webpack');
const path = require('path');
const RemovePlugin = require('remove-files-webpack-plugin');
const CopyPlugin = require("copy-webpack-plugin");
const HtmlWebpackPlugin = require('html-webpack-plugin');

module.exports = {
    mode: 'production',
    resolve: {
        alias: {
            'vue': "vue/dist/vue.esm-bundler.js",
            'vue-router': "vue-router/dist/vue-router.esm-bundler.js",
            'dayjs': "dayjs/dayjs.min.js",
            'basil': "basil.js/build/basil.js"
        }
    },
    entry: {
        'app': {
            import: './src-frontend/scripts/app.js',
            dependOn: 'vendor'
        },
        'vendor': ['vue', 'vue-router', 'axios', 'dayjs', 'basil']
    },
    output: {
        path: path.resolve(__dirname, 'public/scripts/'),
        publicPath: '/scripts/',
        filename: '[name]-bundle-[contenthash].min.js',
        clean: true
    },
    plugins: [
        // to remove warn in browser console: runtime-core.esm-bundler.js:3607 Feature flags __VUE_OPTIONS_API__, __VUE_PROD_DEVTOOLS__ are not explicitly defined...
        new webpack.DefinePlugin({ __VUE_OPTIONS_API__: true, __VUE_PROD_DEVTOOLS__: true }),
        new RemovePlugin({
            before: {
                include: [
                    __dirname, 'public/scripts/',
                    __dirname, 'public/styles/'
                ]
            }
        }),
        new CopyPlugin({
            patterns: [
                { from: path.resolve(__dirname, 'src-frontend/styles'), to: path.resolve(__dirname, 'public/styles') }
            ],
        }),
        new HtmlWebpackPlugin({
            template: path.resolve(__dirname, 'templates/index-webpack.html.twig'),
            filename: path.resolve(__dirname, 'templates/index.html.twig'),
            hash: false
        })
    ]
};