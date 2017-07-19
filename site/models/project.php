<?php

class ProjectPage extends Page {

	public function getPublicContent($withChildren = false) {
		$content = parent::getPublicContent($withChildren);
		$content['year'] = (string)$this->parent()->title();
		$content['slug'] = (string)$this->slug();
		if (strlen($content['categories']) === 0) {
			$content['categories'] = ['Uncategorized'];
		} else {
			$content['categories'] = explode(',', $content['categories']);
		}
		return $content;
	}

}

?>
