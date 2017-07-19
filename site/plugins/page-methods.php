<?php

function buildImage($imageSource) {
	$imageSizes = [
		'2400' => 2400,
		'1600' => 1600,
		'1200' => 1200,
		'800' => 800,
		'400' => 400
	];
	$image = new StdClass();
	$image->srcset = [];
	$image->url = (string)$imageSource->url();
	$image->meta = $imageSource->meta()->toArray();
	array_push($image->srcset, array(
		'label' => 'original',
		'width' => $imageSource->width(),
		'height' => $imageSource->height(),
		'url' => $imageSource->url(),
		'isOriginal' => true,
	));
	foreach ($imageSizes as $label => $width) {
		if ($imageSource->width() > $width) {
			$thumbnail = [
				'label' => $label,
				'width' => $width,
				'url' => thumb($imageSource, array('width' => $width), false)];
			array_push($image->srcset, $thumbnail);
		}
	}
	$image->isCover = $imageSource === $imageSource->page()->cover();
	return $image;
}


function smellsLikeYaml($input) {
	if (gettype($input) !== 'string') return false;
	if (strlen($input) === 0) return false;
	$yamlArray = '/^-\s?\\n\s*[a-z]*:/';
	preg_match($yamlArray, $input, $output);
	return (count($output) > 0);
};

page::$methods['getPublicContent'] = function($page, $withChildren = false) {
	$content = $page->content()->toArray();
	$content['uid'] = $page->uid();
	$content['id'] = $page->id();
	if ($page->cover()) $content['cover'] = buildImage($page->cover());
	// if ($page->getAllImageURLs()) $content['images'] = $page->getAllImageURLs();
	$content['template'] = $page->intendedTemplate();

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
	}
	if (gettype($withChildren) === 'integer') $withChildren -= 1;

	return $content;
};

page::$methods['cover'] = function($page) {
	if (!$page->cover_image()->exists()) return false;
	$image_str = (string)$page->cover_image();
	if (null !== $page->files()->find($image_str) && $page->files()->find($image_str)->width() > 0) {
		return $page->files()->find($image_str);
	} else if ($page->images()->count() > 0) {
		return $page->images()->first();
	} else {
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
