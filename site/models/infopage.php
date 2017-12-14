<?php

class InfopagePage extends Page {

	public function getPublicContent($withChildren = false) {
		$content = parent::getPublicContent($withChildren);
		if (!is_array($content['buttons'])) $content['buttons'] = [];
		if (!is_array($content['blocks'])) $content['blocks'] = [];

		$content['blocks'] = array_map(function($block) {
			if (array_key_exists('cover_image', $block)) {
				$image_str = (string)$block['cover_image'];
				if (null !== $this->files()->find($image_str) && $this->files()->find($image_str)->width() > 0) {
					$block['cover'] = buildImage($this->files()->find($image_str));
					$block['cover']->isCover = true;
				}
			}
			return $block;
		}, $content['blocks']);

		return $content;
	}

}

?>
