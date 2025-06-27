const path = require('path');

module.exports = {
    entry: './assets/js/blocks.tsx',
    output: {
        path: path.resolve(__dirname, 'build'),
        filename: 'index.js',
    },
    resolve: {
        extensions: ['.tsx', '.ts', '.js'], // Dodaj .ts i .tsx
    },
    module: {
        rules: [
            {
                test: /\.(js|ts|tsx)$/, // Obsługuj js, ts, tsx
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@wordpress/babel-preset-default'], // Babel preset z WP
                    },
                },
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

