<?php

page::$methods['getPublicContent'] = function($page, $withChildren = false, $onlyVisibleChildren = true) {
	$content = $page->content()->toArray();
	$content['slug'] = $page->uid();
	$content['id'] = $page->id();
	$content['isVisible'] = $page->isVisible();
	if ($page->cover()) {
		$content['cover'] = buildImage($page->cover());
	}
	if ($page->coverVideo()) {
		$content['coverVideo'] = $page->coverVideo()->url();
	}
	// if ($page->getAllImageURLs()) $content['images'] = $page->getAllImageURLs();
	$content['type'] = $page->intendedTemplate();

	foreach ($content as $key => $value) {
		if (smellsLikeYaml($value)) {
			$content[$key] = yaml($value);
		}
		if (smellsLikeNum($value)) {
			$content[$key] = floatval($value);
		}
		if (smellsLikeBool($value)) {
			$content[$key] = ($value === 'true') ? true : false;
		}
	}

	if ($withChildren) {
		$content['children'] = [];
		$children = $page->children();
		if ($onlyVisibleChildren) $children = $children->visible();
		if ($children->count() > 0) {
			foreach ($children as $child) {
				$childContent = $child->getPublicContent($withChildren, $onlyVisibleChildren);
				array_push($content['children'], $childContent);
			}
		}
		if (count($content['children']) === 0) unset($content['children']);
	}
	if (gettype($withChildren) === 'integer') $withChildren -= 1;

	unset($content['cover_image']);
	unset($content['cover_video']);


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
	}
};

page::$methods['coverVideo'] = function($page) {
	// if (!$page->cover_image()->exists()) return false;
	$video_str = (string)$page->cover_video();
	if (null !== $page->files()->find($video_str)) {
		return $page->files()->find($video_str);
	} else {
		return false;
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
