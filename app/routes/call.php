<?php

// الصفحة الرئيسية لمركز الاتصال
$router->get('/call', 'call/CallsController@index');

// تسجيل المكالمات
$router->post('/call/record', 'call/CallsController@record');
$router->get('/call/history/:id', 'call/CallsController@getHistory');
$router->post('/call/release-hold', 'call/CallsController@releaseHold');

// المستندات
$router->get('/call/documents/:id', 'call/DocumentsController@getDriverDocuments');
$router->post('/call/documents/update', 'call/DocumentsController@update');

// التحويلات
$router->post('/call/assign', 'call/AssignmentsController@assign');
$router->post('/call/mark-seen', 'call/AssignmentsController@markAsSeen');

// تحديث بيانات السائق
$router->post('/call/updateDriverInfo', 'call/CallsController@updateDriverInfo'); 