<?php

namespace app\admin\controller\system;

use DI\Attribute\Inject;
use Tinywan\Jwt\JwtToken;
use app\base\ApiController;
use app\admin\service\system\UserService;
use app\admin\validate\UserValidate;
use support\Request;

class UserController extends ApiController
{
    #[Inject]
    private UserService $userService;

    private string $validateClass = UserValidate::class;
    public function index(Request $request)
    {
        return $this->success($this->userService->paginate($request->all()));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validated($this->validateClass, 'store');

        return $this->success($this->userService->store($validatedData));
    }

    public function show($id)
    {
        return $this->success($this->userService->show($id));
    }


    public function update($id, Request $request)
    {
        $validatedData = $request->validated($this->validateClass, 'update');

        return $this->success($this->userService->update($id, $validatedData));
    }

    public function destroy($id)
    {
        return $this->success($this->userService->delete($id));
    }

    public function me()
    {
        return $this->success(JwtToken::getUser());
    }

    public function resetPassword(Request $request)
    {
        $validatedData = $request->validated($this->validateClass, 'resetPassword');

        return $this->success($this->userService->resetPassword(JwtToken::getCurrentId(), $validatedData));
    }
}
