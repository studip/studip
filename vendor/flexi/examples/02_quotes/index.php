<?php

# Copyright (c)  2008 - Marcus Lunzenauer <mlunzena@uos.de>
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in all
# copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE.

# load flexi lib
require_once dirname(__FILE__) . '/../../lib/flexi.php';

# where are the templates
$path_to_the_templates = dirname(__FILE__) . '/templates';

# we need a template factory
$factory = new Flexi_TemplateFactory($path_to_the_templates);

# open template
$template = $factory->open('quotes');


# set quotes
$quotes = array(
  array('author' => 'August Strindberg',
        'quote'  => 'Der Mensch ist ein wunderliches Tier.'),
  array('author' => 'Pierre Reverdy',
        'quote'  => 'Der Mensch ist ein Tier, das sich selbst gezähmt hat.'),
  array('author' => 'Thomas Niederreuther',
        'quote'  => 'Der Mensch ist das einzige Tier, das sich für einen Menschen hält.'),
  array('author' => 'Durs Grünbein',
        'quote'  => 'Der Mensch ist das Tier, das Kaugummi kaut.'),
  array('author' => 'Mark Twain',
        'quote'  => 'Der Mensch ist das einzige Tier, das erröten kann - oder sollte.'));

# select one randomly
shuffle($quotes);
$quote_of_the_day = array_shift($quotes);

$template->set_attributes(array('quotes'           => $quotes,
                                'quote_of_the_day' => $quote_of_the_day));


# set current time
$time = time();
$template->set_attribute('time', $time);


# render template
echo $template->render();
