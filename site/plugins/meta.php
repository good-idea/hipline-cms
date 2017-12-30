<?php


function meta($page) {
	$site = kirby()->site();
	$homepage = $site->pages()->find('home');

	$homepageImage = $homepage->og_image();
	$defaultImage = ($homepageImage) ? $homepage->images()->find($homepageImage) : '';

	$homepageDescription = $homepage->og_description();
	$defaultDescription = ($homepageDescription) ? (string)$homepageDescription : '';

	$page = ($page) ? $page : $homepage;
	$title = ($page->isHomepage()) ? $site->title() : $site->title() . " | " . $page->title();

	$description = (strlen((string)$page->og_description()) > 0) ? (string)$page->og_description() : $defaultDescription;
	$description = strip_tags(kirbytext($description));

	$pageImage = $page->images()->find($page->og_image());
	$image = ($pageImage) ? $pageImage : $defaultImage;
	$image = thumb($image, array('width', 1200), false);
	$meta = new StdClass();
	
	$meta->title = (string)$title;
	$meta->description = (string)$description;
	$meta->image = (string)$image;

	return($meta);
}

?>
