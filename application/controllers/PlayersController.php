<?php
class PlayersController extends Zend_Rest_Controller
{
    private $playerRepo;
    
    public function init()
    {
        $this->playerRepo = new SRL_Data_PlayerRepository();
    }
    
    public function indexAction()
    {
        $search = $this->GetRequest()->GetParam("search");
        if (empty($search)) {
            $this->_forward('get', 'players', null, array("id" => Zend_Auth::getInstance()->getIdentity()->username));
        }
            
        else {
            $players = $this->playerRepo->GetPlayerSearch($search);
            $this->view->players = $players;
            $this->view->playerCount = count($players);
        }
    }
    
    public function getAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        $this->view->player = $this->playerRepo->GetPlayer($id);
    }
    
    public function postAction()
    {
        if ( Zend_Auth::getInstance()->getIdentity()->username != "racebot" ) {
            $this->getResponse()->setHttpResponseCode(404);
        }
        else {
            $json = json_decode($this->GetRequest()->GetRawBody());
            if ( isset($json->player) ) {
                $this->playerRepo->UpdateLastSeen($json->player);
            }
        }
    }
    
    public function putAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        
        $auth = Zend_Auth::getInstance();
        $identity = $auth->getIdentity();
        
        if (strcasecmp($identity->username, $id) != 0 && $identity->username != "racebot")
        {
            return;
        }
        
        $json = json_decode($this->GetRequest()->GetRawBody());

        if ( isset($json->youtube) ) {
            $youtubeRepo = new SRL_Data_YoutubeRepository();
            $json->youtube = str_replace('"', "", $json->youtube);
            $json->youtube = str_replace("'", "", $json->youtube);
            $youtubeRepo->SetYoutube($id, $json->youtube);
        }

        if ( isset($json->twitter) ) {
            $twitterRepo = new SRL_Data_TwitterRepository();
            $json->twitter = str_replace('"', "", $json->twitter);
            $json->twitter = str_replace("'", "", $json->twitter);
            $twitterRepo->SetTwitter($id, $json->twitter);
        }

        if ( isset($json->channel) && isset($json->api) ) {
            $streamRepo = new SRL_Data_StreamRepository();
            $json->channel = preg_replace("/[^A-Za-z0-9_-]/", "", $json->channel);
            $json->api = preg_replace('/[^a-z]/', "", $json->api);
            $streamRepo->SetStream($id, $json->channel, $json->api);
        }
        
        if ( isset($json->country) ) {
            $countryRepo = new SRL_Data_CountryRepository();
            $json->country = str_replace('"', "", $json->country);
            $json->country = str_replace("'", "", $json->country);
            $countryRepo->SetCountry($id, $json->country);
        }

        if ( isset($json->casename) ) {
            $json->casename = str_replace('"', "", $json->casename);
            $json->casename = str_replace("'", "", $json->casename);
            $this->playerRepo->RecasePlayer($id, $json->casename);
        }
        
        $this->view->player = $this->playerRepo->GetPlayer($id);
    }
    
    public function deleteAction()
    {
        $this->getResponse()->setHttpResponseCode(404);
    }
    
    public function optionsAction()
    {
        
    }
}
