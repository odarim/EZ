<?php

namespace App\Enum;

enum Roles: string
{
    case ROLE_ACL_DEFAULT = 'ROLE_USER';
    case ROLE_ACL_ALL = 'ROLE_ADMIN';
    case ROLE_ACL_CUSTOMER = 'ROLE_CUSTOMER';
    case ROLE_ACL_ALLOWED_TO_SWITCH = 'ROLE_ALLOWED_TO_SWITCH';
}
