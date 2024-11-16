<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsersModel;

class Login extends BaseController {
    public function index() {
        return view('login');
    }

    public function auth() {

        $rules = [
            "user" => "required",
            "password" => "required",
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->listErrors());
        }

        $userModel = new UsersModel();
        $post = $this->request->getPost(['user', 'password']);

        $user = $userModel->validateUser($post['user'], $post['password']);

        if ($user !== null) {
            $this->setSesion($user);
            return redirect()->to(base_url('home'));
        }

        return redirect()->back()->withInput()->with('errors', 'El usuario y/o contraseña  son incorrectos.');

    }
    private function setSesion($userData){

        $data = [
            'logged_in' => true,
            'userid' => $userData['id'],
            'username' => $userData['name'],
        ];

        $this->session->set($data);
    }
    
    public function logout (){
        if ($this->session->get('logged_in')) {
            $this->session->destroy();
        }

        return redirect()->to(base_url());
    }


}
