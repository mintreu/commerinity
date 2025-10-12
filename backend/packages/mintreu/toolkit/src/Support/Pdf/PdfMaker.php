<?php

namespace Mintreu\Toolkit\Support\Pdf;

class PdfMaker
{

    public static function make()
    {
        return new static();
    }


    public  function html()
    {
        return $this;
    }

    public  function view()
    {
        return $this;
    }



}
