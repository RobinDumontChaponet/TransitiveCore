<?php

namespace Transitive\Core\Install;

use Composer\Script\Event;

class ScriptHandler
{
	private static function _copyDirectory(string $source, string $dest) {
		$dir = opendir($source);
	    @mkdir($dest);
	    while(false !== ($file = readdir($dir))) {
	        if ($file != '.' && $file != '..' ) {
	            if (is_dir($source.'/'.$file))
	                self::_copyDirectory($source.'/'.$file, $dest.'/'.$file);
	            else
	                copy($source . '/' . $file, $dest . '/' . $file);
	        }
	    }
	    closedir($dir);
	}

	public static function setFiles(array $files, $from = null) {
		$from = $from ?? dirname(dirname(__FILE__));

		foreach($files as $dest) {
			if(is_array($dest)) {
				$source = $from.'/'.$dest[0];
				$dest = $dest[1];
			}  else
				$source = $from.'/'.$dest;

			if(!file_exists($dest)) {
				if(is_file($source))
					copy($source, $dest);
				elseif(is_dir($source))
					self::_copyDirectory($source, $dest);

				echo ' copying: ', $dest, PHP_EOL;
			}
		}
	}


    public static function setup(Event $event)
    {
	    @mkdir('config');
		self::setFiles([
			'config/default.php',
			'presenters',
			'views'
		]);

		if(!file_exists('www') && !file_exists('public_html')) {
			@mkdir('htdocs');
			self::setFiles(['htdocs/index.php']);
		}
    }
}