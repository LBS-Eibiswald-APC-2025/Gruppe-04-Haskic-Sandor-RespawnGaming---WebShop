<?php /** @noinspection PhpUnused */

class IndexController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(): void
    {
        require_once APP . 'model/GamesModel.php';
        $games = GamesModel::getAllGames();
        $this->View->render('index/index', ['games' => $games]);
    }
}
