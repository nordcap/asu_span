<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//определяем правила валидации


$config = array(
									array(
										'field'=>'date1_search',
										'label'=>'lang:date1_search',
										'rules'=>'trim|callback_check_empty_date1|callback_check_date'
									),
									array(
										'field'=>'date2_search',
										'label'=>'lang:date2_search',
										'rules'=>'trim|callback_check_empty_date2|callback_check_date|callback_compare_date'
									)

);
