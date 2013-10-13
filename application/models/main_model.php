<?php
if (!defined('BASEPATH'))
  exit ('No direct script access allowed');

class Main_model extends CI_Model
{

  function __construct()
  {
    parent :: __construct();
    $this->load->database();
    $this->load->library('grocery_CRUD');
  }

  //обработка списания расходников
  function get_del()
  {
    $crud = new grocery_CRUD();
    $crud->set_theme('datatables');
    //тема flexigrid или datatables
    $crud->set_subject('расход');
    //объект подстановки
    $crud->set_table('history_del');
    //основная таблица
    $crud->set_relation('spans_id', 'spans', 'Name');
    $crud->set_relation('printers_id', 'printers', 'Name');
    $crud->set_relation('rooms_id', 'rooms', 'Number');
    $crud->columns('Date', 'printers_id', 'spans_id', 'Count', 'rooms_id');
    //отображаемые столбцы
    $crud->display_as('Date', 'Дата')
    //псевдонимы столбцов
    ->display_as('Count', 'Кол-во')
    //псевдонимы столбцов
    ->display_as('spans_id', 'Расходник')
    //псевдонимы столбцов
    ->display_as('printers_id', 'Принтер')
    //псевдонимы столбцов
    ->display_as('rooms_id', 'Комната');
    //псевдонимы столбцов
    $crud->unset_edit();
    //запрещаем редактировать, тк сложно синхронизировать Count с табл spans
    //$crud->fields('id', 'Number');//поля которые можно редактировать
    $crud->required_fields('Date', 'spans_id', 'printers_id', 'rooms_id', 'Count');
    //обязательное поле
    $crud->set_rules('Count', 'Кол-во', 'integer');
    $crud->callback_before_insert(array($this, 'callback_before_insert_history_del'));
    $crud->callback_before_delete(array($this, 'callback_before_delete_history_del'));
    $output = $crud->render();
    return $output;
  }

  //обработка прихода расходников
  function get_add()
  {
    $crud = new grocery_CRUD();
    $crud->set_theme('datatables');
    //тема flexigrid или datatables
    $crud->set_subject('приход');
    //объект подстановки
    $crud->set_table('history_add');
    //основная таблица
    $crud->columns('Date', 'spans_id', 'Count');
    //отображаемые столбцы
    $crud->display_as('Date', 'Дата')
    //псевдонимы столбцов
    ->display_as('Count', 'Кол-во')
    //псевдонимы столбцов
    ->display_as('spans_id', 'Расходник');
    //псевдонимы столбцов
    $crud->set_relation('spans_id', 'spans', 'Name');
    $crud->unset_edit();
    //запрещаем редактировать, тк сложно синхронизировать Count с табл spans
    //$crud->fields('id', 'Number');//поля которые можно редактировать
    $crud->required_fields('Date', 'spans_id');
    //обязательное поле
    $crud->set_rules('Count', 'Кол-во', 'integer');
    $crud->callback_after_insert(array($this, 'callback_after_insert_history_add'));
    $crud->callback_before_delete(array($this, 'callback_before_delete_history_add'));
    $output = $crud->render();
    return $output;
  }

  //администрирование принтеров
  function get_printers()
  {
    $crud = new grocery_CRUD();
    $crud->set_theme('datatables');
    //тема flexigrid или datatables
    $crud->set_subject('принтер');
    //объект подстановки
    $crud->set_table('printers');
    //основная таблица
    $crud->columns('Name');
    //отображаемые столбцы
    $crud->display_as('Name', 'Наименование');
    //псевдонимы столбцов
    //$crud->fields('id', 'Number');//поля которые можно редактировать
    $crud->required_fields('Name');
    //обязательное поле
    $crud->order_by('Name');
    //сортировка
    $output = $crud->render();
    return $output;
  }

  //администрирование расходников
  function get_spans()
  {
    $crud = new grocery_CRUD();
    $crud->set_theme('datatables');
    //тема flexigrid или datatables
    $crud->set_subject('расходник');
    //объект подстановки
    $crud->set_table('spans');
    //основная таблица
    $crud->columns('Name', 'Current_Count');
    //отображаемые столбцы
    $crud->display_as('Name', 'Наименование')
    //псевдонимы столбцов
    ->display_as('Current_Count', 'Кол-во в наличии');
    //псевдонимы столбцов
    $crud->fields('Name', 'Current_Count');
    //поля которые можно редактировать
    $crud->required_fields('Name');
    $crud->callback_column('Name', array($this, 'callback_name'));
    //обязательное поле
    $crud->order_by('Name');
    //сортировка
    $output = $crud->render();
    return $output;
  }

