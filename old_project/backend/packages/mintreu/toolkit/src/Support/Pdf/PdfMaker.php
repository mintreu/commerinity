<?php

namespace Mintreu\Toolkit\Support\Pdf;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\App;

class PdfMaker
{




    public static function make(): static
    {
        return new static();
    }






}
