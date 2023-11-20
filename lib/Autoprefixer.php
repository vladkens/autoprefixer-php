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
	 * @param   mixed   $browsers
	 */
	public function __construct($browsers = null)
	{
		if (!is_null($browsers)) {
			$this->setBrowsers($browsers);
		}
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
	 * @param   mixed   $css
	 * @param   mixed   $browsers
	 * @throws  RuntimeException        If node runtime unavailable
	 * @throws  AutoprefixerException
	 * @return  array
	 */
	public function compile($css, $browsers = null)
	{
		$return_string = !is_array($css);

		if ($return_string) {
			$css = array($css);
		}

		$data_string = json_encode(array(
			'css'      => $css,
			'browsers' => !is_null($browsers) ? $browsers : $this->browsers
		));

		if ($data_string === false || $data_string === null) {
			$error_message = 'Failed to json_encode: ';
			if (function_exists('json_last_error_msg')) {
				$error_message .= json_last_error_msg();
			} else {
				$error_message .= json_last_error();
			}
			throw new AutoprefixerException($error_message);
		}

		// by default, use OS temp dir
		$error_log_dir = defined('AF_LOG_DIR') ? AF_LOG_DIR : sys_get_temp_dir();
		$error_log_file = $error_log_dir . DIRECTORY_SEPARATOR . 'autoprefixer.error.log';
		if (!file_exists($error_log_file)) {
			@touch($error_log_file);
		}
		if (!is_writable($error_log_file)){
			throw new AutoprefixerException("Error log file '$error_log_file' isn't writable");
		}

		$nodejs = proc_open('node ' . __DIR__ . '/vendor/wrap.js',
			array(array('pipe', 'r'), array('pipe', 'w'), array('file', $error_log_file, 'a')),
			$pipes
		);

		if ($nodejs === false) {
			throw new RuntimeException('Could not reach node runtime');
		}

		$written = $this->fwrite_stream($pipes[0], $data_string);
		fclose($pipes[0]);

		if ($written !== strlen($data_string)) {
			proc_close($nodejs);
			throw new AutoprefixerException(sprintf('Could not write data: %d/%d', $written, strlen($data_string)));
		}

		$output = stream_get_contents($pipes[1]);
		proc_close($nodejs);

		if (!$output) {
			throw new AutoprefixerException('Could not read output');
		}
		$output = json_decode($output, true);

		if (!is_array($output)) {
			throw new AutoprefixerException('Broken output');
		}

		$error_messages = '';
		foreach ($output as $key => &$value) {
			if (preg_match('/^Error:\s*/i', $value)) {
				$value = preg_replace('/^Error:\s*/i', '', $value);
				$error_messages .= "In css[$key]: $value \n";
			}
		}

		if (strlen($error_messages) > 0) {
			throw new AutoprefixerException($error_messages);
		}

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
