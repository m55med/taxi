<?php
// Custom routes for HelpController
$router->get("admin/help-videos", "Admin/HelpController@index")->middleware(['admin', 'developer']);
$router->post("admin/help-videos/save", "Admin/HelpController@save")->middleware(['admin', 'developer']);
