<?php
namespace App\Enums;

enum HistoryType: int {
    case CREATE = 0;
    case UPDATE = 1;
    case SELECT = 2;
    case DELETE = 3;
    case LOGIN  = 4;
    case BOOK   = 5;
    case BOOK_DELETE = 6;
    case HISTORY_DELETE = 7;
}
