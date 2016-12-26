var autoprefixer = require('./autoprefixer.js');
var data = '';


var processCss = function() {
    var result = autoprefixer.process.apply(autoprefixer, arguments);
    var warns  = result.warnings().map(function (i) {
        delete i.plugin;
        return i.toString();
    });
    var map = result.map ? result.map.toString() : null;
    return { css: result.css, map: map, warnings: warns };
};


process.stdin.on('data', function(chunk) {
    data += chunk;
});

process.stdin.on('end', function() {
    data = JSON.parse(data);
    for (var i in data.css) {
        try {
            data.css[i] = processCss(data.css[i], { browsers: data.browsers }).css;
        } catch (e) {
            data.css[i] = 'Error: ' + e.message;
        }
    }
    
    process.stdout.write(JSON.stringify(data.css));
});