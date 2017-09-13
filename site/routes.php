<?php

function fetchPage($uri, $children = false) {
  return kirby()->site()->pages()->findByURI($uri)->getPublicContent($children);
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

c::set('routes', array(
  array(
    'method' => 'GET',
    'pattern' => 'api/initial',
    'action' => function() {
      $content = new StdClass();
      $content->home = fetchPage('/home');
      $content->choreographers = fetchPage('/choreographers', 1);
      $content->classes = fetchPage('/classes', 2);
      $content->community = fetchPage('/community', 1);
      $content->about = fetchPage('/about', 1);
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

  array(
    'method' => 'POST',
    'pattern' => 'api/sync/passes',
    'action' => function() {
      try {
        $response = new StdClass();
        $response->added = array();
        $response->updated = array();
        $passes = kirby()->site()->pages()->find('classes')->children()->find('passes');
        foreach ($_POST['passes'] as $MBOPass) {
          $passPage = $passes->children()->findBy('mboid', $MBOPass['ProductID']);
          if ($passPage) {
            $updatedContent = array();
            if (array_key_exists('Count', $MBOPass)) $updatedContent['classcount'] = $MBOPass['Count'];
            if (array_key_exists('Price', $MBOPass)) $updatedContent['price'] = $MBOPass['Price'];
            $passPage->update($updatedContent);
            array_push($response->updated, $MBOPass['Name']);
          } else {
            $newContent = array();
            if (array_key_exists('Name', $MBOPass)) $newContent['title'] = $MBOPass['Name'];
            if (array_key_exists('ID', $MBOPass)) $newContent['mboid'] = $MBOPass['ProductID'];
            if (array_key_exists('Count', $MBOPass)) $newContent['classcount'] = $MBOPass['Count'];
            if (array_key_exists('Price', $MBOPass)) $newContent['price'] = $MBOPass['Price'];
            array_push($response->added, $MBOPass['Name']);
            $newPassPage = kirby()->site()->pages()->create(
              'classes/passes/' . str::slug($MBOPass['Name']),
              'pass',
              $newContent
            );
          }
        }
        return response::json(json_encode($response));
      } catch (Exception $e) {
        return response::json($e->getMessage());
      }
    }
  ),

  array(
    'method' => 'POST',
    'pattern' => 'api/sync/classes',
    'action' => function() {
      try {
        $response = new StdClass();
        $response->added = array();
        $response->updated = array();
        $classes = kirby()->site()->pages()->find('classes')->children()->find('types');
        foreach ($_POST['classes'] as $MBOClass) {
          $classPage = $classes->children()->findBy('mboid', $MBOClass['ID']);
          if ($classPage) {
            $updatedContent = array();
            if (array_key_exists('Description', $MBOClass)) $updatedContent['mbodescription'] = $MBOClass['Description'];
            $classPage->update($updatedContent);
            array_push($response->updated, $MBOClass['Name']);
          } else {
            $newContent = array();
            if (array_key_exists('Description', $MBOClass)) $newContent['mbodescription'] = $MBOClass['Description'];
            if (array_key_exists('Name', $MBOClass)) $newContent['title'] = $MBOClass['Name'];
            if (array_key_exists('ID', $MBOClass)) $newContent['mboid'] = $MBOClass['ID'];
            $newClassPage = kirby()->site()->pages()->create(
              'classes/types/' . str::slug($MBOClass['Name']),
              'class',
              $newContent
            );
            array_push($response->added, $MBOClass['Name']);
            if (array_key_exists('ImageURL', $MBOClass)) downloadImageToPage($newClassPage, $MBOClass['ImageURL']);
          }
        }
        return response::json(json_encode($response));
      } catch (Exception $e) {
        return response::json($e->getMessage());
      }
    }
  ),

  array(
    'method' => 'POST',
    'pattern' => 'api/sync/staff',
    'action' => function() {
      try {
        $response = new StdClass();
        $response->added = array();
        $response->updated = array();
        $staff = kirby()->site()->pages()->find('choreographers');
        foreach($_POST['staff'] as $staffMember) {
          $staffMemberPage = $staff->children()->findBy('mboid', $staffMember['ID']);
          if ($staffMemberPage) {
            $updatedContent = array();
            if (array_key_exists('Bio', $staffMember)) $updatedContent['mbobio'] = $staffMember['Bio'];
            $staffMemberPage->update($updatedContent);
            array_push($response->updated, $staffMember['Name']);
          } else {
            $firstName = $staffMember;
            $newContent = array();
            $newContent['mboid'] = $staffMember['ID'];
            $newContent['title'] = $staffMember['Name'];
            if (array_key_exists('Bio', $staffMember)) $newContent['mbobio'] = $staffMember['Bio'];
            if (array_key_exists('FirstName', $staffMember)) $newContent['firstname'] = $staffMember['FirstName'];
            if (array_key_exists('LastName', $staffMember)) $newContent['lastname'] = $staffMember['LastName'];
            $newStaffMemberPage = kirby()->site()->pages()->create(
              'choreographers/' . str::slug($staffMember['Name']),
              'choreographer',
              $newContent
            );
            if (array_key_exists('ImageURL', $staffMember)) downloadImageToPage($newStaffMemberPage, $staffMember['ImageURL']);
            array_push($response->added, $staffMember['Name']);
          }
        }
        return response::json(json_encode($response));
      } catch (Exception $e) {
        return response::json($e->getMessage());
      }
    }
  )
))

?>
