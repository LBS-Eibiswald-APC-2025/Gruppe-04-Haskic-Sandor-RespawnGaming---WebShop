<?php

class GamesController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    // Index-Methode für die Spiele-Seite
    public function index(): void
    {
        require_once APP . 'model/Game.php';
        $games = Game::getAllGames();
        $this->View->render('games/index', ['games' => $games]);
    }
}
