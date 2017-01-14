<?php
class StreamprefsController extends Zend_Rest_Controller
{
    private $adminLogRepo;
    private $streamprefsRepo;
    private $streamRepo;

    public function init()
    {
        $this->adminLogRepo = new SRL_Data_AdminLogRepository();
        $this->streamprefsRepo = new SRL_Data_StreamprefsRepository();
        $this->streamRepo = new SRL_Data_StreamRepository();
    }

    public function indexAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        $locked = $this->streamprefsRepo->isLocked($identity->username);
        $blacklisted = $this->streamRepo->isBlacklisted($identity->username);
        if ( $locked ) {
        	$this->view->frontpage_pref = 5;
        }
        else if ( $blacklisted ) {
        	$this->view->frontpage_pref = 6;
        }
        else {
        	$this->view->frontpage_pref = $this->streamRepo->getFrontpagePref($identity->username);
        }
        $this->view->pinnedStreams = $this->streamprefsRepo->getPinnedStreams($identity->username);
        $this->view->pinnedGames = $this->streamprefsRepo->getPinnedGames($identity->username);
        $this->view->hiddenStreams = $this->streamprefsRepo->getHiddenStreams($identity->username);
        $this->view->hiddenGames = $this->streamprefsRepo->getHiddenGames($identity->username);
        $this->view->stream = $this->streamRepo->GetStream($identity->username)["channel"];
        $this->view->streamapi = $this->streamRepo->GetStream($identity->username)["api"];
        $this->view->importpreference = $this->streamprefsRepo->GetImportPreference($identity->username);
        //$this->view->defaultSort = $this->streamprefsRepo->getDefaultSortPreference($identity->username);
    }
    public function getAction()
    {
        $id = $this->GetRequest()->GetParam("id");
        $this->view->players = $this->streamRepo->GetStreamInformation($id);
    }

    public function postAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        $username = $identity->username;
        $json = json_decode($this->GetRequest()->GetRawBody());
        if ( isset($json->pinnedstreams) || isset($json->hiddenstreams) || isset($json->pinnedgames) || isset($json->hiddengames) ) {
            $this->streamprefsRepo->clearPrefs($username);
        }
        if ( !$this->streamprefsRepo->isLocked($identity->username) ) {
        	$this->streamRepo->setFrontpagePref($username, $json->frontpage_pref);
        }
        if ( isset($json->default_sort) ) {
            $this->streamprefsRepo->setDefaultSortPreference($username, $json->default_sort);
        }
        if ( isset($json->pinnedstreams) ) {
            foreach ( $json->pinnedstreams as $stream ) {
               if ( $stream != "" ) { $this->streamprefsRepo->pinStream($username, $stream); }
            }
        }
        if ( isset($json->hiddenstreams) ) {
            foreach ( $json->hiddenstreams as $stream ) {
                if ( $stream != "" ) { $this->streamprefsRepo->hideStream($username, $stream); }
            }
        }
        if ( isset($json->pinnedgames) ) {
            foreach ( $json->pinnedgames as $game ) {
                if ( $game != "" ) { $this->streamprefsRepo->pinGame($username, strtolower($game)); }
            }
        }
        if ( isset($json->hiddengames) ) {
            foreach ( $json->hiddengames as $game ) {
                if ( $game != "" ) { $this->streamprefsRepo->hideGame($username, strtolower($game)); }
            }
        }
        if ( isset($json->importpreference) ) {
            $this->streamprefsRepo->SetImportPreference($username, $json->importpreference);
        }
    }

    public function putAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        $username = $identity->username;
        if ( $_SERVER['REQUEST_METHOD'] == "POST" && ( $identity->role == "admin" || $identity->role == "op" || $identity->role == "halfop" || $identity->role == "voice" ) ) {
            $player = $this->GetRequest()->GetParam("id");
            if ( $player != NULL ) {
                $json = json_decode($this->GetRequest()->GetRawBody());
                if ( $username == "racebot" && isset($json->source) ) { $username = $json->source; }
                $previousPreference = $this->streamRepo->GetFrontpagePref($player);
                $this->streamRepo->setFrontpagePref($player, $json->frontpage_pref);
                if ( $json->frontpage_pref == 1 && !isset($json->streamoption) ) {
                    if ( $this->streamRepo->recentlyPurged($player) ) { return; }
                    $stream = $this->streamRepo->GetStream($player)["channel"];
                    $warnings = $this->streamRepo->incrementWarningCount($player);
                    if ( isset($json->comment) && $json->comment != "" ) {
                        $comment = $json->comment;
                    }
                    if ( $warnings == NULL ) {
                        $commentappend = "Stream does not exist";
                    }
                    else {
                        $c = curl_init("http://api.speedrunslive.com/frontend/streams");
                        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
                        $r = json_decode(curl_exec($c));
                        curl_close($c);
                        $teamsJSON = $r->_source;
                        $streamtitle = "";
                        foreach ( $teamsJSON->channels AS $channel ) {
                            if ( $channel->name == $stream ) {
                                $streamtitle = "Title: ".$channel->title;
                            }
                        }
                        $commentappend = "Warning #: ".$warnings;
                        switch ( $previousPreference ) {
                            case 0:
                                $commentappend .= " / Preference: on";
                                break;
                            case 1:
                                $commentappend .= " / Preference: off";
                                break;
                            case 2:
                                $commentappend .= " / Preference: auto";
                                break;
                            case 3:
                                $commentappend .= " / Preference: races";
                                break;
                        }
                        if ( $streamtitle != "" ) {
                            $commentappend .= " / ".$streamtitle;
                        }
                    }
                    if ( $comment == "" ) {
                        $comment = $commentappend;
                    }
                    else {
                        $comment .= " / " . $commentappend;
                    }
                	$this->sendPurgeMail($player, $this->streamRepo->getWarningCount($player));
                    $this->adminLogRepo->LogAction($username, 'purged', $player, $comment);
                }
                return;
            }
        }
        $json = json_decode($this->GetRequest()->GetRawBody());

        if ( isset($json->pinnedstreams) ) {
            foreach ( $json->pinnedstreams as $stream ) {
                if ( $stream != "" ) { $this->streamprefsRepo->pinStream($username, $stream); }
            }
        }
        if ( isset($json->hiddenstreams) ) {
            foreach ( $json->hiddenstreams as $stream ) {
                if ( $stream != "" ) { $this->streamprefsRepo->hideStream($username, $stream); }
            }
        }
        if ( isset($json->pinnedgames) ) {
            foreach ( $json->pinnedgames as $game ) {
                if ( $game != "" ) { $this->streamprefsRepo->pinGame($username, strtolower($game)); }
            }
        }
        if ( isset($json->hiddengames) ) {
            foreach ( $json->hiddengames as $game ) {
                if ( $game != "" ) { $this->streamprefsRepo->hideGame($username, strtolower($game)); }
            }
        }
    }

    public function deleteAction()
    {
        $identity = Zend_Auth::getInstance()->getIdentity();
        $username = $identity->username;
        $json = json_decode($this->GetRequest()->GetRawBody());
        if ( isset($json->pinnedstreams) ) {
            foreach ( $json->pinnedstreams as $stream ) {
                if ( $stream != "" ) { $this->streamprefsRepo->unpinStream($username, $stream); }
            }
        }
        if ( isset($json->hiddenstreams) ) {
            foreach ( $json->hiddenstreams as $stream ) {
                if ( $stream != "" ) { $this->streamprefsRepo->unhideStream($username, $stream); }
            }
        }
        if ( isset($json->pinnedgames) ) {
            foreach ( $json->pinnedgames as $game ) {
                if ( $game != "" ) { $this->streamprefsRepo->unpinGame($username, strtolower($game)); }
            }
        }
        if ( isset($json->hiddengames) ) {
            foreach ( $json->hiddengames as $game ) {
                if ( $game != "" ) { $this->streamprefsRepo->unhideGame($username, strtolower($game)); }
            }
        }
    }

    public function optionsAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
    }

	private function getEmail($user) {
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        $r = curl_exec($c);
        curl_close($c);
        return $r;
    }

    private function sendPurgeMail($user, $warningNum) {
    	$email = $this->getEmail($user);
        $f = fopen("/tmp/email.txt", "w");
        fwrite($f, "To: $email\n");
        fwrite($f, "Subject: Non-speedrunning content warning $warningNum\n");
        fwrite($f, "From: no-reply@speedrunslive.com\n\n");
        if ( $warningNum == 1 ) {
	        fwrite($f, "Hello $user,\n\nIt seems you had an option toggled which displayed you on the SRL front page, while streaming non-speedrunning content. We've temporarily set your option to \"Don't display me\", which you can change back at your convenience by editing your personal settings on IRC; or by logging in to speedrunslive.com and changing this in the profile settings.\n\n");
	    }
	    else if ( $warningNum == 2 ) {
	    	fwrite($f, "Hello $user,\n\nIt seems you had an option toggled which displayed you on the SRL front page, while streaming non-speedrunning content. We've temporarily set your option to \"Don't display me\". As this is your second warning, you will need to wait 24 hours before you can change it back. You can do so by editing your personal settings on IRC; or by logging in to speedrunslive.com and changing this in the profile settings.\n\n");
	    }
	    else if ( $warningNum == 3 ) {
	    	fwrite($f, "Hello $user,\n\nIt seems you had an option toggled which displayed you on the SRL front page, while streaming non-speedrunning content. We've temporarily set your option to \"Don't display me\". As this is your third warning, you will need to wait 24 hours before you can change it back. You can do so by editing your personal settings on IRC; or by logging in to speedrunslive.com and changing this in the profile settings.\n\n");
	    }
	    else if ( $warningNum >= 4 ) {
	    	fwrite($f, "Hello $user,\n\nIt seems you had an option toggled which displayed you on the SRL front page, while streaming non-speedrunning content. We've temporarily set your option to \"Don't display me\". As you have already received three or more warnings, you will need to wait 1 week before you can change it back. You can do so by editing your personal settings on IRC; or by logging in to speedrunslive.com and changing this in the profile settings.\n\n");
	    }
		fwrite($f, "Please be mindful what option you have turned on while streaming non-speedrunning content. If you don't want to have to constantly change this option, we suggest you choose the \"Auto-Detect\" option. This will automatically show your stream if your twitch title includes one or more of the following words: 100%, any%, low%, attempt(s), IL, individual level(s), learning, planning, practice, practicing, race(s), routing, rta(s), run(s), speedrun(s), TAS, [srl].\nIt will automatically hide your stream, overruling the above, if your twitch title includes one or more of the following words:\nblind, casual, design(ing), let's play(s), [nosrl]\n\n");
        fwrite($f, "We try to ensure that all streams on the SRL front page are related to speedrunning. If you are flagged as streaming non-speedrunning content to the front page again in the future, you will be temporarily blacklisted, per the system outlined here: http://www.speedrunslive.com/faq/#whatcanistream\n\n");
        fwrite($f, "- speedrunslive.com administrators.");
        fclose($f);
        exec("sendmail -t < /tmp/email.txt");
        exec("rm -f /tmp/email.txt");
    }
}
?>
