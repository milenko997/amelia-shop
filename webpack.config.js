const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path          = require( 'path' );

module.exports = {
	...defaultConfig,

	entry: {
		// Theme JS + SCSS (SCSS imported inside main.js)
		main: path.resolve( __dirname, 'src/js/main.js' ),

		// Gutenberg blocks
		'blocks/hero/index':           path.resolve( __dirname, 'src/blocks/hero/index.js' ),
		'blocks/category-grid/index':  path.resolve( __dirname, 'src/blocks/category-grid/index.js' ),
		'blocks/features-strip/index': path.resolve( __dirname, 'src/blocks/features-strip/index.js' ),
		'blocks/newsletter/index':      path.resolve( __dirname, 'src/blocks/newsletter/index.js' ),
		'blocks/products/index':        path.resolve( __dirname, 'src/blocks/products/index.js' ),
		'blocks/products/view':         path.resolve( __dirname, 'src/blocks/products/view.js' ),
		'blocks/about-us/index':        path.resolve( __dirname, 'src/blocks/about-us/index.js' ),
		'blocks/contact/index':         path.resolve( __dirname, 'src/blocks/contact/index.js' ),
	},

	output: {
		...defaultConfig.output,
		path:     path.resolve( __dirname, 'build' ),
		filename: '[name].js',
	},
};
