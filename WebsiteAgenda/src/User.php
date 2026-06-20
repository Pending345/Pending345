<?php

namespace Calendar;

class User
{
    private String $name;
    private String $email;
    private String $password;
    private Calendar $calendar;

    public function __construct()
    {
        $this->calendar = new Calendar;
    }

    public function setUser(String $name, String $email, String $password): void
    {
        $this->setName($name);
        $this->setEmail($email);
        $this->setPassword($password);
    }

    public function setName(String $name): void
    {
        $this->name = $name;
    }

    public function setEmail(String $email): void
    {
        $this->email = $email;
    }

    public function setPassword(String $password): void
    {
//        $this->password = $password;
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public function checkPassword(String $password): bool
    {
        if (password_verify($password, $this->password)) {
            return true;
        }
        return false;
    }

    /**
     * @return String
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return Calendar
     */
    public function getCalendar(): Calendar
    {
        return $this->calendar;
    }
}