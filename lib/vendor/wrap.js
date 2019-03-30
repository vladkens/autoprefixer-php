var autoprefixer = require('./autoprefixer.js');
var data = '';

process.stdin.on('data', function(chunk) {
    data += chunk;
});

process.stdin.on('end', function() {
    data = JSON.parse(data);
    for (var i in data.css) {
        try {
            data.css[i] = autoprefixer.process(data.css[i], 'autoprefixer', { browsers: data.browsers }).css;
        } catch (e) {
            data.css[i] = 'Error: ' + e.message;
        }
    }
    
    process.stdout.write(JSON.stringify(data.css));
});
