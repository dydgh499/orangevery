<?php
namespace App\Enums;

enum IdentityAuthType: int {
    case BONEAJA        = 0;
    case KAKAO_AUTH     = 1;
    case KAKAO_ACCOUNT  = 2;
}
