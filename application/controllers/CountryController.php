<?php
class CountryController extends Zend_Rest_Controller
{
    private $countryRepo;
    
    public function init()
    {
        $this->countryRepo = new SRL_Data_CountryRepository();
    }
    
    public function indexAction()
    {
        $countries = $this->countryRepo->GetCountries();
        
        $this->view->countries = $countries;
    }
    
    public function getAction()
    {
    
    }
    
    public function postAction()
    {
    
    }
    
    public function putAction()
    {
        
    }
    
    public function deleteAction()
    {
    
    }
    
    public function optionsAction()
    {
        
    }
}