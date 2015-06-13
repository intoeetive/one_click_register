<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=one_click_register'.AMP.'method=save_settings');?>

<?php 

echo '<p><strong>'.$url.'</strong></p>';

$this->table->set_template($cp_pad_table_template); 
$this->table->set_heading(
    array('data' => lang('preference'), 'style' => 'width:50%;'),
    lang('setting')
);


foreach ($settings as $key => $val)
{
	$this->table->add_row(lang('l_'.$key, $key), $val);
}

echo $this->table->generate();

?>
<?php $this->table->clear()?>

<p><?=form_submit('submit', lang('submit'), 'class="submit"')?></p>

<?php
form_close();

