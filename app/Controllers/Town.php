<?php

namespace App\Controllers;

class Town extends BaseController
{
    public function index()
    {
        if (! $this->currentCharacter()) {
            return redirect()->to('/login');
        }

        return view('game/town');
    }
}
