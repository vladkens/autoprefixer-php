<?php

class Autoprefixer
{
    /**
     * @param   mixed   $css
     * @param   mixed   $browsers
     * @throw   AutoprefixerException
     * @return  array
     */
    public function compile($css, $browsers = null)
    {
        if ($return_string = !is_array($css)) $css = array($css);
        
        $nodejs = proc_open('node ' . __DIR__ . '/vendor/wrap.js',
            array(array('pipe', 'r'), array('pipe', 'w')),
            $pipes);
            
        if ($nodejs) {
            $this->fwrite_stream($pipes[0],
                json_encode(array('css' => $css, 'browsers' => $browsers)));
            fclose($pipes[0]);
            
            $output = stream_get_contents($pipes[1]);
            $output = json_decode($output);
            fclose($pipes[1]);
        }
        
        proc_close($nodejs);
        
        $error_messages = '';
        foreach ($output as $key => $value) {
            if (preg_match('/^Error:\s*/i', $value)) {
                $value = preg_replace('/^Error:\s*/i', '', $value);
                $error_messages .= "In css[$key]: $value \n";
            }
        }
        
        if (strlen($error_messages) > 0)
            throw new AutoprefixerException($error_messages);
        
        return $return_string ? $output[0] : $output;
    }
    
    /**
     * Download autoprefixer updates.
     * @return  bool    True if updated.
     */
    public function update()
    {
        $update_url = 'https://raw.github.com/ai/autoprefixer-rails/master/vendor/autoprefixer.js';
        $local_path = __DIR__ . '/vendor/autoprefixer.js';
        $new = file_get_contents($update_url);
        $old = file_get_contents($local_path);
        
        if (md5($new) == md5($old)) return false;
        
        file_put_contents($local_path, $new);
        return true;
    }
    
    /**
     * @param   object  $fp         php://stdin
     * @param   string  $string
     * @param   int     $buflen
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
