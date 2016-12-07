<?php

namespace Transitive\Core;

if (!function_exists('http_response_code')) {
    function http_response_code($newcode = null) {
        static $code = 200;
        if($newcode !== null) {
            header('X-PHP-Response-Code: '.$newcode, true, $newcode);
            if(!headers_sent())
                $code = $newcode;
        }

        return $code;
    }
}

function getBestSupportedMimeType($mimeTypes = null) {
    // Values will be stored in this array
    $AcceptTypes = Array ();

    // Accept header is case insensitive, and whitespace isn’t important
    $accept = strtolower(str_replace(' ', '', $_SERVER['HTTP_ACCEPT']));
    // divide it into parts in the place of a ","
    $accept = explode(',', $accept);
    foreach ($accept as $a) {
        // the default quality is 1.
        $q = 1;
        // check if there is a different quality
        if (strpos($a, ';q=')) {
            // divide "mime/type;q=X" into two parts: "mime/type" i "X"
            list($a, $q) = explode(';q=', $a);
        }
        // mime-type $a is accepted with the quality $q
        // WARNING: $q == 0 means, that mime-type isn’t supported!
        $AcceptTypes[$a] = $q;
    }
    arsort($AcceptTypes);

    // if no parameter was passed, just return parsed data
    if (!$mimeTypes) return $AcceptTypes;

    $mimeTypes = array_map('strtolower', (array)$mimeTypes);

    // let’s check our supported types:
    foreach ($AcceptTypes as $mime => $q) {
       if ($q && in_array($mime, $mimeTypes)) return $mime;
    }
    // no mime-type found
    return null;
}

class FrontController {
	/**
	 * @var Request
	 */
	private $binder;

	/**
	 * @var array Router
	 */
	private $routers;

	public $layout;

	private $contentType;

	public function __construct(string $queryURL=null)
	{
		$queryURL = (!empty($queryURL)) ? $queryURL : 'index';
		$this->binder = new Binder(ROOT_PATH.'/presenters/'.$queryURL, ROOT_PATH.'/views/'.$queryURL);

		$this->layout = function () { ?>

<!DOCTYPE html>
<!--[if lt IE 7]><html class="lt-ie9 lt-ie8 lt-ie7" xmlns="http://www.w3.org/1999/xhtml"><![endif]-->
<!--[if IE 7]>   <html class="lt-ie9 lt-ie8" xmlns="http://www.w3.org/1999/xhtml"><![endif]-->
<!--[if IE 8]>   <html class="lt-ie9" xmlns="http://www.w3.org/1999/xhtml"><![endif]-->
<!--[if gt IE 8]><html class="get-ie9" xmlns="http://www.w3.org/1999/xhtml"><![endif]-->
<head>
<meta charset="UTF-8">
<?php $this->printMetas() ?>
<?php $this->printTitle('Default layout') ?>
<base href="<?php echo (constant('SELF') == null) ? '/' : constant('SELF').'/'; ?>" />
<?php $this->printStyles() ?>
<!--[if lt IE 9]><script type="text/javascript" src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
<?php $this->printScripts() ?>
</head>
<body>
<div id="wrapper">
<?php $this->printContent(); ?>
</div>
</body>
</html>

<?php  };
	}

    /**
     * @return Binder
     */
    public function getBinder():Binder
    {
        return $this->binder;
    }

    /**
     * @param Binder $binder
     */
    public function setBinder(Binder $binder):void
    {
        $this->binder = $binder;
    }

