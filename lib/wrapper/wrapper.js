var postcss = require('postcss');
var autoprefixer = require('autoprefixer');

data = '';

process.stdin.on('data', function(chunk) {
    data += chunk;
});

process.stdin.on('end', function() {
    var promises = [];
    var processingResult = [];

    data = JSON.parse(data);

    var prefixer = postcss([ autoprefixer(data.options) ]);

    for (var i in data.css) {
            promise = new Promise(function (resolve) {
                prefixer.process(data.css[i]).then(
                    function (result) {
                        processingResult[i] = {
                            error: false,
                            css: result.css
                        };
                        resolve();
                    },
                    function (error) {
                        processingResult[i] = {
                            error: error.message,
                            css: null
                        };
                        resolve();
                    }
                );
            });

            promises.push(promise)
    }

    Promise.all(promises).then(function () {
            process.stdout.write(JSON.stringify(processingResult));
        }
    )
});