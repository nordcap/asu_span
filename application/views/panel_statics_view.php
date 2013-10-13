<div id="panel_search" style=" padding-left: 20px; padding-top:5px;">
<?php
// отображение панели
print("<h3 style='padding-left: 30px; padding-bottom: 10px;'>Панель поиска</h3>");

$date1 = array(
'name'=>'date1_search',
'value'=>set_value('date1_search'));

$date2 = array(
'name'=>'date2_search',
'value'=>set_value('date2_search'));

$btn1 = array('name'=>'search','id'=>'btnsearch', 'value'=>'true');
$btn2= array('name'=>'clear','id'=>'btnclear', 'value'=>'true');


echo form_label('начало', 'date1_search');
echo form_input($date1);
echo form_label('конец', 'date2_search');
echo form_input($date2);
//echo '<div style="border: 1px solid gray; width: 130px; height: 20px; margin-top:10px; padding: 10px;">';
echo form_submit($btn1);
echo form_submit($btn2);
echo '</div>';  