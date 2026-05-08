const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const WooCommerceDependencyExtractionWebpackPlugin = require('@woocommerce/dependency-extraction-webpack-plugin');
const path = require('path');

const wcDepMap = {
	'@woocommerce/blocks-registry': ['wc', 'wcBlocksRegistry'],
	'@woocommerce/settings'       : ['wc', 'wcSettings']
};

const wcHandleMap = {
	'@woocommerce/blocks-registry': 'wc-blocks-registry',
	'@woocommerce/settings'       : 'wc-settings'
};

const requestToExternal = (request) => {
	if (wcDepMap[request]) {
		return wcDepMap[request];
	}
};

const requestToHandle = (request) => {
	if (wcHandleMap[request]) {
		return wcHandleMap[request];
	}
};

// Export configuration.
module.exports = {
	...defaultConfig,
	entry: {
		'frontend/blocks': '/resources/js/frontend/blocks/index.js',
		'frontend/blocks/lomi-one': '/resources/js/frontend/blocks/gateway-one/index.js',
		'frontend/blocks/lomi-two': '/resources/js/frontend/blocks/gateway-two/index.js',
		'frontend/blocks/lomi-three': '/resources/js/frontend/blocks/gateway-three/index.js',
		'frontend/blocks/lomi-four': '/resources/js/frontend/blocks/gateway-four/index.js',
		'frontend/blocks/lomi-five': '/resources/js/frontend/blocks/gateway-five/index.js',
	},
	output: {
		path: path.resolve( __dirname, 'assets/js/blocks' ),
		filename: '[name].js',
	},
	plugins: [
		...defaultConfig.plugins.filter(
			(plugin) =>
				plugin.constructor.name !== 'DependencyExtractionWebpackPlugin'
		),
		new WooCommerceDependencyExtractionWebpackPlugin({
			requestToExternal,
			requestToHandle
		})
	]
};
