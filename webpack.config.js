
const path = require('path');

module.exports = {
    entry: './assets/js/blocks.js',
    output: {
        path: path.resolve(__dirname, 'build'),
        filename: 'index.js',
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                use: 'babel-loader',
                exclude: /node_modules/,
            },
        ],
    },
    externals: {
        react: 'React',
        'react-dom': 'ReactDOM',
        '@wordpress/element': ['wp', 'element'],
        '@wordpress/i18n': ['wp', 'i18n'],
        '@woocommerce/blocks-registry': ['wc', 'blocksRegistry'],
    },
};
