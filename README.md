# Autoprefixer PHP

[Autoprefixer](https://github.com/ai/autoprefixer) is a tool
to parse CSS and add vendor prefixes to CSS rules using values
from the [Can I Use](http://caniuse.com/). This library provides
PHP integration with [Node.js](http://nodejs.org/) application.

Write your CSS rules without vendor prefixes (in fact, forget about them
entirely):

```php
$autoprefixer = new Autoprefixer();
$css      = 'a { transition: transform 1s }';
$prefixed = $autoprefixer->compile($css);
```

Autoprefixer uses the data on current browser popularity
and properties support to apply prefixes for you:

```css
a {
  -webkit-transition: -webkit-transform 1s;
  transition: -ms-transform 1s;
  transition: transform 1s
}
```

You can ask me any questions by e-mail: <vladkens@yandex.ru>

## Install

- First you need install [Node.js](http://nodejs.org/) in your server.

### Install via Composer.

- Create a composer.json file in your project root:

    ```js
    {
        "require": {
            "vladkens/autoprefixer": "dev-master"
        }
    }
    ```

- Write in the project root:

    Linux: `php composer.phar install`
    
    Windows: `composer.bat install`

- In `index.php` write:

    ```php
    require_once 'vendor/autoload.php';
    ```

## Usage

```php
$autoprefixer = new Autoprefixer();
$css_one = 'a { color: black; }';
$css_two = 'a { color: white; }';

// If need compile one css. Function return compied CSS.
$prefixed = $autoprefixer->compile($css_one);
echo $prefixed;

// If need compile many css in one time. Function return array of compiled CSS.
$prefixed = $autoprefixer->(array($css_one, $css_two));
echo $prefixed[0] . "\n" . $prefixed[1];

// If occurred error in compile time Autoprefixer throw exception named `AutoprefixerException`.
// You need process it.
try {
    $autoprefixer->compile($css_one);
} catch (AutoprefixerException $error) {
    echo $error->getMessage();
} catch (Exception $error) {
    echo $error->getMessage();
}

// If you want to choose specific browsers
$autoprefixer = new Autoprefixer('last 1 version'); // one rule
// or 
$autoprefixer = new Autoprefixer(array('ff > 2', '> 2%', 'ie8')); // many rules
// or
$autoprefixer->setBrowsers('last 1 version');
// or change browsers on a one iteration
$autoprefixer->compile($css_one, 'last 1 version');

// Also, you can get latest version Autoprefixer using
$autoprefixer->update();
```

## Speed
On my Intel i5-3210M 2.5GHz and HDD 5200 RPM GitHub styles compiled in 390 ms.

## License
[MIT](https://raw.github.com/vladkens/autoprefixer-php/master/LICENSE)

## More docs
See https://github.com/ai/autoprefixer/blob/master/README.md