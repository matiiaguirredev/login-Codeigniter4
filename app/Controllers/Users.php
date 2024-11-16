<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsersModel;

class Users extends BaseController {

    protected $helpers = ['form'];

    public function index() {
        return view('register');
    }

    public function create() {

        $rules = [
            'user' => 'required|max_length[30]|is_unique[users.user]',
            'password' => 'required|max_length[50]|min_length[5]',
            'repassword' => 'matches[password]',
            'name' => 'required|max_length[100]',
            'email' => 'required|max_length[80]|valid_email|is_unique[users.email]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->listErrors());
        }

        $userModel = new UsersModel();
        $post = $this->request->getPost(['user', 'password', 'name', 'email']);

        $token = bin2hex(random_bytes(20));

        $userModel->insert([
            'user' => $post['user'],
            'password' => password_hash($post['password'], PASSWORD_DEFAULT),
            'name' => $post['name'],
            'email' => $post['email'],
            'active' => 0,
            'activacion_token' => $token,
        ]);

        $email = \Config\Services::email();
        $email->setTo($post['email']);
        $email->setSubject('Activa tu cuenta');

        $url = base_url('activate-user/' . $token);
        $body = '<p>Hola' . $post['name'] . '</p>';
        $body .= "<p>Para continuar con el proceso de registro, has click en la siguiente linea <a href='$url'</a> Activar cuenta </p>";
        $body .= '<p> Gracias! </p>';

        $email->setMessage($body);
        $email->send();

        $title = 'Regristro exitoso';
        $message = 'Revisa tu correco electronico para activar tu cuenta';

        return $this->showMessage($title, $message);
    }

    public function activateUser($token) {
        $userModel = new UsersModel();

        $user = $userModel->where([
            'activacion_token' => $token,
            'active' => 0,
        ])->first();

        if ($user) {
            $userModel->update(
                $user['id'],
                [
                    'active' => 1,
                    'activacion_token' => null
                ]
            );

            return $this->showMessage('Cuenta activada', 'Tu cuenta ha sido activada');
        }

        return $this->showMessage('Ocurrio un error.', 'Por favor, intenta nuevamente mas tarde.');
    }

    public function linkREquestForm() {


        return view('link_request');
    }

    public function sentResetLinkEmail() {

        $rules = [
            'email' => 'required|max_length[80]|valid_email',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->listErrors());
        }

        $userModel = new UsersModel();
        $post = $this->request->getPost(['email']);
        $user = $userModel->where(['email' => $post['email'], 'active' => 1])->first();

        if ($user) {
            $token = bin2hex(random_bytes(20));
            $expiresAt = new \DateTime();

            $expiresAt->modify('+1 hour');

            $userModel->update(
                $user['id'],
                [
                    'reset_token' => $token, // nuevo token
                    'reset_token_expires_at' => $expiresAt->format('Y-m-d H:i:s') // tiempo de vida de la funcion de la contrasena
                ]
            ); // lo que entra dentro de los corchetes es la infomracion que se va actualizar

            $email = \Config\Services::email();
            $email->setTo($post['email']);
            $email->setSubject('Recuperar contraseña.');

            $url = base_url('password-reset/' . $token);
            $body = '<p>Estimad@ ' . $user['name'] . '</p>';
            $body .= "<p>Se ha solicitado un reinicio de contraseña. <br>Para restaurar la contraseña visita la siguien direccion: <a href='$url'> $url </a></p>";

            $email->setMessage($body);
            $email->send();
        }

        $title = 'Correo de recuperacion enviado.';
        $message = 'Se ha enviado un correo electrnico con instrucciones para restablecer tu contraseña';

        return $this->showMessage($title, $message);
    }

    public function resetForm($token) {

        $userModel = new UsersModel();
        $user = $userModel->where(['reset_token' => $token])->first();

        if ($user) {
            $currentDateTime = new \DateTime;
            $currrentDateTimeStr = $currentDateTime->format('Y-m-d H:i:s');

            if ($currrentDateTimeStr <= $user['reset_token_expires_at']) {
                return view('reset_password', ['token' => $token]);
            } else {
                return $this->showMessage('Token invalido', 'El token de recuperacion de contraseña ha expirado, solicita uno nuevo para restablecer la contraseña.');
            }
        }

        return $this->showMessage('Ocurrio un error.', 'Por favor, intenta mas tarde.');
    }

    public function resetPassword() {
        $rules = [
            'password' => 'required|max_length[50]|min_length[5]',
            'repassword' => 'matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->listErrors());
        }

        $userModel = new UsersModel();
        $post = $this->request->getPost(['token', 'password']);
        $user = $userModel->where(['reset_token' => $post['token'], 'active' => 1])->first();

        if ($user) {
            $userModel->update(
                $user['id'],
                [
                    'password' => password_hash($post['password'], PASSWORD_DEFAULT),
                    'reset_token' => null,
                    'reset_token_expires_at' => null,
                ]
            );

            return $this->showMessage('Contraseña restablecida', 'Tu contraseña ha sido restablecida. Ahora puedes ingresar con tu nueva contraseña.');
        }

        return $this->showMessage('Ocurrio un error.', 'Por favor, intenta mas tarde.');
    }

    private function showMessage($title, $message) {
        $data = [
            'title' => $title,
            'message' => $message,
        ];

        return view('message', $data);
    }
}
