<?php

class CommunityController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(): void
    {
        $this->View->render('community/index');
    }
}
