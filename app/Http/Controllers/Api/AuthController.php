<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostLoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\HelperAPI;
use Exception;

class AuthController extends Controller
{
    /**
     * @var User
     */
    private $userModel;

    /**
     * @param User $userModel
     */
    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * Check Login And Create Token Key
     *
     * @param PostLoginRequest $request
     * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function login(PostLoginRequest $request): \Illuminate\Http\Response|array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $user = $this->userModel->authenticate($request);
            $token = $user->createToken('main')->plainTextToken;
            $responseData = [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => HelperAPI::formatDateTime($user->created_at)
                ],
                'token' => $token
            ];
        } catch (Exception $e) {
            return HelperAPI::responseError($e->getMessage());
        }

        return HelperAPI::responseSuccess($responseData, 'Login success!');
    }

    /**
     * @return array
     */
    public function logout(): array
    {
        try {
            $user = Auth::user();
            $user->currentAccessToken()->delete();
        } catch (Exception $e) {
            return HelperAPI::responseError($e->getMessage());
        }

        return HelperAPI::responseSuccess([],'Logout success!');
    }

    /**
     * Get User Data
     *
     * @param Request $request
     * @return array
     * @throws Exception
     */
    public function getUser(Request $request): array
    {
        $user = $request->user();
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => HelperAPI::formatDateTime($user->created_at)
        ];

        return HelperAPI::responseSuccess($data,'Get User data!');
    }
}
