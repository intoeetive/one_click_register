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

class One_click_register_upd {

    var $version = 0.1;
    
    function __construct() { 

    } 
    
    function install() { 
  
        ee()->lang->loadfile('one_click_register');  
		
		ee()->load->dbforge(); 
        
        $settings = array(
            'secret'        => ee()->functions->random('alnum', 10),
            'email_subject' => lang('email_subject'),
            'email_template'=> lang('email_template'),
        );

        $data = array( 'module_name' => 'One_click_register' , 'module_version' => $this->version, 'has_cp_backend' => 'y', 'has_publish_fields' => 'n', 'settings'=>base64_encode(serialize($settings))); 
        ee()->db->insert('modules', $data); 
        
        $data = array( 'class' => 'One_click_register' , 'method' => 'register' ); 
        ee()->db->insert('actions', $data); 
        
        return TRUE; 
        
    } 
    
    
    function uninstall() { 

        ee()->load->dbforge(); 
		
		ee()->db->select('module_id'); 
        $query = ee()->db->get_where('modules', array('module_name' => 'One_click_register')); 
        
        ee()->db->where('module_id', $query->row('module_id')); 
        ee()->db->delete('module_member_groups'); 
        
        ee()->db->where('module_name', 'One_click_register'); 
        ee()->db->delete('modules'); 
        
        ee()->db->where('class', 'One_click_register'); 
        ee()->db->delete('actions'); 

        return TRUE; 
    } 
    
    function update($current='') 
	{ 
		return TRUE; 
    } 
	

}
/* END */
?>