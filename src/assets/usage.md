## 1. Install Laravel Mix

Create a `package.json` file in your project andwith the following keys:

```json
  "scripts": {
    "start": "npm run watch",
    "dev": "npm run development",
    "development": "cross-env NODE_ENV=development node_modules/webpack/bin/webpack.js --progress --hide-modules --config=node_modules/laravel-mix/setup/webpack.config.js",
    "watch": "cross-env NODE_ENV=development node_modules/webpack/bin/webpack.js --watch --progress --hide-modules --config=node_modules/laravel-mix/setup/webpack.config.js",
    "hot": "cross-env NODE_ENV=development node_modules/webpack-dev-server/bin/webpack-dev-server.js --inline --disable-host-check --hot --https --key certs/cert-key.pem --cert certs/cert.pem --config=node_modules/laravel-mix/setup/webpack.config.js",
    "production": "cross-env NODE_ENV=production node_modules/webpack/bin/webpack.js --progress --hide-modules --config=node_modules/laravel-mix/setup/webpack.config.js"
  },
  "keywords": [],
  "author": "",
  "devDependencies": {
    "cross-env": "^6.0.3",
    "del": "^5.1.0",
    "laravel-mix": "^5.0.1",
    "postcss-custom-properties": "^9.0.2",
    "sass": "^1.25.0",
    "sass-loader": "^8.0.2",
    "uglifyjs-webpack-plugin": "^2.2.0",
    "unminified-webpack-plugin": "^2.0.0",
    "vue-template-compiler": "^2.6.11"
  }
```

If you're developing on a non https remove, otherwise, adjust to match your reality:

```
--https --key certs/cert-key.pem --cert certs/cert.pem
```


### 1.1 Configure Laravel Mix

Create a `webpack.mix.js` file:

```js
const mix = require('laravel-mix');
const path = require('path');
const del = require('del');

del(['admin/dist/']);

mix.setPublicPath(path.normalize('.'));
mix.setResourceRoot(path.normalize('assets/dist'));

const fs = require('fs');

const sassOptions = {
    postCss: [require('autoprefixer'), require('postcss-custom-properties')],
};

mix.options({
    processCssUrls: false,

    terser: {
        extractComments: false,
    },

    hmrOptions: {
        host: process.env.APP_URL,

        port: 8080,
        http2: true,
        contentBase: path.join(__dirname, '/'),
        stats: 'verbose'
    },
});

mix.webpackConfig(webpack => {
    return {
        externals: {
            jquery: 'jQuery',
            $: 'jQuery',
        },

        output: {
            filename: '[name].min.js',
            chunkFilename: "[name].min.js",
        },
    }
});

mix.js('assets/src/js/admin/index.js', 'assets/dist/js/');

mix.sourceMaps();
mix.disableNotifications();

if (mix.inProduction()) {
    mix.version();
}
```

### 1.3 `.env` file

If you're using hot module replacement, you need to create a `.env` file next to your `webpack.mix.js` file, that will contain:

```dotenv
APP_URL=https://your-dev-server-url
```

### 2. Initialize

If you're using in a plugin:
```php
$mix = new iamntz\wpUtils\assets\Mix(
    plugin_dir_path(__FILE__), 
    plugin_dir_url(__FILE__)
);
```

If you're using in a theme:
```php
$mix = new iamntz\wpUtils\assets\Mix(
    get_template_directory(), 
    get_template_directory_uri()
);
```

### 3. Usage

```php
wp_register_script('custom-admin-script', $mix->mix('/admin/dist/js/admin.js'));
```
