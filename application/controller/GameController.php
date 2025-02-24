<?php

class GameController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    // Index-Methode für die Spiele-Seite
    public function index(): void
    {
        $games = GameModel::getAllGames();
        $this->View->render('game/index', ['games' => $games]);
    }

    public function search(): void
    {
        $games = GameModel::searchGames($_POST['search']);
        $this->View->render('game/index', ['games' => $games]);
    }


}
