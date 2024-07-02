<?php
namespace App\Enums;

enum AuthLoginCode: int {
    case NOT_FOUND      = -1;
    case SUCCESS        = 0;
    case WRONG_ACCESS   = 951;
    case WRONG_PASSWORD = 952;
    case LOCK_ACCOUNT   = 953;
    case NOT_ALLOW_FIRST_PASSWORD   = 954;
    case REQUIRE_PASSWORD_CHANGE    = 955;
    case REQUIRE_PHONE_AUTH         = 956;
    case REQUIRE_OTP_AUTH           = 957;
}
