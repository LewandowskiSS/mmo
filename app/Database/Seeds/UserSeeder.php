<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\UserModel;
use App\Models\CharacterModel;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Создаём тестового пользователя
        $userModel = new UserModel();
        $email = 'test@example.com';
        $password = 'Pass1234'; // минимум 8 символов, буквы+цифры

        // Если пользователь уже существует – не создаём дубль
        $existing = $userModel->where('email', $email)->first();
        if ($existing) {
            $userId = $existing->id;
        } else {
            $userId = $userModel->insert([
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
        }

        // Добавляем персонажа, если его нет
        $characterModel = new CharacterModel();
        $character = $characterModel->where('user_id', $userId)->first();
        if (! $character) {
            // генерируем уникальный тег 4 цифры
            $tag = null;
            $name = 'ТестовыйПерсонаж';
            for ($i = 0; $i < 100; $i++) {
                $candidate = sprintf('%04d', random_int(0, 9999));
                if (! $characterModel->where('name', $name)->where('tag', $candidate)->first()) {
                    $tag = $candidate;
                    break;
                }
            }
            if ($tag === null) {
                $tag = '0000'; // fallback
            }
            $characterId = $characterModel->insert([
                'user_id' => $userId,
                'name'    => $name,
                'tag'     => $tag,
                'gender'  => 'male',
                'level'   => 1,
                'xp'      => 0,
                'gold'    => 20,
                'hp'      => 50,
                'max_hp'  => 50,
                'attack'  => 5,
                'defense' => 2,
                'energy'  => 20,
                'max_energy' => 20,
                'last_energy_at' => date('Y-m-d H:i:s'),
            ]);
        }

        log_message('info', 'Тестовый аккаунт создан: email=' . $email . ' password=' . $password);
    }
}
