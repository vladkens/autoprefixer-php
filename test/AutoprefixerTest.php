<?php

class AutoprefixerTest extends PHPUnit_Framework_TestCase
{
    protected $autoprefixer;
    protected $settings = array();
    static protected $tests = array();
    
    /**
     * @dataProvider provider
     */
    public function testCompiler($name)
    {
        $this->assertEquals(static::$tests[$name][0], static::$tests[$name][1]);
    }
    
    /**
     * @beforeClass
     */
    public static function updateAutoprefixer()
    {
        $autoprefixer = new Autoprefixer();
        $autoprefixer->update();
    }
    
    public function provider()
    {
        $this->settings = $this->getSettings();
        
        $path = __DIR__ . DIRECTORY_SEPARATOR . '..' .DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR;
        if (file_exists($path . 'Autoprefixer.php') && file_exists($path . 'AutoprefixerException.php')) {
            require_once $path . 'Autoprefixer.php';
            require_once $path . 'AutoprefixerException.php';
            $this->autoprefixer = new Autoprefixer();
        } else {
            throw new Exception('Could not find Autoprefixer.php');
        }
        $this->scan(__DIR__ . DIRECTORY_SEPARATOR . 'cases' . DIRECTORY_SEPARATOR);
        
        $result = array();
        foreach (static::$tests as $name => $value) {
            $result[] = array($name);
        }
        return $result;
    }
    
    protected function scan($path)
    {
        foreach (scandir($path) as $name) {
            if (is_dir($name) && $name != '.' && $name != '..') {
                $this->scan($path . DIRECTORY_SEPARATOR . $name);
            }
                
            if (substr($name, -4) != '.css' || substr($name, -8) == '.out.css') continue;
            
            $this->register(substr($name, 0, strrpos($name, '.')), $path);
        }
    }
    
    protected function register($name, $path)
    {
        $input  = file_get_contents($path . DIRECTORY_SEPARATOR . $name . '.css');
        $output = file_exists($path . DIRECTORY_SEPARATOR . $name . '.out.css') ?
            file_get_contents($path . DIRECTORY_SEPARATOR . $name . '.out.css') : $input;
            
        $browsers = $this->settings[0][1];
        foreach ($this->settings as $value) {
            $res = array_search($name, $value[0]);
            if ($res !== false) {
                $browsers = $value[1];
                break;
            }
        }
        
        $input  = $this->clear($this->autoprefixer->compile($input, $browsers));
        $output = $this->clear($output);
        
        static::$tests[$name] = array($input, $output);
    }
    
    protected function clear($string)
    {
        $string = preg_replace('/\/\*.*?\*\//ms', '', $string);
        return preg_replace('/\s+/', ' ', trim($string));
    }
    
    protected function getSettings()
    {
        return array_values(array(
            'compiler' => array(
                array('default'),
                array('Chrome 25', 'Opera 12')
            ),
            'cleaner' => array(
                array('vendor-hack', 'mistakes'),
                array()
            ),
            'borderer' => array(
                array('border-radius'),
                array('Safari 4', 'Firefox 3.6')
            ),
            'cascader' => array(
                array('cascade'),
                array('Chrome > 19', 'Firefox 21', 'IE 10')
            ),
            'keyframer' => array(
                array('keyframes'),
                array('Chrome > 19', 'Opera 12')
            ),
            'flexboxer' => array(
                array('flexbox', 'flex-rewrite', 'double'),
                array('Chrome > 19', 'Firefox 21', 'IE 10')
            ),
            'without3d' => array(
                array('3d-transform'),
                array('Opera 12', 'Explorer > 0')
            ),
            'uncascader' => array(
                array('uncascade'),
                array('Firefox 15')
            ),
            'gradienter' => array(
                array('gradient'),
                array('Chrome 25', 'Opera 12', 'Android 2.3')
            ),
            'selectorer' => array(
                array('selectors', 'placeholder'),
                array('Chrome 25', 'Firefox > 17', 'Explorer 10')
            ),
            'intrinsicer' => array(
                array('intrinsic', 'multicolumn'),
                array('Chrome 25', 'Firefox 22')
            ),
            'backgrounder' => array(
                array('background-size'),
                array('Firefox 3.6', 'Android 2.3')
            )
        ));
    }
};
