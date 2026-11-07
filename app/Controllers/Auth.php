<?php

namespace App\Controllers;

use App\Models\CharacterModel;
use App\Models\UserModel;

class Auth extends BaseController
{
    public function login()
    {
        if ($this->request->is('post')) {
            $throttler = service('throttler');
            if (! $throttler->check(md5('login:' . $this->request->getIPAddress()), 10, 600)) {
                return redirect()->back()->with('error', 'Слишком много попыток входа. Подожди 10 минут.')->withInput();
            }

            $email    = trim((string) $this->request->getPost('email'));
            $password = (string) $this->request->getPost('password');

            $user = (new UserModel())->where('email', $email)->first();

            if (! $user || ! password_verify($password, $user->password_hash)) {
                return redirect()->back()->with('error', 'Неверный email или пароль.')->withInput();
            }

            $this->session->set('user_id', $user->id);

            $character = (new CharacterModel())->where('user_id', $user->id)->first();
            if ($character) {
                $this->session->set('character_id', $character->id);
            }

            return redirect()->to('/');
        }

        return view('auth/login');
    }

    public function register()
    {
        if ($this->request->is('post')) {
            $throttler = service('throttler');
            if (! $throttler->check(md5('register:' . $this->request->getIPAddress()), 5, 600)) {
                return redirect()->back()->with('error', 'Слишком много попыток регистрации. Подожди 10 минут.')->withInput();
            }

            $name     = trim((string) $this->request->getPost('name'));
            $email    = trim((string) $this->request->getPost('email'));
            $password = (string) $this->request->getPost('password');

            $errors = [];

            if ($name === '' || mb_strlen($name) > 20) {
                $errors[] = 'Имя персонажа должно быть от 1 до 20 символов.';
            }
            if (! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
                $errors[] = 'Введи корректный email.';
            }
            if (strlen($password) < 8 || ! preg_match('/[A-Za-z]/', $password) || ! preg_match('/[0-9]/', $password)) {
                $errors[] = 'Пароль: минимум 8 символов, буквы и цифры.';
            }

            if (! $errors) {
                $userModel = new UserModel();
                if ($userModel->where('email', $email)->first()) {
                    $errors[] = 'Этот email уже зарегистрирован.';
                }
            }

            if ($errors) {
                return redirect()->back()->with('error', implode(' ', $errors))->withInput();
            }

            $userId = (new UserModel())->insert([
                'email'         => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);

            $characterId = (new CharacterModel())->insert([
                'user_id'        => $userId,
                'name'           => $name,
                'level'          => 1,
                'xp'             => 0,
                'gold'           => 20,
                'hp'             => 50,
                'max_hp'         => 50,
                'attack'         => 5,
                'defense'        => 2,
                'energy'         => 20,
                'max_energy'     => 20,
                'last_energy_at' => date('Y-m-d H:i:s'),
            ]);

            $this->session->set('user_id', $userId);
            $this->session->set('character_id', $characterId);

            return redirect()->to('/')->with('success', "Добро пожаловать, {$name}!");
        }

        return view('auth/register');
    }

    public function logout()
    {
        $this->session->destroy();
        return redirect()->to('/login');
    }
}
