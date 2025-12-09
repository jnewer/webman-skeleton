<?php

namespace app\admin\validate;

use app\base\BaseValidate;



class UserValidate extends BaseValidate
{
    protected $rule = [
        'username' => 'require|unique:user,username|min:4|max:20',
        'nickname' => 'require|min:8|max:20',
        'mobile'   => 'mobile',
        'gender'   => 'integer|in:0,1,2',
        'avatar'   => 'string',
        'emaril'    => 'email',
        'status'   => 'integer|in:0,1',
        'dept_id'  => 'integer',
        'role_ids' => 'require|array',
        'open_id' => 'string',
        'old_password' => 'require|min:6|max:16|different:new_password',
        'new_password' => 'require|min:6|max:16',
        'new_password_confirm' => 'require|confirm',

    ];

    protected $message = [
        'username.require' => '用户名不能为空',
        'nickname.require' => '昵称不能为空',
        'role_ids.require' => '角色不能为空',
    ];

    protected $scene = [
        'store'  =>  ['username', 'nickname', 'mobile', 'gender', 'avatar', 'emaril', 'status', 'dept_id', 'role_ids'],
        'update'  =>  ['username', 'nickname', 'mobile', 'gender', 'avatar', 'emaril', 'status', 'dept_id', 'role_ids'],
        'resetPassword'  =>  ['old_password', 'new_password', 'new_password_confirm'],
    ];
}
