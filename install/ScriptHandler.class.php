<?php

namespace Transitive\Core\Install;

use Composer\Script\Event;

function copyDirectory(string $source, string $dest) {
    $dir = opendir($source);
    @mkdir($dest);
    while(false !== ($file = readdir($dir))) {
        if ($file != '.' && $file != '..' ) {
            if (is_dir($source.'/'.$file))
                copyDir($source.'/'.$file, $dest.'/'.$file);
            else
                copy($source . '/' . $file, $dest . '/' . $file);
        }
    }
    closedir($dir);
}

function setFiles(array $files, $from = '') {
	$from = dirname(dirname(__FILE__)).'/'.$from;

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
				copyDirectory($source, $dest);

			echo ' copying: ', $dest, PHP_EOL;
		}
	}
}

class ScriptHandler
{
    public static function setup(Event $event)
    {
	    @mkdir('config');
		setFiles([
			'config/default.php',
			'presenters',
			'views'
		]);

		if(!file_exists('www') && !file_exists('public_html')) {
			@mkdir('htdocs');
			setFiles(['htdocs/index.php']);
		}
    }
}