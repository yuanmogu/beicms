<?php

use think\facade\Route;

Route::pattern([
    'folder' => '\w+',
    'id'   => '\d+',
]);


//专题
Route::get('special/index', 'special/index')->name('special');
Route::get('s/<folder>', 'special/show')->name('special');

//单页
Route::get('p/<folder>', 'single/index')->name('single');

//栏目页
Route::rule('c/<folder>', 'category/index')->name('category');

//文档
Route::get('a/<folder>/<id>', 'archives/index')->name('archives');


//点赞AJAX
Route::get('archives/likes', 'archives/likes');

//留言
Route::get('message/add', 'message/add');