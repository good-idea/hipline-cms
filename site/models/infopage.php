<?php

class InfopagePage extends Page {

	public function getPublicContent($withChildren = false) {
		$content = parent::getPublicContent($withChildren);
		if (!is_array($content['buttons'])) $content['buttons'] = [];
		if (!is_array($content['blocks'])) $content['blocks'] = [];
		return $content;
	}

}

?>
