<?php
class GamesController extends Zend_Rest_Controller
{
    private $gameRepo;
    
    public function init()
    {
        $this->gameRepo = new SRL_Data_GameRepository();
    }
    
    public function indexAction()
    {
        $search = $this->GetRequest()->GetParam("search");
        if (empty($search)) {
            $page = 1;
            if (isset($_GET["page"])) {
                $page = $_GET["page"];
            }
            $games = $this->gameRepo->GetGames($page);
            $count = $this->gameRepo->CountGames();
            
            $this->view->games = $games;
            $this->view->gameCount = $count;
        } else {
            $games = $this->gameRepo->GetGameSearch($search);

            $this->view->games = $games;
            $this->view->gameCount = count($games);
        }
    }
    
    public function getAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        $this->view->game = $this->gameRepo->GetGame($id);
    }
    
    public function postAction()
    {
        $this->getResponse()->setHttpResponseCode(404);
    }
    
    public function putAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        $json = json_decode($this->GetRequest()->GetRawBody());
        
        $game = null;
        if (isset($id) && isset($json->name)) {
            $game = $this->gameRepo->CreateGame(trim($id), trim($json->name));
        }
        
        $this->view->game = $game;
    }
    
    public function deleteAction()
    {
        $this->getResponse()->setHttpResponseCode(404);
    }
    
    public function optionsAction()
    {
        
    }
}