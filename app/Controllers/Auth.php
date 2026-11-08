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

            // Ввод: обрезаем пробелы, приводим к безопасному виду
            $name     = trim((string) $this->request->getPost('name'));
            $name     = preg_replace('/\s+/u', ' ', $name); // схлопывание пробелов
            $gender   = (string) $this->request->getPost('gender');
            $email    = trim((string) $this->request->getPost('email'));
            $password = (string) $this->request->getPost('password');
            $confirm  = (string) $this->request->getPost('password_confirm');

            $errors = [];

            // Никнейм: 1–64 символа, буквы/цифры/пробел/подчёркивание/дефис (Unicode), без решётки и html-символов
            if ($name === '' || mb_strlen($name) > 64) {
                $errors[] = 'Никнейм должен быть от 1 до 64 символов.';
            } elseif (preg_match('/[<>"\'#]/u', $name)) {
                $errors[] = 'Никнейм не может содержать символы < > " \' #.';
            } elseif (! preg_match('/^[\p{L}\p{N} _\-]+$/u', $name)) {
                $errors[] = 'Никнейм: только буквы, цифры, пробел, подчёркивание и дефис.';
            }

            // Пол: строгий белый список
            if (! in_array($gender, ['male', 'female'], true)) {
                $errors[] = 'Выбери пол персонажа.';
            }

            if (! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
                $errors[] = 'Введи корректный email.';
            }
            if (strlen($password) < 8 || ! preg_match('/[A-Za-z]/', $password) || ! preg_match('/[0-9]/', $password)) {
                $errors[] = 'Пароль: минимум 8 символов, буквы и цифры.';
            }
            if ($password !== $confirm) {
                $errors[] = 'Пароли не совпадают.';
            }

            $userModel = new UserModel();
            if (! $errors && $userModel->where('email', $email)->first()) {
                $errors[] = 'Этот email уже зарегистрирован.';
            }

            if ($errors) {
                return redirect()->back()->with('error', implode(' ', $errors))->withInput();
            }

            // Тег: ровно 4 цифры (0000–9999), уникален в паре с именем
            $characterModel = new CharacterModel();
            $tag    = null;
            $exists = static function (string $n, string $t) use ($characterModel): bool {
                return $characterModel->where('name', $n)->where('tag', $t)->countAllResults() > 0;
            };

            for ($i = 0; $i < 100; $i++) {
                $candidate = sprintf('%04d', random_int(0, 9999));
                if (! $exists($name, $candidate)) {
                    $tag = $candidate;
                    break;
                }
            }

            if ($tag === null) {
                return redirect()->back()->with('error', 'Это имя занято. Попробуй другое.')->withInput();
            }

            try {
                $userId = $userModel->insert([
                    'email'         => $email,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                ]);

                $characterId = $characterModel->insert([
                    'user_id'        => $userId,
                    'name'           => $name,
                    'tag'            => $tag,
                    'gender'         => $gender,
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
            } catch (\Throwable $e) {
                log_message('error', 'Register failed: {msg}', ['msg' => $e->getMessage()]);
                return redirect()->back()->with('error', 'Ошибка регистрации. Попробуй ещё раз.')->withInput();
            }

            $this->session->set('user_id', $userId);
            $this->session->set('character_id', $characterId);

            return redirect()->to('/')->with('success', "Добро пожаловать, {$name}#{$tag}!");
        }

        return view('auth/register');
    }

    public function logout()
    {
        $this->session->destroy();
        return redirect()->to('/login');
    }
}
