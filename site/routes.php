<?php

function fetchPage($uri, $children = false, $onlyVisibleChildren = true) {
  return kirby()->site()->pages()->findByURI($uri)->getPublicContent($children, $onlyVisibleChildren);
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
    'pattern' => 'api/initial',
    'action' => function() {
      $content = new StdClass();
      $content->home = fetchPage('/home');
      $content->choreographers = fetchPage('/choreographers', 1)['children'];
		$content->classtypes = fetchPage('/classtypes', 2, true);
		$content->sourcePasses = fetchPage('/passes', 1, false);
		$content->infoPages = array_map(function($page) {
			return fetchPage((string)$page['uid'], 1);
		}, kirby()->site()->pages()->visible()->filterBy('intendedTemplate', 'section')->toArray());
      // $content->community = fetchPage('/community', 1);
      // $content->about = fetchPage('/about', 1);
      try {
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

  array(
    'method' => 'POST',
    'pattern' => 'api/sync/passes',
    'action' => function() {
			try {

				$response = new StdClass();
				$response->added = array();
				$response->updated = array();
				$response->errors = array();
				$passes = kirby()->site()->pages()->find('passes');


				foreach ($_POST['passes'] as $MBOPass) {
					$assignId = array_key_exists('ProductID', $MBOPass) ? $MBOPass['ProductID'] : "membership-" . (string)$MBOPass['ID'];
					// consoleLog($assignId);
					$passPage = $passes->children()->findBy('mboid', $assignId);
					if (!$passPage) $passPage = $passes->children()->findBy('slug', str::slug($MBOPass['Name']));
					if ($passPage) {
						$updatedContent = array();
						if (array_key_exists('Count', $MBOPass)) $updatedContent['classcount'] = $MBOPass['Count'];
						if (array_key_exists('Price', $MBOPass)) $updatedContent['price'] = $MBOPass['Price'];
						if (array_key_exists('Price', $MBOPass)) consoleLog($MBOPass['Price']);
						if (array_key_exists('Duration', $MBOPass)) $updatedContent['duration'] = $MBOPass['Duration'];
						if (array_key_exists('AgreementTerms', $MBOPass)) $updatedContent['agreementterms'] = $MBOPass['AgreementTerms'];
						if (array_key_exists('NumberOfAutopays', $MBOPass)) $updatedContent['numberofautopays'] = $MBOPass['NumberOfAutopays'];
						if (array_key_exists('RecurringPaymentAmountTotal', $MBOPass)) $updatedContent['RecurringPaymentAmountTotal'] = $MBOPass['RecurringPaymentAmountTotal'];
						if (array_key_exists('FirstPaymentAmountTotal', $MBOPass)) $updatedContent['FirstPaymentAmountTotal'] = $MBOPass['FirstPaymentAmountTotal'];
						if (array_key_exists('ContractIds', $MBOPass)) $updatedContent['contractids'] = $MBOPass['ContractIds'];
						try {
							$passPage->update($updatedContent);
							array_push($response->updated, $MBOPass['Name']);
						} catch (Exception $e) {
							consoleLog('*** Error ***');
							consoleLog($e->getMessage());
							consoleLog($e->getTraceAsString());
							array_push($response->errors, $e->getMessage());
						}

					} else {
						$newContent = array();
						$newContent['mboid'] = $assignId;
						if (array_key_exists('Name', $MBOPass)) $newContent['title'] = $MBOPass['Name'];
						if (array_key_exists('ProgramID', $MBOPass)) $newContent['mboprogramid'] = $MBOPass['ProgramID'];
						if (array_key_exists('Count', $MBOPass)) $newContent['classcount'] = $MBOPass['Count'];
						if (array_key_exists('Price', $MBOPass)) $newContent['price'] = $MBOPass['Price'];
						if (array_key_exists('Duration', $MBOPass)) $newContent['duration'] = $MBOPass['Duration'];
						if (array_key_exists('AgreementTerms', $MBOPass)) $newContent['agreementterms'] = $MBOPass['AgreementTerms'];
						if (array_key_exists('NumberOfAutopays', $MBOPass)) $newContent['numberofautopays'] = $MBOPass['NumberOfAutopays'];
						if (array_key_exists('RecurringPaymentAmountTotal', $MBOPass)) $newContent['RecurringPaymentAmountTotal'] = $MBOPass['RecurringPaymentAmountTotal'];
						if (array_key_exists('FirstPaymentAmountTotal', $MBOPass)) $newContent['FirstPaymentAmountTotal'] = $MBOPass['FirstPaymentAmountTotal'];
						if (array_key_exists('ContractIds', $MBOPass)) $newContent['contractids'] = $MBOPass['ContractIds'];
						array_push($response->added, $MBOPass['Name']);
						// consoleLog(str::slug($MBOPass['Name']));
						// consoleLog($MBOPass['ID']);
						try {
							$newPassPage = kirby()->site()->pages()->create(
								'passes/' . preg_replace('/^([0-9]+-)+/', '', str::slug($MBOPass['Name'])) . '-' . $assignId,
								'pass',
								$newContent
							);
						} catch (Exception $e) {
							consoleLog('*** Error ***');
							consoleLog($e->getMessage());
							consoleLog($e->getTraceAsString());
							array_push($response->errors, $e->getMessage() . ": " . $newContent['title'] . ' - ' . $assignId);
						}
					}
				}
				return response::json(json_encode($response));
			} catch (Exception $e) {
				// consoleLog('*** Error ***');
				// consoleLog($e->getMessage());
				// consoleLog($e->getTraceAsString());
				// return response::json(json_encode($e->getMessage()));
				return response::json('wtf');
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
        $programs = kirby()->site()->pages()->find('classtypes');

				foreach ($_POST['classTypes'] as $program) {
					$programPage = $programs->children()->findBy('mboid', $program['mboID']);
					if (!$programPage) {
						$programPage = kirby()->site()->pages()->create(
							'classtypes/' . $program['slug'],
							'classtype',
							array(
								'title' => $program['title'],
								'mboid' => $program['mboID'],
							)
						);
						array_push($response->added, (string)$programPage->title());
					} else {
						$programPage->update(array(
							'title' => $program['title']
						));
					}
					foreach ($program['classes'] as $class) {
						$classPage = $programPage->children()->findBy('mboid', $class['ID']);
						if (!$classPage) {
							$newContent = array();
							if (array_key_exists('Description', $class)) $newContent['mbodescription'] = $class['Description'];
							if (array_key_exists('Name', $class)) $newContent['title'] = $class['Name'];
							if (array_key_exists('ID', $class)) $newContent['mboid'] = $class['ID'];
							consoleLog((string)$programPage->id());
							$newClassPage = kirby()->site()->pages()->create(
								(string)$programPage->id() . '/' . str::slug($class['Name']),
								'class',
								$newContent
							);
							array_push($response->added, $class['Name']);
							if (array_key_exists('ImageURL', $class)) downloadImageToPage($newClassPage, $class['ImageURL']);
						} else {
							$updatedContent = array();
							if (array_key_exists('Description', $class)) $updatedContent['mbodescription'] = $class['Description'];
							$classPage->update($updatedContent);
							array_push($response->updated, $class['Name']);
						}
					}
				}
				return response::json(json_encode($response));
			} catch (Exception $e) {
				consoleLog('*** Error ***');
				consoleLog($e->getMessage());
				consoleLog($e->getTraceAsString());
				return response::json($e->getMessage());
			}
		}),

		array(
		'method' => 'POST',
		'pattern' => 'api/sync/staff',
		'action' => function() {
			try {
				$response = new StdClass();
				$response->added = array();
				$response->updated = array();
				$choreographers = kirby()->site()->pages()->find('choreographers');
				$studiocrew = kirby()->site()->pages()->find('studiocrew');
				foreach($_POST['staff'] as $staffMember) {
					// if a staff member teaches classes, add them to choreographers
					if (count(array_intersect(['classes', 'workshops', 'popups'], $staffMember['roles'])) > 0) {
						$staffMemberPage = $choreographers->children()->findBy('mboid', $staffMember['ID']);
						if ($staffMemberPage) {
							$updatedContent = array();
							if (array_key_exists('Bio', $staffMember)) $updatedContent['mbobio'] = $staffMember['Bio'];
							if (array_key_exists('roles', $staffMember)) {
								$existingRoles = explode(', ', $staffMemberPage->roles());
								$incomingRoles = $staffMember['roles'];
								// consoleLog($existingRoles);
								// consoleLog($incomingRoles);
								$updatedContent['roles'] = implode(', ',
								array_filter(
								array_unique(array_merge($existingRoles, $incomingRoles)),
								function ($role) {
									return strlen($role) > 0;
								}
								)
								);
							}
							if (array_key_exists('classTypes', $staffMember)) {
								$existingClassTypes = explode(', ', $staffMemberPage->classtypes());
								$incomingClassTypes = $staffMember['classTypes'];
								$updatedContent['classTypes'] = implode(', ',
								array_filter(
								array_unique(array_merge($existingRoles, $incomingRoles)),
								function ($role) {
									return strlen($role) > 0;
								}
								)
								);

							}
							$staffMemberPage->update($updatedContent);
							array_push($response->updated, $staffMember['Name']);
						} else {
							// Create a new choreographer
							$firstName = $staffMember;
							$newContent = array();
							$newContent['mboid'] = $staffMember['ID'];
							$newContent['title'] = $staffMember['Name'];
							if (array_key_exists('Bio', $staffMember)) $newContent['mbobio'] = $staffMember['Bio'];
							if (array_key_exists('FirstName', $staffMember)) $newContent['firstname'] = $staffMember['FirstName'];
							if (array_key_exists('LastName', $staffMember)) $newContent['lastname'] = $staffMember['LastName'];

							if (array_key_exists('roles', $staffMember)) {
								$newContent['roles'] = implode(', ', $staffMember['roles']);
							}
							if (array_key_exists('classTypes', $staffMember)) {
								$newContent['classtypes'] = implode(', ', $staffMember['classTypes']);
							}
							consoleLog('here');
							$newStaffMemberPage = kirby()->site()->pages()->create(
							'choreographers/' . str::slug($staffMember['Name']),
							'choreographer',
							$newContent
							);
							if (array_key_exists('ImageURL', $staffMember)) downloadImageToPage($newStaffMemberPage, $staffMember['ImageURL']);
							array_push($response->added, $staffMember['Name']);

						}
					} else {
						// Update a studio crew member

						$staffMemberPage = $studiocrew->children()->findBy('mboid', $staffMember['ID']);
						if ($staffMemberPage) {
							$updatedContent = array();
							if (array_key_exists('Bio', $staffMember)) $updatedContent['mbobio'] = $staffMember['Bio'];

							if (array_key_exists('roles', $staffMember)) {
								$existingRoles = explode(', ', $staffMemberPage->roles());
								$incomingRoles = $staffMember['roles'];
								$updatedContent['roles'] = implode(', ',
								array_filter(
								array_unique(array_merge($existingRoles, $incomingRoles)),
								function ($role) {
									return strlen($role) > 0;
								}
								)
								);
							}

							$staffMemberPage->update($updatedContent);
							array_push($response->updated, $staffMember['Name']);
						} else {
							// Make a new studio crew number

							$firstName = $staffMember;
							$newContent = array();
							$newContent['mboid'] = $staffMember['ID'];
							$newContent['title'] = $staffMember['Name'];
							if (array_key_exists('Bio', $staffMember)) $newContent['mbobio'] = $staffMember['Bio'];
							if (array_key_exists('FirstName', $staffMember)) $newContent['firstname'] = $staffMember['FirstName'];
							if (array_key_exists('LastName', $staffMember)) $newContent['lastname'] = $staffMember['LastName'];
							if (array_key_exists('roles', $staffMember)) {
								$newContent['roles'] = implode(', ', $staffMember['roles']);
							}

							$newStaffMemberPage = kirby()->site()->pages()->create(
							'studiocrew/' . str::slug($staffMember['Name']),
							'crewmember',
							$newContent
							);
							if (array_key_exists('ImageURL', $staffMember)) downloadImageToPage($newStaffMemberPage, $staffMember['ImageURL']);
							array_push($response->added, $staffMember['Name']);
						}
					}
				}
				return response::json(json_encode($response));
			} catch (Exception $e) {
				consoleLog('*** Error ***');
				consoleLog($e->getMessage());
				consoleLog($e->getTraceAsString());
				return response::error($e->getMessage(), 500);
			}
		}
		)
	))


?>
