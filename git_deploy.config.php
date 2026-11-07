<?php
// Settings for git_deploy.php. Safe to commit: it holds no secret and prints nothing.
return [
    'branch' => 'main',
    // vendor/ is not in Git and the server has no terminal or Composer, so the deploy only syncs files.
    // When composer.lock changes, upload the new vendor/ folder with File Manager.
    'steps' => [],
];
