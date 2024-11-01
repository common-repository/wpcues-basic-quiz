<?php
$cont=preg_replace_callback('/\{\{\{(.*)\}\}\}/U',function($matches) use (&$replacements){
									return array_shift($replacements);
											}, $kawasaki);
										
?>