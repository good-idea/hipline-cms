<?php

class ChoreographerPage extends Page {

	public function getYear() {
		if ($this->year()->exists() && strlen($this->year()) > 0) return $this->year();
		return $this->parent()->title();
	}

	public function getPublicContent($withChildren = false) {
		$content = parent::getPublicContent($withChildren);
		if (!array_key_exists('bio', $content) || strlen($content['bio']) === 0) {
			if (array_key_exists('mbobio', $content)) {
				$content['bio'] = $content['mbobio'];
			} else {
				$content['bio'] = '';
			}
		}
		unset($content['mbobio']);
		if (!array_key_exists('expectations', $content)) $content['expectations'] = '';
		if (!array_key_exists('quote', $content)) {
			$emptyQuote = new StdClass();
			$emptyQuote->citation = '';
			$emptyQuote->body = '';
			$content['quote'] = $emptyQuote;
		} else if (is_array($content['quote'])){
			$content['quote'] = $content['quote'][0];
		}

		return $content;
	}

}

?>
