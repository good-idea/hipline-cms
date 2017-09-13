<?php
autologin();

function autologin() {
  // Prevent access on the production system
  if(url::host() !== c::get('autologin.host', 'localhost')) return false;

  // Add route if localhost environment
  kirby()->routes(array(
    array(
      'pattern' => c::get('autologin.route', 'autologin'),
      'action'  => function() {
        $user = site()->user( c::get('autologin.username') );
        if($user and $user->login( c::get('autologin.password') ) ) {
          go( c::get('autologin.redirect', 'panel') );
        } else {
          echo 'Invalid username or password';
          return false;
        }
      }
    )
  ));
}
