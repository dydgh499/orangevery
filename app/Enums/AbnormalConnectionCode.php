<?php
namespace App\Enums;

enum AbnormalConnectionCode: int {
    case NO_REGISTER_IP = 0;
    case PARAM_MODULATION_APPROCH = 1;
    case CANNOT_ALLOW_TIME = 2;
    case BLOCK_IP = 3;
    case OPERATION_PERMIITED = 4;
    case MECRO = 5;
    case OVERSEA_IP = 6;
    case NOT_SAME_LOGIN_IP = 7;
}