  //администрирование комнат
  function get_rooms()
  {
    $crud = new grocery_CRUD();
    $crud->set_theme('datatables');
    //тема flexigrid или datatables
    $crud->set_subject('комнату');
    //объект подстановки
    $crud->set_table('rooms');
    //основная таблица
    $crud->columns('Number');
    //отображаемые столбцы
    $crud->display_as('Number', 'Номер комнаты');
    //псевдонимы столбцов
    //$crud->fields('id', 'Number');//поля которые можно редактировать
    $crud->required_fields('Number');
    //обязательное поле
    $crud->order_by('Number');
    //сортировка
    $output = $crud->render();
    return $output;
  }

  //вставка новой строки в в табл. history_add
  function callback_after_insert_history_add($post_array)
  {
    $sql = "UPDATE spans SET Current_Count = Current_Count + ? WHERE id = ?";
    $this->db->query($sql, array($post_array['Count'], $post_array['spans_id']));
    return TRUE;
  }

  //удаляется строка из табл. дохода history_add
  function callback_before_delete_history_add($primary_key)
  {
    $this->db->select('Count, spans_id')->from('history_add')->where('id', $primary_key);
    $query = $this->db->get();
    $row = $query->row_array();
    if ($this->synchro($row['spans_id'], $row['Count']))
    {
    //при успешной синхронизации выполнение продолжается
      return TRUE;
    }
    else
    {
    //иначе возникает ошибка
      return FALSE;
    }
  }

  //вставка новой строки в табл. расхода history_del
  function callback_before_insert_history_del($post_array)
  {
    if ($this->synchro($post_array['spans_id'], $post_array['Count']))
    {
    //при успешной синхронизации выполнение продолжается
      return TRUE;
    }
    else
    {
    //иначе возникает ошибка
      return FALSE;
    }
  }

  //удаление строки расхода в табл. history_del
  function callback_before_delete_history_del($primary_key)
  {
    $this->db->select('Count, spans_id')->from('history_del')->where('id', $primary_key);
    $query = $this->db->get();
    $row = $query->row_array();
    $sql = "UPDATE spans SET Current_Count = Current_Count + ? WHERE id = ?";
    $this->db->query($sql, array($row['Count'], $row['spans_id']));
    //file_put_contents('test',$primary_key);
    return TRUE;
  }

  //ф-ия отображения содержимого столбца полностью
  function callback_name($primary_key, $row)
  {
     return $row->Name;
  }

  //ф-ия синхронизации табл spans и history_add [history_del] при удалении расходников
  function synchro($spans_id, $count)
  {
  //находим текущее значение расходника
    $this->db->select('Current_Count')->from('spans')->where('id', $spans_id);
    $query = $this->db->get();
    $row_span = $query->row_array();
    //если оно больше удаляемого значения то проводим запрос на уменьшение
    if ($row_span['Current_Count'] >= $count)
    {
      $sql = "UPDATE spans SET Current_Count = Current_Count - ? WHERE id = ?";
      $this->db->query($sql, array($count, $spans_id));
      return TRUE;
    }
    else
    {
      return FALSE;
      //действия не происходит
    }
  }
  //ф-ия поиска информации по табл. расходников
  function search($condition)
  {

    $this->db->select('id, Name, Current_Count');
    $this->db->from('spans');
    $this->db->order_by('Name');
    $query = $this->db->get();

    if ($query->num_rows() > 0)
		{
			foreach($query->result_array() as $row )
			{
        $sumAdd = $this->getCountAdd($row['id'], $condition);
        $sumDel = $this->getCountDel($row['id'], $condition);
        $row['Count_Add'] = $sumAdd;
        $row['Count_Del'] = $sumDel;
				$result[] = $row; //$row-массив значений
			}
			return $result;
		}
		else
		{
			return NULL;
		}

  }
  //получить суммарное значение поставленного расходника
  function getCountAdd($id, $condition)
  {
      $this->db->select_sum('Count');
      $this->db->from('history_add');
      $this->db->where('spans_id',$id);
      $this->db->where($condition);
      $this->db->group_by('spans_id');
      $query = $this->db->get();

      if ($query->num_rows() > 0)
  		{
        $result = $query->row_array();
  			return $result['Count'];
  		}
  		else
  		{
  			return NULL;
  		}

  }
  //получить суммарное значение растраченных расходников
  function getCountDel($id, $condition)
  {
      $this->db->select_sum('Count');
      $this->db->from('history_del');
      $this->db->where('spans_id',$id);
      $this->db->where($condition);
      $this->db->group_by('spans_id');
      $query = $this->db->get();
/*      $str = $this->db->last_query();
      file_put_contents('query', $str);*/
      if ($query->num_rows() > 0)
  		{
        $result = $query->row_array();
  			return $result['Count'];
  		}
  		else
  		{
  			return NULL;
  		}
  }

}