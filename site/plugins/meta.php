<?php


function meta($page) {
	$site = kirby()->site();
	$homepage = $site->pages()->find('home');

	$homepageImage = $homepage->seo_image();
	$defaultImage = ($homepageImage) ? $homepage->images()->find($homepageImage) : '';

	$homepageDescription = $homepage->seo_description();
	$defaultDescription = ($homepageDescription) ? (string)$homepageDescription : '';

	$page = ($page) ? $page : $homepage;
	$title = ($page->isHomepage()) ? $site->title() : $site->title() . " | " . $page->title();

	$description = (strlen((string)$page->seo_description()) > 0) ? (string)$page->seo_description() : $defaultDescription;
	$description = strip_tags(kirbytext($description));

	$pageImage = $page->images()->find($page->seo_image());
	$image = ($pageImage) ? $pageImage : $defaultImage;
	// $image = thumb($image, array('width', 1200), false);
	$meta = new StdClass();
	
	$meta->title = (string)$title;
	$meta->description = (string)$description;
	$meta->image = buildImage($image);

	return($meta);
}

?>
