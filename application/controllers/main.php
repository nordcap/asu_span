<?php
if (!defined('BASEPATH'))
  exit ('No direct script access allowed');
session_start();
class Main extends CI_Controller
{

/**
* Контроллер Main является главным конструктором и похоже единственным
*/
  function __construct()
  {
    parent :: __construct();
    $this->load->helper('date');
    $this->load->model('Main_model');
    $this->load->library('form_validation');

    //$this->output->enable_profiler(TRUE);

  }

  public function index()
  {
    $output = array('output' => '', 'js_files' => array(), 'css_files' => array(), 'method' => 'statics','res'=>array());
    $this->view_output($output);
  }
  //вывод CRUD таблицы

  public function view_output($output = null)
  {

    $this->load->view('main_view', $output);

	
  }
  //статистика

  public function statics()
  {

    $output = array('output' => '', 'js_files' => array(), 'css_files' => array(), 'method' => 'statics', 'res'=>array());
    if ($this->input->post('search')) {
        if($this->form_validation->run() == true)
        {
            $date1_search = $this->format_date($this->input->post('date1_search'));
            $date2_search = $this->format_date($this->input->post('date2_search'));
            $condition = array('Date >='=>$date1_search, 'Date <='=>$date2_search);


                 $output['res'] = $this->Main_model->search($condition);
        }
    //заносим в сессию данные
    $_SESSION['data_print'] = $output['res'];
    }
    if ($this->input->post('clear')) {
      //перегружаем страницу чтобы очистить поля
      redirect('main', 'refresh');
    }


    $this->view_output($output);
  }
  //списание расходников

  public function del_span()
  {
    $output = $this->Main_model->get_del();
    $this->view_output($output);
  }
  //приход расходников

  public function add_span()
  {
    $output = $this->Main_model->get_add();
    $this->view_output($output);
  }
  //принтеры

  public function printers()
  {
    $output = $this->Main_model->get_printers();
    $this->view_output($output);
  }
  //расходники

  public function spans()
  {
    $output = $this->Main_model->get_spans();
    $this->view_output($output);
  }
  //комнаты

  public function rooms()
  {
    $output = $this->Main_model->get_rooms();
    $this->view_output($output);
  }
  //форматирование строки вводимой даты с хх.хх.хххх на хххх-хх-хх в формат MYSQL
  //------------------------------------------------------------------

  public function format_date($str)
  {
    list($day, $month, $year) = explode('.', $str);
    return $year . '-' . $month . '-' . $day;
  }
  //проверка даты на правильность форматирования
  //------------------------------------------------------------------

  public function check_date($date)
  {
    if (preg_match('/[\d]{0,2}\.[\d]{1,2}\.[\d]{2,4}/', $date))
    {
      list($day, $month, $year) = explode('.', $date);
      if (checkdate($month, $day, $year) === TRUE)
      {
        return TRUE;
      }
      else
      {
        return FALSE;
      }
    }
    else
    {
      return FALSE;
    }
  }
  //заполнение начальной даты
  //------------------------------------------------------------------

  public function check_empty_date1($date1)
  {
    if (empty ($date1))
    {
      return '01.01.1980';
    }
    else
    {
      return TRUE;
    }

  }
  //заполнение конечной даты
  //------------------------------------------------------------------

  public function check_empty_date2($date2)
  {
    if (empty ($date2))
    {
      return mdate("%d.%m.%Y");
    }
    else
    {
      return TRUE;
    }
  }
  //сравнение дат
  //------------------------------------------------------------------

  public function compare_date()
  {
    $arg1 = strtotime($this->input->post('date1_search'));
    $arg2 = strtotime($this->input->post('date2_search'));
    if ($arg1 <= $arg2)
    {
      return TRUE;
    }
    else
    {
      return FALSE;
    }
  }

  //отправка на страницу печати
  //------------------------------------------------------------------
  public function in_print() {
    if ( isset($_SESSION['data_print']))
    {
      $data['res'] = $_SESSION['data_print'];

      $this->load->view('table_print', $data);
      session_unset();
      session_destroy();
    }
    else
    {
      //перегружаем страницу чтобы очистить поля
      redirect('main', 'refresh');

    }
  }

}