    /**
     * @param bool $isJSON
     */
    public function execute(bool $isJSON = false):void
    {
/*  // @ IN Router/Route
		if(!is_file($this->binder->getPresenterPath())) {
            http_response_code(404);
            $_SERVER['REDIRECT_STATUS'] = 404;

            $this->binder->setPresenterPath('genericHttpErrorHandler.presenter.php');
            if(!is_file(self::$viewIncludePath.'genericHttpErrorHandler.view.php'))
                $this->binder->setViewPath('');
            else
                $this->binder->setViewPath(self::$viewIncludePath.'genericHttpErrorHandler.view.php');
		}
*  IN Router/Route */
/*
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
			echo 'ajaxed';
		if(!empty($_SERVER['HTTP_ACCEPT']) && strtolower($_SERVER['HTTP_ACCEPT']) == 'application/vnd.transitive.document+json')
			echo 'transitive.document';
*/



		$this->contentType = getBestSupportedMimeType(array('application/xhtml+xml', 'text/html', 'application/json', 'application/vnd.transitive.document+json', 'application/vnd.transitive.document+xml', 'application/vnd.transitive.document+yaml'));

		if(!empty($contentType)) {
			header('Content-Type: '.$contentType);

			if($contentType=='application/vnd.transitive.document+json' || $contentType=='application/json') {
				header('Expires: '.gmdate('D, d M Y H:i:s').' GMT');
				header('Cache-Control: public, max-age=60');
			}
		}


		header('Vary: X-Requested-With,Content-Type');

		if(!$this->binder->getView()->hasContent()) {
            http_response_code(204);
            $_SERVER['REDIRECT_STATUS'] = 404;
		}
		$this->binder->execute($isJSON);

/*
		if($this->binder->isJSON() && !headers_sent())
			header('Content-Type: application/json');
*/
    }

    public function printMetas():void
    {
        $this->binder->getView()->printMetas();
    }

    /**
     * @param string $prefix
     * @param string $separator
     * @param string $endSeparator
     *
     * @return string
     */
    public function getTitle(string $prefix = '', string $separator = ' | ', string $endSeparator = ''):string
    {
        $title = $this->binder->getView()->getTitle();

        if(!empty($title))
            return $prefix.$separator.$title.$endSeparator;

        return $prefix;

    }

    /**
     * @param string $prefix
     * @param string $separator
     * @param string $endSeparator
     */
    public function printTitle(string $prefix = '', string $separator = ' | ', string $endSeparator = ''):void
    {
        echo '<title>';
        echo $this->getTitle($prefix, $separator, $endSeparator);
        echo '</title>';
    }

    public function printStyles():void
    {
        $this->binder->getView()->printStyles();
    }

    public function printScripts():void
    {
        $this->binder->getView()->printScripts();
    }

	/**
     * @param string $key
     */
    public function getContent(string $key = null)
    {
        return $this->binder->getContent($key);
    }

    /**
     * @param string $key
     */
    public function printContent(string $key = null):void
    {
        $this->binder->printContent($key);
    }

	public function getHead():ViewRessource
    {
		return $this->binder->getHead();
    }

    public function printHead():void
    {
		$this->binder->printHead();
    }

    public function getBody()
    {
		return $this->binder->getBody();
    }
    public function printBody():void
    {
		$this->binder->printBody();
    }

	public function getDocument()
	{
		return $this->binder->getDocument();
	}
    public function printDocument():void
    {
		$this->binder->printDocument();
    }


/*
    public function __debugInfo():void
    {
        // TODO: implement here
    }
*/

    public function __toString():string
    {
        // TODO: implement here
    }

    public function print($contentType = null):void
    {
	    if($contentType==null)
	    	$contentType = $this->contentType;

		switch($contentType) {
			case 'application/vnd.transitive.document+json':
				echo $this->getDocument();
			break;
			case 'application/vnd.transitive.document+xml':
				echo $this->getDocument()->asXML;
			break;
			case 'application/vnd.transitive.document+yaml':
				echo $this->getDocument()->asYAML;
			break;
			case 'application/json':
				echo '{"case":"json"}';
			break;
			default:
				switch(gettype($layout = $this->layout)) {
		            case 'string': case 'integer': case 'double':
		                echo $layout;
		            break;
		            case 'object':
		                if(get_class($layout) == 'Closure')
		                    $layout($this);
		            break;
		            default:
		                echo 'No Layout';
		        }
		}
    }

    /**
     * @return array
     */
    public function getRouters():array
    {
		return $this->routers;
    }

    /**
     * @param array $routers
     */
    public function setRouters(array $routers):void
    {
		$this->routers = $routers;
    }

    /**
     * @param Router $router
     */
    public function addRouter(Router $router):void
    {
		$this->routers[] = $router;
    }

    public function removeRouter(Router $router):bool
    {
        // TODO: implement here
    }
}
