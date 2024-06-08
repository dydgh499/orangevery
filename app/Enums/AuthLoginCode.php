<?php
namespace App\Enums;

enum AuthLoginCode: int {
    case NOT_FOUND      = -1;
    case SUCCESS        = 0;
    case WRONG_ACCESS   = 951;
    case WRONG_PASSWORD = 952;
    case LOCK_ACCOUNT   = 953;
    case REQUIRE_PHONE_AUTH  = 956;
}
