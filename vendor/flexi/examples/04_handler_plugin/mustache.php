<?php
error_reporting(E_ALL ^ E_NOTICE);

// load flexi lib
require_once dirname(__FILE__) . '/../../lib/flexi.php';

// where are the templates
$path_to_the_templates = dirname(__FILE__) . '/templates';

// we need a template factory
$factory = new Flexi_TemplateFactory($path_to_the_templates);

// load haml plugin

require_once dirname(__FILE__) . '/../../lib/mustache_template.php';
$factory->add_handler('mustache', 'Flexi_MustacheTemplate');

// open template
$template = $factory->open('mustache');

$template->set_attributes(array(
  "name" => "Chris",
  "value" => 10000,
  "taxed_value" => 10000 - (10000 * 0.4),
  "in_ca" => true
));

// test mix of different template engines
$template->set_layout("layout");

// render template
echo $template->render();
