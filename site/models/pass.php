<?php

class PassPage extends Page {

	public function getPublicContent($withChildren = false) {
		$content = parent::getPublicContent($withChildren);
		$icon_str = (string)$this->icon_image();
		if (null !== $this->files()->find($icon_str) && $this->files()->find($icon_str)->width() > 0) {
			$content['icon'] = buildImage($this->files()->find($icon_str));
		} else {
			$parent = $this->parent();
			$icon_str = (string)$parent->default_icon();
			$content['icon'] = buildImage($parent->files()->find($icon_str));
		}
		return $content;
	}

}

?>
