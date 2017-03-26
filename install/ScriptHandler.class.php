<?php

namespace Transitive\Core\Install;

use Composer\Script\Event;

class ScriptHandler
{
    private static function _copyDirectory(string $source, string $dest) {
        $dir = opendir($source);
        @mkdir($dest);
        while(false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                if (is_dir($source.'/'.$file))
                    self::_copyDirectory($source.'/'.$file, $dest.'/'.$file);
                elseif(!file_exists($dest.'/'.$file)) {
                    copy($source.'/'.$file, $dest.'/'.$file);
                    echo ' + copying: ', $dest.'/'.$file, PHP_EOL;
                }
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

            if(is_file($source) && !file_exists($dest)) {
                copy($source, $dest);
                echo ' + copying: ', $dest, PHP_EOL;
            } elseif(is_dir($source))
                self::_copyDirectory($source, $dest);
        }
    }

    public static function setup(Event $event)
    {
        self::setFiles([
            'presenters',
            'views',
        ]);

        if(!file_exists('www') && !file_exists('public_html')) {
            @mkdir('htdocs');
            self::setFiles(['htdocs/index.php']);
        }
    }
}
