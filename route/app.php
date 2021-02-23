<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

// 登录注册
Route::rule('/login', 'Login/login');
Route::rule('/register', 'Login/register');
Route::rule('/index', 'Login/index');

// 后台文章管理
Route::rule('/upload', 'ArtAdmin/uploader');
Route::rule('/artList', 'ArtAdmin/artList');
Route::rule('/addArt', 'ArtAdmin/addArt');
Route::rule('/delArt', 'ArtAdmin/delArt');
Route::rule('/editArt', 'ArtAdmin/editArt');

// 前端页面展示数据
Route::rule('/visits', 'ArtWeb/visits');
Route::rule('/addRead', 'ArtWeb/addRead');
Route::rule('/artWebList', 'ArtWeb/artList');
Route::rule('/tagArtList', 'ArtWeb/tagArtList');
Route::rule('/rankList', 'ArtWeb/rankList');
Route::rule('/artContent', 'ArtWeb/artContent');

Route::rule('/ceshi', 'ArtWeb/ceshi');

Route::rule('/ahmset', 'CreateCache/ahmset');
Route::rule('/addIdSet', 'CreateCache/addIdSet');
Route::rule('/addRank', 'CreateCache/addRank');
Route::rule('/ipstr', 'CreateCache/ipstr');