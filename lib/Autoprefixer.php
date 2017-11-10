<?php

/**
 * @author Vlad Pronsky <vladkens@yandex.ru>
 * @license https://raw.github.com/vladkens/autoprefixer-php/master/LICENSE MIT
 */

class Autoprefixer
{
    /**
     * @var array
     */
    private $browsers = array();
    /**
     * @var bool
     */
    private $sourceMap;

    /**
     * @param   mixed $browsers
     * @param bool $sourceMap
     */
    public function __construct($browsers = null, $sourceMap = false)
    {
        if (!is_null($browsers)) {
            $this->setBrowsers($browsers);
        }
        $this->sourceMap = $sourceMap;
    }
    
    /**
     * Set browsers info.
     * @param   mixed   $browsers   String if one argument, array if many.
     * @return  void
     */
    public function setBrowsers($browsers)
    {
        if (!is_array($browsers)) {
            $browsers = array($browsers);
        }
        $this->browsers = $browsers;
    }

    /**
     * @param   mixed $css
     * @param   mixed $browsers
     * @param null $sourceMap
     * @return array If node runtime unavailable
     * @throws AutoprefixerException
     */
    public function compile($css, $browsers = null, $sourceMap = null)
    {
        if ($return_string = !is_array($css)) {
            $css = array($css);
        }
        
        $nodejs = proc_open('node ' . __DIR__ . '/wrapper/wrapper.js',
            array(array('pipe', 'r'), array('pipe', 'w')),
            $pipes
        );
        if ($nodejs === false) {
            throw new RuntimeException('Could not reach node runtime');
        }

        $this->fwrite_stream($pipes[0],
            json_encode(array(
                'css' => $css,
                'options' => array(
                    'browsers' => !is_null($browsers) ? $browsers : $this->browsers),
                    'map'      => $sourceMap === null ? $this->sourceMap : $sourceMap
                )
            ));
        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]);
        $output = json_decode($output, true);
        fclose($pipes[1]);
        
        proc_close($nodejs);
        
        $error_messages = '';
        foreach ($output as $key => &$value) {
            if ($value['error'] !== false) {
                $error_messages .= "In css[$key]: {$value['error']} \n";
            }
        }
        
        if (strlen($error_messages) > 0) {
            throw new AutoprefixerException($error_messages);
        }
        
        return $return_string ? $output[0]['css'] : array_map(function($r) {return $r['css'];}, $output);
    }

    /**
     * Download autoprefixer updates.
     * @return bool True if updated.
     * @throws AutoprefixerException
     */
    public function update()
    {
        $currentVersion = $this->getAutoprefixerVersion();
        $cwd = getcwd();
        chdir(__DIR__.'/wrapper');
        $output = [];
        $result = 0;
        exec('npm -q update 2>&1', $output, $result);
        chdir($cwd);
        if($result) {
            throw new AutoprefixerException("Error running npm update: returned $result\n".implode("\n", $output));
        }
        return $this->getAutoprefixerVersion() != $currentVersion;
    }

    public function getAutoprefixerVersion() {
        $package = json_decode(file_get_contents(__DIR__ . '/wrapper/node_modules/autoprefixer/package.json'), true);
        return $package['version'];
    }
    
    /**
     * @param   resource  $fp         php://stdin
     * @param   string    $string
     * @param   int       $buflen
     * @return  string
     */
    private function fwrite_stream($fp, $string, $buflen = 4096)
    {
        for ($written = 0, $len = strlen($string); $written < $len; $written += $fwrite) {
            $fwrite = fwrite($fp, substr($string, $written, $buflen));
            if ($fwrite === false) {
                return $written;
            }
        }
        
        return $written;
    }
    
};
