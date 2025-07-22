<?php

namespace App\Repositories\Interfaces;

use App\Models\User;

interface UserRepositoryInterface
{
    public function create(array $data): User;
    public function update($id, array $data): User;
    public function login(array $credentials);
    public function logout();
    public function refresh();
}
