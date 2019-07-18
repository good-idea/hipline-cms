<?php

function fetchPage($uri, $children = false, $onlyVisibleChildren = true) {
	$page = kirby()->site()->pages()->findByURI($uri);
	if ($page) {
		return $page->getPublicContent($children, $onlyVisibleChildren);
	} else {
		return null;
	} 
}

function getQueryParam($url, $param) {
	$queryString = parse_url($url, PHP_URL_QUERY);
	parse_str($queryString, $vars);
	if (array_key_exists($param, $vars)) {
		return $vars[$param];
	}
	return '';
}

function fetchMeta($uri) {
	$page = kirby()->site()->pages()->find($uri);
	return meta($page);
}

function getFilename($url) {
	$arr = explode('/', $url);
	$last = array_pop($arr);
	$withParams = explode('?', $last);
	$filename = $withParams[0];
	return $filename;
}

function downloadImageToPage($page, $imageUrl) {
	$filename = getFilename($imageUrl);
	$ch = curl_init($imageUrl);
	$fp = fopen((string)$page->root() . DS . $filename, 'wb');
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);
}

c::set('headers', array(
	header('Access-Control-Allow-Origin: *')
));

c::set('routes', array(
	array(
		'method' => 'GET',
		'pattern' => '/',
		'action' => function() {
			return go('/panel');
		}
	),
	array(
		'method' => 'GET',
		'pattern' => 'api/meta',
		'action' => function() {
			$uri = getQueryParam($_SERVER['REQUEST_URI'], 'uri');
			$meta = fetchMeta($uri);
			consoleLog($uri);
			return response::json(json_encode($meta));
		}
	),
	array(
		'method' => 'GET',
		'pattern' => 'api/initial',
		'action' => function() {
			try {
				$content = new StdClass();
				$uri = getQueryParam($_SERVER['REQUEST_URI'], 'uri');
				$content->meta = fetchMeta('/');
				$content->home = fetchPage('/home');
				$content->choreographers = fetchPage('/choreographers', 1);
				$content->classes = fetchPage('/classes', 2, true);
				$content->sourcePasses = fetchPage('/passes', 1, false);
				$content->infoPages = array_map(function($page) {
					return fetchPage((string)$page['uid'], 1);
				}, kirby()->site()->pages()->visible()->filterBy('intendedTemplate', 'section')->toArray());
				// $content->community = fetchPage('/community', 1);
				// $content->about = fetchPage('/about', 1);
				return response::json(json_encode($content));
			} catch (Exception $e) {
				consoleLog('*** Error ***');
				consoleLog($e->getMessage());
				consoleLog($e->getTraceAsString());
				return response::json($e->getMessage());
				
			}
		}
	),
	array(
		'method' => 'GET',
		'pattern' => 'api/page/(:all)',
		'action' => function($path) {
			$content = new StdClass();
			$content->home = fetchPage('/home');
			$content->choreographers = fetchChoreographers();
			return response::json(json_encode($content));
		}
	),
))
?>
																																																				
