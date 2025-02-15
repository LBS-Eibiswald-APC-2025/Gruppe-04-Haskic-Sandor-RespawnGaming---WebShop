<?php

class IndexController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(): void
    {
        require_once APP . 'model/Game.php';
        $games = Game::getAllGames();
        $this->View->render('index/index', ['games' => $games]);
    }
}
