<?php
namespace App\Service\User;

class UserConstants
{
    const RoleUser = 'ROLE_USER';
    const RoleAdmin = 'ROLE_ADMIN';

    public static array $UserTypes = [
        [
            'value' => 'home_buyer',
            'text' => 'Home Buyer',
        ],
        [
            'value' => 'home_seller',
            'text' => 'Home Seller',
        ],
    ];
}