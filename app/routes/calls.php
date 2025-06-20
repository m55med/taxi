<?php

// الصفحة الرئيسية لمركز الاتصال
$router->get('/call', 'calls/CallsController@index');
$router->get('/call/getNextDriver', 'calls/CallsController@getNextDriver');

// تسجيل المكالمات
$router->post('/call/record', 'calls/CallsController@record');
$router->get('/call/history/:id', 'calls/CallsController@getHistory');
$router->post('/call/release-hold', 'calls/CallsController@releaseHold');

// المستندات
$router->get('/call/documents/:id', 'calls/DocumentsController@getDriverDocuments');
$router->post('/call/documents/update', 'calls/DocumentsController@updateDocuments');

// التحويلات
$router->post('/call/assign', 'calls/AssignmentsController@assign');
$router->post('/call/mark-seen', 'calls/AssignmentsController@markAsSeen');

// تحديث بيانات السائق
$router->post('/call/updateDriverInfo', 'calls/CallsController@updateDriverInfo');

// تخطي السائق
$router->get('/call/skip/:id', 'calls/CallsController@skip'); 