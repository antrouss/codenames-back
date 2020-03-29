<?php

/**
 * User controller used to control actions about the user
 *
 * PHP version 7.4
 *
 * @category   Controller
 * @author     Antony Roussos <antrouss4@gmail.com>
 * @version    0.0.1
 */

namespace App\Controller;

use App\Service\BaseService;
use App\Service\UserService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends BaseController
{
    /**
     * Endpoint that is used when login is successful
     * 
     * @return Response
     * 
     * @Route("/login", name="login", methods={"POST"})
     */
    public function login(): Response
    {
        $user = $this->getUser();
        $result = [
            'code' => BaseService::SUCCESS,
            'data' => $user,
        ];
        return $this->respond($result);
    }

    /**
     * Endpoint to register a new user
     *
     * @param Request $request
     * @param UserService $user_service
     * 
     * @return Response
     * 
     * @Route("/register", name="register", methods={"POST"})
     */
    public function register(Request $request, UserService $user_service): Response
    {
        $user_data = json_decode($request->getContent(), true);
        $data = (object) $user_data;
        $result = $this->validate($data, $this, __FUNCTION__);
        /**
         * If validation using json schema has errors, then respond with the
         * errors, else continue with user registration.
         */
        if ($result['code'] !== BaseService::SUCCESS) {
            return $this->respond($result);
        }
        $result = $user_service->register($data->username, $data->password);
        return $this->respond($result);
    }
}
