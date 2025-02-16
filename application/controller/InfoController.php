<?php

class InfoController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(): void
    {
        $this->View->render('info/index');
    }
}
