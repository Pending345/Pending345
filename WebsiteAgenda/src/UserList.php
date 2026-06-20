<?php

namespace Calendar;

class UserList
{
    private array $users = [];

    public function addUser(User $user): string
    {
        $this->users[] = $user;
        return "User {$user->getName()} added to list!";
    }

    public function getUsers(): array
    {
        return $this->users;
    }

    public function getUser(string $name): User|string
    {
        foreach ($this->users as $user) {
            if ($user->getName() === $name) {
                return $user;
            }
        }
        return "User {$name} not found!";
    }

    public function getUserByEmail(string $email): User|string
    {
        foreach ($this->users as $user) {
            if ($user->getEmail() === $email) {
                return $user;
            }
        }
        return "User {$email} not found!";
    }

    public function removeUser(User $user): string
    {
        $key = array_search($user, $this->users);
        if ($key !== false) {
            unset($this->users[$key]);
            return "User {$user->getName()} removed from list!";
        }
        return "User {$user->getName()} not found!";
    }
}