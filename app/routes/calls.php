<?php

use App\Core\Router;

$router = new Router();

$router->get('/calls', 'calls/CallsController@index');
$router->get('/calls/getNextDriver', 'calls/CallsController@getNextDriver');

// تسجيل المكالمات
$router->post('/calls/record', 'calls/CallsController@record');
$router->get('/calls/history/:id', 'calls/CallsController@getHistory');
$router->post('/calls/release-hold', 'calls/CallsController@releaseHold');

// المستندات
$router->get('/calls/documents/:id', 'calls/DocumentsController@getDriverDocuments');
$router->post('/calls/documents/update', 'calls/CallsController@updateDocuments');

// التحويلات
$router->post('/calls/assign', 'calls/AssignmentsController@assign');
$router->post('/calls/mark-seen', 'calls/AssignmentsController@markAsSeen');

// تحديث بيانات السائق
$router->post('/calls/updateDriverInfo', 'calls/CallsController@updateDriverInfo');

// تخطي السائق
$router->get('/calls/skip/:id', 'calls/CallsController@skip');

// تحديث سمة السائق (رحلات > 10)
$router->post('/calls/updateDriverAttribute', 'calls/CallsController@updateDriverAttribute'); 