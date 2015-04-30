module.exports = {
    entry: "./app/main.js",
    output: {
        path: __dirname,
        filename: "js/main.js"
    },
    module: {
        loaders: [
            { test: /\.coffee$/, loader: 'coffee-loader' },
            { test: /\.js$/, loader: 'jsx-loader?harmony' },
            { test: /\.css$/, loader: "style!css" },
            { test: /\.scss$/, loader: "style!css!autoprefixer!sass" }
        ]
    },
    resolve: {
      extensions: ['', '.js', '.json', '.coffee', 'scss']
    }
};
