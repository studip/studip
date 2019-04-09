#!/usr/bin/env php
<?php
require_once 'studip_cli_env.inc.php';

$dir = new FilesystemIterator($STUDIP_BASE_PATH . '/lib/models');
foreach ($dir as $fileinfo) {
    $class = mb_strstr($fileinfo->getFilename(), '.', true);
    if (!in_array($class, words('SimpleCollection SimpleORMap SimpleORMapCollection StudipArrayObject')) && class_exists($class)) {
        echo $class . "\n";
        $model = new $class;
        $meta = $model->getTableMetaData();
        $props = [];
        foreach ($meta['fields'] as $field => $info) {
            $name = mb_strtolower($field);
            $props[$name] = '@property string ' . $name;
            $props[$name] .= ' database column';
            if ($alias = array_search($name, $meta['alias_fields'])) {
                $props[$alias] = '@property string ' . $alias;
                $props[$alias] .= ' alias column for ' . $name;
            }
        }
        foreach ($meta['additional_fields'] as $field => $info) {
            $name = mb_strtolower($field);
            $props[$name] = '@property string ' . $name;
            $props[$name] .= ' computed column';
            $getter = isset($info['get']) || method_exists($model, 'get' . $name);
            $setter = isset($info['set']) || method_exists($model, 'set' . $name);
           
            if ($setter && $getter) {
                $props[$name] .= ' read/write';
            } else if ($setter) {
                $props[$name] .= ' read only';
            }
        }
        foreach ($meta['relations'] as $relation) {
            $options = $model->getRelationOptions($relation);
            $props[$relation] = '@property ';
            if ($options['type'] === 'has_many' ||
            $options['type'] === 'has_and_belongs_to_many') {
                $props[$relation] .= 'SimpleORMapCollection';    
            } else {
                $props[$relation] .= $options['class_name'];
            }
            $props[$relation] .= ' ' . $relation;
            $props[$relation] .= ' ' . $options['type'] . ' ' . $options['class_name'];
        }
        $props = array_map(function($p) {return ' * ' . $p . "\n";}, $props);
        $file = file($fileinfo->getPathname());
        foreach ($file as $n => $line) if (mb_strpos($line, 'class') === 0) break;
        if ($n < count($file)) {
            $classstart = $n;
            $propend = null;
            $propstart = null;
            $docend = null;
            for ($n; $n >= 0; --$n) {
                if (!isset($docend) && mb_strpos($file[$n], ' */') === 0) $docend = $n;
                if (!isset($propend) && mb_strpos($file[$n], ' * @property') === 0) $propend = $n;
                if (isset($propend) && mb_strpos($file[$n], ' * @property') === 0) $propstart = $n;
            }
            if (isset($docend)) {
                if (isset($propstart)) {
                    array_splice($file, $propstart, $propend-$propstart+1, $props);
                } else {
                    array_splice($file, $docend, 0, $props);
                }
                $ok = file_put_contents($fileinfo->getPathname(), join('', array_map(function($l) {return rtrim($l, "\r\n") . PHP_EOL;}, $file)));
                if ($ok) echo $fileinfo->getPathname() . " written \n";
                else echo $fileinfo->getPathname() . " not writable \n";
            } else {
                echo 'no docblock found in ' . $fileinfo->getPathname() . chr(10);
            }
            
        }
    }
}
