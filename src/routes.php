<?php

// Landing Routes
$app->get('/', 'Modules\Landing:index');

// NIAS
$app->any('/login',   'Modules\Landing:login');
$app->get('/log_out', 'Modules\Landing:log_out');
$app->get('/logout',  'Modules\Landing:get_logout');
$app->post('/logout', 'Modules\Landing:post_logout');

// Panel routes
$app->get('/user/{lang}/{isvu}', 'Modules\User:user')->add('Modules\Landing');
$app->get('/user/executor/{id}', 'Modules\User:executor')->add('Modules\Landing');
$app->get('/executors/{lang}',   'Modules\User:executors')->add('Modules\Landing');

// Download routes
$app->get('/download/xml/{isvu}', 'Modules\Download:getXML')->add('Modules\Landing');
$app->get('/download/pdf/{isvu}', 'Modules\Download:getPDF')->add('Modules\Landing');
$app->get('/transfer/{isvu}',     'Modules\Download:transfer')->add('Modules\Landing');

$app->get('/provjera',            'Modules\Check:check');
$app->get('/check/{filename}',    'Modules\Check:getHTML');

$app->get('/check', 'Modules\Landing:check')->add('Modules\Landing');

// Catching Landing:index
$app->any('/*', 'Modules\Landing:index');