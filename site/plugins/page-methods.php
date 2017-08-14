<?php

page::$methods['getPublicContent'] = function($page, $withChildren = false) {
	$content = $page->content()->toArray();
	$content['slug'] = $page->uid();
	$content['id'] = $page->id();
	if ($page->cover()) $content['cover'] = buildImage($page->cover());
	// if ($page->getAllImageURLs()) $content['images'] = $page->getAllImageURLs();
	$content['type'] = $page->intendedTemplate();

	foreach ($content as $key => $value) {
		if (smellsLikeYaml($value)) {
			$content[$key] = yaml($value);
		}
	}

	if ($withChildren) {
		$content['children'] = [];
		$children = $page->children()->visible();
		if ($children->count() > 0) {
			foreach ($children as $child) {
				$childContent = $child->getPublicContent($withChildren);
				array_push($content['children'], $childContent);
			}
		}
		if (count($content['children']) === 0) unset($content['children']);
	}
	if (gettype($withChildren) === 'integer') $withChildren -= 1;

	return $content;
};

page::$methods['cover'] = function($page) {
	// if (!$page->cover_image()->exists()) return false;
	$image_str = (string)$page->cover_image();
	if (null !== $page->files()->find($image_str) && $page->files()->find($image_str)->width() > 0) {
		return $page->files()->find($image_str);
	} else if ($page->images()->count() > 0) {
		return $page->images()->first();
	} else {
		return false;
		return site()->pages()->find('error')->images()->find('default_main_image.jpg');
	}
};


// page::$methods['getAllImageURLs'] = function($page) {
//
// 	$images = [];
// 	if ($page->images()->count() === 0) return false;
// 	foreach ($page->images() as $imageSource) {
// 		array_push($images, buildImage($imageSource));
// 	}
// 	return $images;
// };


?>
