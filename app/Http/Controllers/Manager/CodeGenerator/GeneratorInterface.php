<?php

namespace App\Http\Controllers\Manager\CodeGenerator;

interface GeneratorInterface
{
    static public function create($generate_code);
    static public function bulkCreate($generate_code, $create_count);
}
