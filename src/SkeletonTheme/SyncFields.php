<?php 

namespace SkeletonTheme;

class SyncFields {
    function __construct() {
        add_action('acf/settings/load_json', [$this, 'resolveBlockJsonLocation']);
        //add_action('acf/settings/save_json', [$this, 'resolveSaveBlockLocation']);

        add_action('acf/update_field_group', [$this, 'resolveSaveBlockLocation']);
    }

    function resolveSaveBlockLocation($args) {
        echo '<pre>';
        print_r($args);
        echo '</pre>';

        do_action('acf/settings/save_json', function($path) { echo $path; return get_stylesheet_directory().'/acf-test-folder-'.rand(100); } );
    }

    function resolveBlockJsonLocation($data) {

        $blocksDir = 'blocks';
        
        $iter = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator(locate_template($blocksDir), \RecursiveDirectoryIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::SELF_FIRST,
			\RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
		);

		$paths = array($blocksDir);
		foreach ($iter as $path => $dir) {
			if ($dir->isDir()) {
				$path = str_replace(get_template_directory() . '/', '', $path);
				$path = str_replace('\\', '/', $path);
				$paths[] = $path . '/';
			}
		}

        foreach ($paths as $path) {
			$dir = new \DirectoryIterator(locate_template($path));
			foreach ($dir as $file_info) {
				$file = $file_info->getFilename();
				$pathName = $file_info->getPathName();
				if ($file_info->isFile() && $file_info->getExtension() == 'json') {
					$fieldsRegistationJson = get_stylesheet_directory().'/'. $path;
                    $data[] = $fieldsRegistationJson;
				}
			}
		}

        return $data;
    }

    
}