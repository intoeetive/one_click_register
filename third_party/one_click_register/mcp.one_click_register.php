<?php

/*
=====================================================
 One Click Register
-----------------------------------------------------
 http://www.intoeetive.com/
-----------------------------------------------------
 Copyright (c) 2015 Yuri Salimovskiy
=====================================================
 This software is intended for usage with
 ExpressionEngine CMS, version 2.0 or higher
=====================================================
*/

if ( ! defined('BASEPATH'))
{
	exit('Invalid file request');
}

class One_click_register_mcp {

    var $version = 0.1;
    
    var $settings = array();
    
    function __construct() { 
        ee()->lang->loadfile('one_click_register');  
        ee()->lang->loadfile('member');
        
        $settings_q = ee()->db->select('settings')->from('modules')->where('module_name', 'One_click_register')->limit(1)->get(); 
        $this->settings = unserialize(base64_decode($settings_q->row('settings')));
    } 


    function index()
    {
        return $this->settings();
    }
    
    function settings()
    {
		
        ee()->load->helper('form');
    	ee()->load->library('table');
        
        $act = ee()->db->select('action_id')
						->from('actions')
						->where('class', 'One_click_register')
						->where('method', 'register')
						->get();
        
        $vars = array(
            'url' => rtrim(ee()->config->item('site_url'), '/').'/?ACT='.$act->row('action_id').'&secret='.$this->settings['secret'].'&email=email@email.email'
        );
 
        $vars['settings'] = array(	
            'secret'			=> form_input('secret', $this->settings['secret']),
            'email_subject'		=> form_input('email_subject', $this->settings['email_subject']),
            'email_template'	=> form_textarea('email_template', $this->settings['email_template']),
            
    		);
        
    	return ee()->load->view('settings', $vars, TRUE);
	
    }    
    
    function save_settings()
    {
		
		if (empty($_POST))
    	{
    		show_error(ee()->lang->line('unauthorized_access'));
    	}
        
        if (ee()->input->post('secret')=='')
    	{
    		show_error(ee()->lang->line('secret_empty'));
    	}

        unset($_POST['submit']);
        
        ee()->db->where('module_name', 'One_click_register');
    	ee()->db->update('modules', array('settings' => serialize($_POST)));
    	
    	ee()->session->set_flashdata(
    		'message_success',
    	 	ee()->lang->line('preferences_updated')
    	);
        
        ee()->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=one_click_register'.AMP.'method=index');
    }
  
}
/* END */
?>