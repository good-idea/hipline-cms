<?php

class ClassPage extends Page {

	public function getPublicContent($withChildren = false) {
		$content = parent::getPublicContent($withChildren);
		if (!array_key_exists('description', $content) || strlen($content['description']) === 0) {
			if (array_key_exists('mbodescription', $content)) {
				$content['description'] = $content['mbodescription'];
			} else {
				$content['description'] = '';
			}
		}
		$content['program'] = $this->parent()->getPublicContent();
		unset($content['mbodescription']);
		return $content;
	}

}

?>
