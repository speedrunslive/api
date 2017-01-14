<?php
class RacesController extends Zend_Rest_Controller
{
    private $raceRepo;
    private $entrantRepo;
    private $ratingRepo;
    // private $countryRepo;
    private $logname = "api-debugging_races_controller_log.log";
    private $debugcount = 0;
    private $debugid = "";
    // 0 = none
    // 3 = everything (this will include stack traces so yeah enjoy that)
    // 2 = extensive (default value for logging, will also log queries)
    // 1 = minimal, only things explicitly set to log to minimal
    private $debuglevel = 3;
    /*
     * The constructor used to create a connection.
     * We've removed this so that it only creates
     * a connection when the queries are executed 
     * or when explicitly calling EstablishConnection().
     */ 


    public function init()
    {
        $this->raceRepo = new SRL_Data_RaceRepository();
        $this->entrantRepo = new SRL_Data_EntrantRepository();
        $this->ratingRepo = new SRL_Data_RatingRepository();
        if ($_SERVER["SERVER_NAME"] == "api-beta.speedrunslive.com") { 
           $this->isDebugging = true;
           $this->debugid = md5(time());           
        }        
    }
    
    public function indexAction()
    {
        $races = $this->raceRepo->GetRaces(1);
        $raceCount = $this->raceRepo->CountRaces();
        $this->WriteLogExtensive("Race controller index called");
        $this->view->races = $races;
        $this->view->raceCount = $raceCount;
    }
    
    public function getAction()
    {
        $id = $this->GetRequest()->GetParam("id");

        $this->WriteLogExtensive("Race controller get called, returning race " . $id);
        $race = $this->raceRepo->GetRace($id);
        $this->view->race = $race;
        $this->view->lower = !is_null($this->GetRequest()->GetParam("lower"));
    }
    
    public function postAction()
    {
        $json = json_decode($this->GetRequest()->GetRawBody());
        if (isset($json->game)) {
            $race = $this->raceRepo->CreateRace($json->game);
            //$race = $this->raceRepo->CreateRace("mp1");
        }
        else {
            $race = $this->raceRepo->CreateRace("newgame");
        }
        
        $this->view->race = $race;
    }
    
    public function putAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        $this->WriteLogAll("Race controller called. ID: " . $id);
        $json = json_decode($this->GetRequest()->GetRawBody());
        $this->WriteLogAll($this->GetRequest()->GetRawBody());
        $race = $this->raceRepo->GetRace($id);
        
        if (isset($json->filename))
            $this->raceRepo->SetFilename($id);
        
        if (isset($json->game))
            $this->raceRepo->SetRaceGame($id, $json->game);
            
        if (isset($json->goal))
            $this->raceRepo->SetRaceGoal($id, $json->goal);
            
        if (isset($json->state))
            switch($json->state) {
                case 1:
                    // only rematch can set state back to entry open
                    break;
                case 2:
                    $this->raceRepo->SetRaceEntryClosed($id);
                    break;
                case 3:
                    $this->raceRepo->SetRaceInProgress($id);
                    break;
                case 4:
                    $this->raceRepo->SetRaceComplete($id);
                    break;
                case 5:
                    $this->raceRepo->SetRaceOver($id);
                    break;
            }
            
