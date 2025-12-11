<?php

namespace app\admin\service\system;

use app\base\BaseService;
use app\admin\model\system\User;
use Illuminate\Pagination\Paginator;
use think\exception\ValidateException;

class UserService extends BaseService
{

    public function paginate(array $params = []): array
    {
        $query = User::query();

        $query->when(!empty($params['username']), function ($query) use ($params) {
            $query->where('username', 'like', '%' . $params['username'] . '%');
        });

        $query->when(!empty($params['nickname']), function ($query) use ($params) {
            $query->where('nickname', 'like', '%' . $params['nickname'] . '%');
        });

        $query->when(!empty($params['mobile']), function ($query) use ($params) {
            $query->where('mobile', 'like', '%' . $params['mobile'] . '%');
        });

        $query->when(!empty($params['email']), function ($query) use ($params) {
            $query->where('email', 'like', '%' . $params['email'] . '%');
        });

        $query->when(isset($params['status']) && $params['status'] !== '', function ($query) use ($params) {
            $query->where('status', $params['status']);
        });

        return $query->customPaginate();
    }

    public function show(mixed $id): User
    {
        $user = User::findOrFail($id);

        return $user;
    }

    public function store(array $attribuetes = []): User
    {
        $user = User::create($attribuetes);

        $user->roles()->sync($attribuetes['role_ids']);

        return $user;
    }

    public function update(mixed $id, array $attribuetes = []): User
    {
        $user = User::findOrFail($id);

        $user->update($attribuetes);

        $user->roles()->sync($attribuetes['role_ids']);

        return $user;
    }

    public function delete(mixed $id): bool
    {
        $user = User::findOrFail($id);

        $user->roles()->detach();

        return $user->delete();
    }

    public function resetPassword($id, array $attribuetes = []): User
    {
        $user = User::findOrFail($id);
        if (password_verify($attribuetes['old_password'], $user->password) === false) {
            throw new ValidateException('原密码不正确');
        }

        $user->password = password_hash($attribuetes['new_password'], PASSWORD_BCRYPT);
        $user->save();

        return $user;
    }
}
