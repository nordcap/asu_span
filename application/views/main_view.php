<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Учёт расходников</title>
	<meta name="keywords" content="" />
	<meta name="description" content="" />
	<script type="text/javascript" src="<?php print base_url('assets/grocery_crud/js/jquery-1.7.1.min.js');?>"></script>

	<?php foreach($css_files as $file) { ?> 	<link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" /> <?php }?>
<?php foreach($js_files as $file) { ?> 	<script src="<?php echo $file; ?>"></script> <?php } ?>
	<link rel="stylesheet" href="<?php print base_url()."application/css/style.css"; ?>" type="text/css" media="screen, projection" />

</head>
<body>
<div id="wrapper">
	<header id="header">
	<h1 style="font-size: 150%; color: #003366; ">АСУ-расход</h1>
			<nav>
				<ul>
					<li><?php print anchor('main/statics', 'Статистика');?></li>
					<li><?php print anchor('main/del_span', 'Списание');?></li>
					<li><?php print anchor('main/add_span', 'Приход');?></li>
					<li><?php print anchor('main/printers', 'Принтеры');?></li>
					<li><?php print anchor('main/spans', 'Расходники');?></li>
					<li><?php print anchor('main/rooms', 'Комнаты');?></li>
					<li><?php print ('<div id="print">' . anchor('main/in_print', 'Печать') . '</div>');?></li>

				</ul>
			</nav>
	</header><!-- #header-->
	<section id="middle">
		<div id="container">
			<div id="content">
      <?php  echo validation_errors('<div style="color:red;">', '</div>'); ?>
    <div>
		<?php echo $output;
          if(isset($res))
          {
            $this->load->view('search_view');
          }
    ?>
    </div>
			<p></p><!--буферная зона-->
			</div><!-- #content-->
		</div><!-- #container-->
  		<aside class="sideRight">
  		<!--Показываем панель поиска-->
      <?php
      if (isset($method))
      {
        print form_open('main/statics');
         $this->load->view('panel_statics_view');
        print form_close();

      }

      ?>
      </aside><!-- #sideRight -->
	</section><!-- #middle-->
</div><!-- #wrapper -->
<footer id="footer">
	<strong>&copy; Budaev A. 2012</strong>
	<?php print '<br/>время выполнения скрипта  '.$this->benchmark->elapsed_time().' сек.'; ?>
</footer><!-- #footer -->
</body>
<script type="text/javascript">
$(document).ready(function(){
  $('#search tr>td:nth-child(1)').css('text-align','left');
});
</script>
</html>