        if (isset($json->record) && $json->record == "true"
            && $race->State() == SRL_Core_RaceState::Complete) {
            $this->WriteLogExtensive("Recording the race.");
            $entrants = $this->entrantRepo->GetEntrants($id);
            $this->WriteLogExtensive("Entrants gathered, getting ratings.");            
            $currentRatings = $this->ratingRepo->GetCurrentRatings($race->Game(), $entrants);
            $count_of_entrants = count($entrants);
            $orderedRatings = array();
            $this->WriteLogExtensive("Looping entrants to set up ordered ratings array.");
            foreach ($entrants as $entrant) {
                $name = strtolower($entrant->Player()->Name());
                $currentRating = $currentRatings["$name"];
                $currentRating->place = $entrant->Place();
                $currentRating->name = $name;

                $orderedRatings[] = $currentRating;
                $this->WriteLogAll("Rating object: " . print_R($currentRating, true));                
                $this->WriteLogAll("Entrant object: " . print_R($entrant, true));
                unset($currentRating);
                unset($entrant);
            }
            $this->WriteLogExtensive("Calculating ratings off ordered ratings via ratings repository.");
            $success = true;
            //$newRatings = $this->ratingRepo->CalculateNewRatings($orderedRatings);                            
            $descriptorspec = array(
                0 => array("pipe", "r"),
                1 => array("pipe", "w"),
                2 => array("file", "/srv/racebot/json_output.txt", "w")
            );

            $process = proc_open('/usr/bin/python /srv/racebot/trueskill_calc.py', $descriptorspec, $pipes);

            if (is_resource($process)) {
                // fwrite($pipes[0], '[{"mu":25,"sigma":8.333333,"place":1,"name":"rainbowism"},{"mu":"23.247847","sigma":"5.506750","place":2,"name":"stauken"}]');
                fwrite($pipes[0], json_encode($orderedRatings));
                fclose($pipes[0]);

                $newRatings = json_decode(stream_get_contents($pipes[1]), true);
                var_dump($newRatings);
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);
            }
            foreach($newRatings as $newRating) {
                if (is_nan($newRating->mu) || is_nan($newRating->sigma)) {
                    $success = false;
                    $this->WriteLogMinimal("Failed to calculate score. Object dump: " . "\r\n" . print_R($newRating, true));
                    break;
                }
            }
            if (!$success) {
                $entrantcap = 150;
                if ($count_of_entrants < $entrantcap) {
                    $entrantcap = $count_of_entrants - 2;
                }
                while (!$success) {                
                    $this->WriteLogMinimal("Attempting to re-record the race with entrant cap at: " . $entrantcap);
                    $entrantcap  = $entrantcap  - 2;
                    $newRatings = $this->ratingRepo->CalculateNewRatings($orderedRatings,$entrantcap);
                    $success = true;     
                    foreach($newRatings as $newRating) {
                        if (is_nan($newRating->mu) || is_nan($newRating->sigma)) {
                            $success = false;
                            $this->WriteLogMinimal("Failed to calculate score. Object dump: " . "\r\n" . print_R($newRating, true));
                            break;
                        }
                    }
                    if ($entrantcap == 0) {
                        $this->WriteLogMinimal("Failed to record without trueskill, send halp!");
                    }
                    if ($entrantcap < 0) {
                        $entrantcap = 0;
                    }                    
                }
            }
            $this->raceRepo->RecordRace($id, $orderedRatings, $newRatings);
        }
        
        if (isset($json->rematch) && $json->rematch == "true") {
            $this->raceRepo->RematchRace($id);
        }
        
        $this->view->race = $this->raceRepo->GetRace($id);
    }
    
    public function deleteAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        $this->raceRepo->EndRace($id);
    }
    
    public function optionsAction()
    {
        
    }
    private function WriteLog($message, $level = 2) {
        if ($this->debuglevel >= $level) {
            $this->WriteLogMessage($message);
        }        
    }

    function WriteLogMinimal($message) {
        $this->WriteLog($message, 1);
    }    
    function WriteLogExtensive($message) {
        $this->WriteLog($message, 2);
    }
    function WriteLogAll($message) {
        $this->WriteLog($message, 3);
    }
    private function WriteLogMessage($message) {
//        $stringprint = "[ID#: " . substr($this->debugid,0,12) . "] [" . date('d/m/y G:i:s', microtime()) . "] ";
 //       $stringprint .= $message  . "\r\n";
  //      file_put_contents($this->logname, $stringprint, FILE_APPEND);


    }
}
