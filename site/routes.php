<?php

function fetchPage($uri, $children = false) {
  return kirby()->site()->pages()->findByURI($uri)->getPublicContent($children);
}

// function fetchSection($section) {
//   $pages = array();
//   foreach(kirby()->site()->pages()->find($section) as $page) {
//     if ($page) array_push($pages, $page);
//   }
//   return $page;
// }
//
// function fetchChoreographers() {
//   $choreographers = array();
//   foreach (kirby()->site()->pages()->find('choreographers')->children()->visible() as $page) {
//     array_push($choreographers, $page->getPublicContent());
//   }
//   return $choreographers;
// }

c::set('routes', array(
  array(
    'method' => 'GET',
    'pattern' => 'api/all',
    'action' => function() {
      $content = new StdClass();
      $content->home = fetchPage('/home');
      $content->choreographers = fetchPage('/choreographers', 1);
      $content->classes = fetchPage('/classes', 2);
      $content->community = fetchPage('/community', 1);
      return response::json(json_encode($content));
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
