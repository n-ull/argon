<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function isBot()
    {
        $userAgent = request()->userAgent();

        return preg_match('/bot|crawl|slurp|spider/i', $userAgent);
    }

    public function isMobile()
    {
        $userAgent = request()->userAgent();

        return preg_match('/(android|iphone|ipad|ipod|windows phone|iemobile|opera mini)/i', $userAgent);
    }

    public function isDesktop()
    {
        return ! $this->isMobile() && ! $this->isBot();
    }
}
