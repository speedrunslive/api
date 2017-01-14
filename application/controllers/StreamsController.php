<?php
class StreamsController extends Zend_Rest_Controller
{
    private $streamRepo;
    
    public function init()
    {
        $this->streamRepo = new SRL_Data_StreamRepository();
    }
    
    public function indexAction()
    {
        $channels = $this->GetRequest()->GetParam("channels");
        $streams = array();
        
        $playerRepo = new SRL_Data_PlayerRepository();
        $channels = str_replace(",", "','", mysql_real_escape_string($channels));
        $players = $playerRepo->GetPlayersByStream($channels);
        
        foreach ($players as $player)
        {
            @$stream->player = $player;
            $stream->channel = $player->Channel();
            $stream->api = $player->Api();
            array_push($streams, $stream);
            unset($stream);
        }
        
        $this->view->streams = $streams;
    }
    
    public function getAction()
    {
        $user = $this->GetRequest()->GetParam("id");
        $channelArray = $this->streamRepo->GetStream($user);
        $channel = $channelArray["channel"];
        $api = $channelArray["api"];
        
        $cFlag = mysql_real_escape_string($this->GetRequest()->GetParam("channel"));
        
        if (!empty($cFlag)) {
            $channel = $user;
            $user = $this->streamRepo->GetPlayerByStream($channel);
        }
        
        $this->view->user = $user;
        $this->view->channel = $channel;
        $this->view->api = $api;
    }
    
    public function postAction()
    {

    }
    
    public function putAction()
    {
        $user = $this->GetRequest()->GetParam("id");
        $json = json_decode($this->GetRequest()->GetRawBody());
        if (isset($user) && isset($json->channel) && isset($json->api)) {
            $this->streamRepo->SetStream($user, $json->channel, $json->api);
            
            $this->view->user = $user;
            $this->view->channel = $json->channel;
            $this->view->api = $json->api;
        }
    }
    
    public function deleteAction()
    {
        $json = json_decode($this->GetRequest()->GetRawBody());
        if (isset($json->user)) {
            $this->streamRepo->RemoveStream($json->user);
        }
    }
    
    public function optionsAction()
    {
        
    }
}