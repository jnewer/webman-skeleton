<?php

namespace app\model;

use support\Model;

/**
 * sys_user 用户信息表
 * @property integer $id [bigint] (主键)
 * @property string $username [varchar(64)] 用户名
 * @property string $nickname [varchar(64)] 昵称
 * @property integer $gender [tinyint(1)] 性别((1-男 2-女 0-保密)
 * @property string $password [varchar(100)] 密码
 * @property integer $dept_id [int] 部门ID
 * @property string $avatar [varchar(255)] 用户头像
 * @property string $mobile [varchar(20)] 联系方式
 * @property integer $status [tinyint(1)] 状态(1-正常 0-禁用)
 * @property string $email [varchar(128)] 用户邮箱
 * @property string $created_at [datetime] 创建时间
 * @property integer $created_by [bigint] 创建人ID
 * @property string $updated_at [datetime] 更新时间
 * @property integer $updated_by [bigint] 修改人ID
 * @property string $deleted_at [datetime] 删除时间
 */
class User extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'mysql';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;


}
