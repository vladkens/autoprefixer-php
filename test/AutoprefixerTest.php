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
    
    public function provider()
    {
        $this->settings = $this->getSettings();
        
        $path = __DIR__ .DIRECTORY_SEPARATOR .'..' .DIRECTORY_SEPARATOR .'lib'.DIRECTORY_SEPARATOR;
        if (file_exists($path.'Autoprefixer.php')&&file_exists($path.'AutoprefixerException.php')) {
            require_once $path . 'Autoprefixer.php';
            require_once $path . 'AutoprefixerException.php';
            $this->autoprefixer = new Autoprefixer();
        } else
            throw new Exception('Could not find Autoprefixer.php');
            
        $this->scan(__DIR__);
        
        $result = array();
        foreach (static::$tests as $name => $value)
            $result[] = array($name);
        return $result;
    }
    
    protected function scan($path)
    {
        foreach (scandir($path) as $name) {
            if (is_dir($name) && $name != '.' && $name != '..')
                $this->scan($path . DIRECTORY_SEPARATOR . $name);
                
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
        return array(
            array(
                array('default'),
                array('chrome 25', 'opera 12')
            ),
            array(
                array('keyframes'),
                array('safari 6', 'chrome 25', 'opera 12')),
            array(
                array('border-radius'),
                array('safari 4', 'ff 3.6')
            ),
            array(
                array('vendor-hack', 'mistakes'),
                array('none')
            ),
            array(
                array('gradient'),
                array('chrome 25', 'opera 12', 'android 2.3')
            ),
            array(
                array('flexbox', 'flex-rewrite'),
                array('safari 6',  'chrome 25', 'ff 21', 'ie 10')
            ),
            array(
                array('selectors', 'placeholder'),
                array('chrome 25', 'ff 22', 'ie 10')
            ),
            array(
                array('intrinsic'),
                array('chrome 25', 'ff 22')
            ),
            array(
                array('old'),
                array('none')
            ),
            array(
                array('ie-transition'),
                array('ie > 0')
            )
        );
    }
    
};

/*$at = new AutoprefixerTest();
echo '<pre>';
print_r([[0,0],[1,1]]);
print_r($at->tests);*/