<?php

namespace Transitive;

require_once __DIR__.'/../vendor/autoload.php';

use Transitive\Core\FrontController as FrontController;
use Transitive\Core\Route as Route;

require 'config/default.php';

$transit = new FrontController();

$transit->addRouter(new Core\PathRouter(PRESENTERS, VIEWS));

$transit->execute(@$_GET['request'] ?? 'index');

$transit->layout = function ($transit) {
    global $timed; ?>

<!DOCTYPE html>
<!--[if lt IE 7]><html class="lt-ie9 lt-ie8 lt-ie7" xmlns="http://www.w3.org/1999/xhtml"><![endif]-->
<!--[if IE 7]>   <html class="lt-ie9 lt-ie8" xmlns="http://www.w3.org/1999/xhtml"><![endif]-->
<!--[if IE 8]>   <html class="lt-ie9" xmlns="http://www.w3.org/1999/xhtml"><![endif]-->
<!--[if gt IE 8]><html class="get-ie9" xmlns="http://www.w3.org/1999/xhtml"><![endif]-->
<head>
<meta charset="UTF-8">
<?php $transit->printMetas() ?>
<?php $transit->printTitle('TITLE') ?>
<base href="<?php echo (constant('SELF') == null) ? '/' : constant('SELF').'/'; ?>" />
<?php $transit->printStyles() ?>
<?php $transit->printScripts() ?>
</head>
<body>
	<div id="wrapper">
		<main role="main">
			<?php $transit->printContent(); ?>
		</main>
	</div>
</body>
</html>

<?php

};

$transit->print();
