<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class {{className}} extends Model
{
    use HasFactory;

    public static function validate($request)
    {
        $request->validate([
{{properties}}            
        ]);
    }
}