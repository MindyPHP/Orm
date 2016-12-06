var webpack = require('webpack'),
    path = require('path');

module.exports = {
    devtool: 'eval',
    entry: {
        admin: './js/index.js',
        vendor: ['jquery', 'axios', 'bluebird']
    },
    output: {
        path: path.join(__dirname, '../public/js'),
        filename: '[name].js'
    },
    module: {
        loaders: [
            { test: /\.(js|jsx)$/, exclude: /node_modules/, loaders: ['babel-loader'] }
        ]
    },
    resolve: {
        modulesDirectories: ['./js', 'node_modules'],
        extensions: ['', '.js', '.jsx'],
        alias: {
            "jquery-ui/widget": "jquery-ui/ui/widget.js"
        }
    },
    plugins: [
        new webpack.optimize.CommonsChunkPlugin('vendor', 'vendor.admin.js'),
        new webpack.ProvidePlugin({
            'Promise': 'bluebird',
            'window.$': 'jquery',
            '$': 'jquery',
            'window.jQuery': 'jquery',
            'jQuery': 'jquery'
        }),
        new webpack.DefinePlugin({
            'process.env': { NODE_ENV: JSON.stringify(process.env.NODE_ENV || 'development') }
        })
    ]
};