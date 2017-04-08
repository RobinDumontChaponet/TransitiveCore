<?php

$view->setTitle(http_response_code());
$view->linkStylesheet('style/genericHttpErrorHandler.css');

$view->content = function ($data) {
?>

<main role="main" id="main">
	<img src="https://http.cat/<?= http_response_code() ?>" alt="<?= http_response_code() ?>" title="<?= Transitive\Utils\HttpRequest::http_response_message() ?>" />
</main>

<?php

};
