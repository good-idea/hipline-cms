<?php


page::$methods['getPublicContent'] = function($page, $withChildren = false, $onlyVisibleChildren = true) {

	$getImageInfo = function($image) {
		return buildImage($image);
	};

	$content = $page->content()->toArray();
	$content['slug'] = $page->uid();
	$content['id'] = $page->id();
	$content['sort'] = $page->sort();
	$content['isVisible'] = $page->isVisible();

	$seo = [];
	if (isset($content['seo_description'])) $seo['description'] = $content['seo_description'];
	if (isset($content['seo_keywords'])) $seo['keywords'] = $content['seo_keywords'];
	if ($page->seoImage()) {
		$seo['image'] = buildImage($page->seoImage());
	} else if ($page->cover()) {
		$seo['image'] = buildImage($page->cover());
	}
	if (isset($content['seo_title'])) {
		$seo['title'] = $content['seo_title'];
	} else if (isset($content['title'])) {
		$seo['title'] = $content['title'];
	}

	$seo['title'] = (string)$page->title();
	$content['seo'] = $seo;

	$hasImages = $page->hasImages();
	if ($hasImages) {
		$images = $page->images()->sortBy('Sort', 'asc');
		$imagesArr = [];
		foreach ($images as $image) {
			$image = buildImage($image);
			array_push($imagesArr, $image);
		};
		$content['images'] = $imagesArr;
	} else {
		$content['images'] = [];
	};

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
	unset($content['seo_title']);
	unset($content['seo_image']);
	unset($content['seo_description']);
	unset($content['seo_keywords']);

	return $content;
};


page::$methods['seoImage'] = function($page) {
	$image_str = (string)$page->seo_image();
	if (null !== $page->files()->find($image_str) && $page->files()->find($image_str)->width() > 0) {
		return $page->files()->find($image_str);
	} else {
		return false;
	}
};

page::$methods['cover'] = function($page) {
	$image_str = (string)$page->cover_image();
	if (null !== $page->files()->find($image_str) && $page->files()->find($image_str)->width() > 0) {
		return $page->files()->find($image_str);
	} else {
		return false;
	}
};

page::$methods['coverVideo'] = function($page) {
	$video_str = (string)$page->cover_video();
	if (null !== $page->files()->find($video_str)) {
		return $page->files()->find($video_str);
	} else {
		return false;
	}
};


?>
