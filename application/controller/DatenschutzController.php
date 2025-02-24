<?php

class DatenschutzController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(): void
    {
        $this->View->render('datenschutz/index');
    }
}
