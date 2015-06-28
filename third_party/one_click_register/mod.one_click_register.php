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


class One_click_register {

    var $return_data	= ''; 	
    
    var $settings = array();

    /** ----------------------------------------
    /**  Constructor
    /** ----------------------------------------*/

    function __construct()
    {        
		ee()->lang->loadfile('one_click_register');  
        ee()->lang->loadfile('member');
        
        $settings_q = ee()->db->select('settings')->from('modules')->where('module_name', 'One_click_register')->limit(1)->get(); 
        $this->settings = unserialize(base64_decode($settings_q->row('settings')));
    }
    /* END */
    
    
    
    function register()
    {
        //no email submitted? do nothing  
        if (ee()->input->get('email')=='' || ee()->input->get('secret')=='')
        {
            ee()->output->show_user_error('general', lang('url_malformed'));
        }
        
        if ( ee()->config->item('allow_member_registration') != 'y' )
		{
			//ee()->output->show_user_error('general', lang('mbr_registration_not_allowed'));
		}
        
        if (ee()->input->get('secret')!=$this->settings['secret'])
        {
            ee()->output->show_user_error('general', lang('wrong_secret'));
        }

        //if email is already in database, do nothing
        $get_email_q = ee()->db->select('email')
                        ->from('members')
                        ->where('email', ee()->input->get('email'))
                        ->or_where('username', ee()->input->get('email'))
                        ->get();
        if ($get_email_q->num_rows()>0)
        {
            ee()->output->show_user_error('general', lang('email_registered'));
        }
        
        $users_qty = ee()->db->count_all('members');
        
        //otherwise, let's create an account
        $data = array();
        $data['email'] = $data['username'] = ee()->input->get('email');
        $data['screen_name'] = 'user'.($users_qty+1);
        $data['group_id'] = (ee()->config->item('req_mbr_activation')=='none') ? ee()->config->item('default_member_group') : 4;
        if (ee()->input->get('group_id')!==false && ee()->input->get('group_id')!==1) 
        {
            $data['group_id'] = ee()->input->get('group_id');
        }
		$data['ip_address']  = ee()->input->ip_address();
		$data['unique_id']	= ee()->functions->random('encrypt');
		$data['join_date']	= ee()->localize->now;
		$data['language']	= (ee()->config->item('deft_lang')) ? ee()->config->item('deft_lang') : 'english';
		$data['time_format'] = (ee()->config->item('time_format')) ? ee()->config->item('time_format') : 'us';
		$data['timezone']	= (ee()->config->item('default_site_timezone') && ee()->config->item('default_site_timezone') != '') ? ee()->config->item('default_site_timezone') : ee()->config->item('server_timezone');

		ee()->db->query(ee()->db->insert_string('exp_members', $data));
		$member_id = ee()->db->insert_id();
        
        //set the password
        $password = (ee()->input->get('password')!='')?ee()->input->get('password'):ee()->functions->random('alnum', 8);
        ee()->load->library('auth');
        ee()->auth->update_password($member_id, $password);

		ee()->db->query(ee()->db->insert_string('exp_member_data', array('member_id' => $member_id)));

		ee()->db->query(ee()->db->insert_string('exp_member_homepage', array('member_id' => $member_id)));

 		//send the email
		$action_id  = ee()->functions->fetch_action_id('Member', 'activate_member');

		$name = ($data['screen_name'] != '') ? $data['screen_name'] : $data['username'];

		$board_id = (ee()->input->get_post('board_id') !== FALSE && is_numeric(ee()->input->get_post('board_id'))) ? ee()->input->get_post('board_id') : 1;

		$forum_id = (ee()->input->get_post('FROM') == 'forum') ? '&r=f&board_id='.$board_id : '';
        
        $authcode_data = array('authcode' => ee()->functions->random('alnum', 10));

		$swap = array(
			'name'				=> $name,
			'activation_url'	=> ee()->functions->fetch_site_index(0, 0).QUERY_MARKER.'ACT='.$action_id.'&id='.$authcode_data['authcode'].$forum_id,
			'site_name'			=> stripslashes(ee()->config->item('site_name')),
			'site_url'			=> ee()->config->item('site_url'),
			'username'			=> $data['username'],
			'email'				=> $data['email'],
            'password'			=> $password
		 );

		$email_subject = ($this->settings['email_subject']!='')?$this->settings['email_subject']:lang('email_subject');
        $email_template = ($this->settings['email_template']!='')?$this->settings['email_template']:lang('email_template');
		$email_tit = $this->_var_swap($email_subject, $swap);
		$email_msg = $this->_var_swap($email_template, $swap);

		// Send email
		ee()->load->helper('text');

		ee()->load->library('email');
		ee()->email->wordwrap = true;
		ee()->email->from(ee()->config->item('webmaster_email'), ee()->config->item('webmaster_name'));
		ee()->email->to($data['email']);
		ee()->email->subject($email_tit);
		ee()->email->message(entities_to_ascii($email_msg));
		ee()->email->Send();
        
        ee()->db->where('member_id', $member_id);
        ee()->db->update('members', $authcode_data);
        
        ee()->stats->update_member_stats();
        
        $msg_data = array(	'title' 	=> ee()->lang->line('mbr_registration_complete'),
            				'heading'	=> ee()->lang->line('thank_you'),
            				'content'	=> lang('mbr_registration_completed')."\n\n".lang('mbr_membership_instructions_email'),
            				//'redirect'	=> $_POST['RET'],
            				'link'		=> array(ee()->config->item('site_url'), ee()->config->item('site_name')),
                            //'rate'		=> 5
        			 );
			
		ee()->output->show_message($msg_data);
    }
    

	/**
	 * Replace variables
	 */
	function _var_swap($str, $data)
	{
		if ( ! is_array($data))
		{
			return FALSE;
		}

		foreach ($data as $key => $val)
		{
			$str = str_replace('{'.$key.'}', $val, $str);
		}

		return $str;
	}
	
	



}
/* END */
?>