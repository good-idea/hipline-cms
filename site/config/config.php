<?php

/*

---------------------------------------
License Setup
---------------------------------------

Please add your license key, which you've received
via email after purchasing Kirby on http://getkirby.com/buy

It is not permitted to run a public website without a
valid license key. Please read the End User License Agreement
for more information: http://getkirby.com/license

*/

c::set('license', 'put your license key here');

/*

---------------------------------------
Kirby Configuration
---------------------------------------

By default you don't have to configure anything to
make Kirby work. For more fine-grained configuration
of the system, please check out http://getkirby.com/docs/advanced/options

*/

c::set('panel.install', true);
c::set('panel.stylesheet', '/assets/css/panel.css');

c::set('debug', true);
c::set('cache', false);

require_once(dirname(__FILE__) . DS . '..' . DS . 'routes.php');

function consoleLog($input) {
	file_put_contents("php://stdout", var_export($input, true) . "\n");
}

/* AutoLogin plugin */

c::set('autologin.username', 'joseph');
c::set('autologin.password', 'password');
c::set('autologin.host', '127.0.0.1');
c::set('autologin.route', 'autologin');
