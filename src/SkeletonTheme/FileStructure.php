<?php

namespace SkeletonTheme;

class FileStructure {
	function __construct() {
		//Set directory for templates
		$themeCoreTemplateTypes = [
			'index',
			'404',
			'archive',
			'author',
			'category',
			'tag',
			'taxonomy',
			'date',
			'embed',
			'home',
			'frontpage',
			'privacypolicy',
			'page',
			'paged',
			'search',
			'single',
			'singular',
			'attachment'
		];

		foreach ($themeCoreTemplateTypes as $type) {
			add_filter($type . '_template_hierarchy', [$this, 'change_template_path']);
		}

		add_filter('theme_page_templates', [$this, 'child_remove_page_templates']);
		add_filter( 'page_template', [$this, 'customPageLocation']);
	}

	function child_remove_page_templates( $page_templates ) {
		$templatesDir = 'templates';
		$iter = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator(locate_template($templatesDir), \RecursiveDirectoryIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::SELF_FIRST,
			\RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
		);
	
		foreach ($iter as $path => $dir) {
			if ($dir->isDir()) {
				$templateDir = new \DirectoryIterator($path);
				foreach ($templateDir as $templateFile) {
					if ($templateFile->isFile() && $templateFile->getExtension() == 'php') {
						$file_headers = get_file_data(
							$templateFile->getPath().'/'.$templateFile->getFilename(),
							[
								'title' => 'Template name',
							]
						);
	
						if (!empty($file_headers['title'])) {
							$page_templates[$dir->getBasename().'/'.$templateFile->getFilename()] = $file_headers['title'];
						}
					}
				}
			}
		}
	  return $page_templates;
	}

	function change_template_path($templates) {
		$templates = array_map(function($template) {
			return 'templates/'.str_replace('.php', '', $template).'/'.$template;
		}, $templates);
		return $templates;
	}

	function customPageLocation( $page_template )
	{
		if (is_page_template()) {
			$page_template = get_template_directory().'/templates/'.get_page_template_slug();
		}
		return $page_template;
	}
}








