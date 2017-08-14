<?php

v::$validators['relativeOrURL'] = function ($value) {
	return v::match($value, '/(^(https?|ftp):\/\/[^\s/$.?#].[^\s]*$)|(^\/[^\s$.?#]+)/');
}

?>
