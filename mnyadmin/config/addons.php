<?php

return array (
  'autoload' => false,
  'hooks' => 
  array (
    'show_map' => 
    array (
      0 => '\\addons\\address\\Address',
    ),
    'sms_send' => 
    array (
      0 => '\\addons\\easysms\\Easysms',
    ),
    'sms_notice' => 
    array (
      0 => '\\addons\\easysms\\Easysms',
    ),
    'sms_check' => 
    array (
      0 => '\\addons\\easysms\\Easysms',
    ),
    'admin_login_style' => 
    array (
      0 => '\\addons\\loginbg\\Loginbg',
    ),
    'upload_after' => 
    array (
      0 => '\\addons\\qiniu\\Qiniu',
    ),
    'upload_delete' => 
    array (
      0 => '\\addons\\qiniu\\Qiniu',
    ),
    'app_init' => 
    array (
      0 => '\\addons\\queue\\Queue',
    ),
    'ems_send' => 
    array (
      0 => '\\addons\\saiyouems\\Saiyouems',
    ),
    'ems_notice' => 
    array (
      0 => '\\addons\\saiyouems\\Saiyouems',
    ),
    'ems_check' => 
    array (
      0 => '\\addons\\saiyouems\\Saiyouems',
    ),
    'content_delete_end' => 
    array (
      0 => '\\app\\member\\behavior\\Hooks',
    ),
    'content_edit_end' => 
    array (
      0 => '\\app\\member\\behavior\\Hooks',
    ),
    'user_sidenav_after' => 
    array (
      0 => '\\app\\pay\\behavior\\Hooks',
    ),
  ),
  'route' => 
  array (
  ),
);