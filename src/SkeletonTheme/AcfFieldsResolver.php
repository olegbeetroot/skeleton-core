<?php

namespace SkeletonTheme;

class AcfFieldsResolver {
   
    function __construct() {
        add_filter('acf/settings/save_json', [$this, 'my_acf_json_save_point']);
        add_filter('acf/settings/load_json', [$this, 'my_acf_json_load_point']);
        $path = get_stylesheet_directory() . '/fields';
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
    }

    function my_acf_json_save_point( $path ) {
        
        $savePath = $this->defineBlockLocation($_POST);

        if (!empty($savePath)) {
            $path = get_stylesheet_directory() . $savePath;
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            return $path;
        }
    }

    function my_acf_json_load_point( $paths ) {       
        $paths = array_merge(
            $this -> findFieldsLocation('blocks'),
            $this -> findFieldsLocation('fields'),
        );
        return $paths; 
    }

    function defineBlockLocation($block) {
        if (empty($block['acf_field_group'])) return false;
        $location = $block['acf_field_group']['location'];

        $rules = [];
        foreach($location as $group) {
            $group = array_values($group);
            $rules = array_merge($rules, $group);
        }

        $savePaths = array_map(function($rule){
            if ($rule['param'] == 'block') {
                return '/blocks/'.str_replace('acf/', '', $rule['value']);
            } else {
                return '/fields/'.$rule['param'].'/'.str_replace(':', '/', $rule['value']);
            }
        }, $rules);

        return $savePaths[0];
    }

    function findFieldsLocation($folder) {
        $iter = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator(locate_template($folder), \RecursiveDirectoryIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::SELF_FIRST,
			\RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
		);
        $paths = [];
		foreach ($iter as $path => $dir) {
			if ($dir->isDir()) {
				$path = str_replace(get_template_directory() . '/', '', $path);
				$path = str_replace('\\', '/', $path);
				$paths[] = get_stylesheet_directory() .'/'. $path . '/';
			}
		}
        return $paths;
    }
}