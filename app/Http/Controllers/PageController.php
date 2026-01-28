<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function privacy()
    {
        return view('page.chinh-sach-bao-mat');
    }

    public function terms()
    {
        return view('page.dieu-khoan-dich-vu');
    }

    public function disclaimer()
    {
        return view('page.tuyen-bo-mien-tru-trach-nhiem');
    }

    public function about()
    {
        return view('page.ve-chung-toi');
    }
}
