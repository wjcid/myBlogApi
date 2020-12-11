<?php
namespace app\event;

use app\model\User;

class Hite
{
    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}