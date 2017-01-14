<?php
class Common_Acl extends Zend_Acl
{
    public function __construct()
    {
        $this->addRole(new Zend_Acl_Role('anon'));
        $this->addRole(new Zend_Acl_Role('user'), 'anon');
        $this->addRole(new Zend_Acl_Role('voice'), 'user');
        $this->addRole(new Zend_Acl_Role('halfop'), 'voice');
        $this->addRole(new Zend_Acl_Role('op'), 'halfop');
        $this->addRole(new Zend_Acl_Role('admin'), 'op');
        
        $this->enforceController("entrants");
        $this->enforceController("error");
        $this->add(new Zend_Acl_Resource("error::error"));
        
        $this->enforceController("games");
        $this->enforceController("goals");
        $this->enforceController("index");
        
        $this->enforceController("leaderboard");
        $this->enforceController("migrate");
        $this->enforceController("pastraces");
        $this->enforceController("races");
        $this->enforceController("ratings");
        
        $this->enforceController("rules");
        $this->enforceController("stat");
        $this->enforceController("streams");
        $this->enforceController("test");
        $this->enforceController("twitter");
        $this->enforceController("youtube");
        $this->enforceController("token");
        $this->enforceController("players");
        $this->enforceController("rtaleaderboards");
        $this->enforceController("rtatimes");
        $this->enforceController("rtamilestones");
        $this->enforceController("tags");
        $this->enforceController("rtaleaderboardtags");
        $this->enforceController("seasons");
        $this->enforceController("seasongoal");
        $this->enforceAuthenticatedController("streamspage", 'op');
        $this->enforceController("country");
	$this->enforceController("offset");
        
        $this->enforceLoggedinController("streamprefs", 'user');
        $this->enforceLoggedinController("adminlog", 'voice');
        $this->enforceLoggedinController("namechanger", 'op');
        $this->enforceLoggedinController("timeeditor", 'halfop');
        $this->enforceController("goalchanger", 'halfop');
        $this->allow('voice', "goalchanger::put");
        $this->allow('anon', "streamprefs::get");
        
        $this->allow('anon', "token::put");
        $this->allow('user', "players::put");
    }
    
    private function enforceLoggedinController($controllerName, $accessLevel)
    {
        $this->add(new Zend_Acl_Resource("$controllerName::index"));
        $this->add(new Zend_Acl_Resource("$controllerName::get"));
        $this->add(new Zend_Acl_Resource("$controllerName::options"));
        $this->add(new Zend_Acl_Resource("$controllerName::put"));
        $this->add(new Zend_Acl_Resource("$controllerName::post"));
        $this->add(new Zend_Acl_Resource("$controllerName::delete"));
        
        $this->allow($accessLevel, "$controllerName::index");
        $this->allow($accessLevel, "$controllerName::get");
        $this->allow('anon', "$controllerName::options");
        $this->allow($accessLevel, "$controllerName::put");
        $this->allow($accessLevel, "$controllerName::post");
        $this->allow($accessLevel, "$controllerName::delete");
    }
    private function enforceAuthenticatedController($controllerName, $authLevel)
    {
        $this->add(new Zend_Acl_Resource("$controllerName::index"));
        $this->add(new Zend_Acl_Resource("$controllerName::get"));
        $this->add(new Zend_Acl_Resource("$controllerName::options"));
        $this->add(new Zend_Acl_Resource("$controllerName::put"));
        $this->add(new Zend_Acl_Resource("$controllerName::post"));
        $this->add(new Zend_Acl_Resource("$controllerName::delete"));
        
        $this->allow('anon', "$controllerName::index");
        $this->allow('anon', "$controllerName::get");
        $this->allow('anon', "$controllerName::options");
        $this->allow($authLevel, "$controllerName::put");
        $this->allow($authLevel, "$controllerName::post");
        $this->allow($authLevel, "$controllerName::delete");
    }
    private function enforceController($controllerName)
    {
        return $this->enforceAuthenticatedController($controllerName, 'voice');
    }
}
