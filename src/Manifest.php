<?php

namespace Skeleton;

class Manifest {

    public $url;
    
    function __construct() {
        $this->url =  get_template_directory() . '/public/mix-manifest.php';
    }

	function getVersionedAsset($key) {
		if (file_exists($this->url)) {
			$assets = include($this->url);
			if (isset($assets[$key])) {
				return get_template_directory_uri() .'/public'. $assets[$key];
			}
		}
		return false;
	}

    function getApplicableAssets() {
		global $template;
		if (file_exists($this->url)) {
			$assets = include($this->url);
			//current page template file page index 404 etc.
			$templateName = str_replace('.php', '', basename($template));

			return array_filter($assets, function($key) use ($templateName) {
				$assetsParts = explode('/', $key);
				$fileName = explode('.', end($assetsParts));
				if ($assetsParts[2] == 'templates' & $fileName[0] == $templateName) { return true; }
			}, ARRAY_FILTER_USE_KEY);
		}
		return false;
	}

	function getApplicableStyles() {
		$assets = $this->getApplicableAssets();

		if ($assets) {
			return array_filter($assets, function($key) {
				$assetsParts = explode('/', $key);
				$fileName = explode('.', end($assetsParts));
				if ($fileName[1] == 'css') { return true; }
			}, ARRAY_FILTER_USE_KEY);
		}
	}

	function getApplicableScripts() {
		$assets = $this->getApplicableAssets();
		if ($assets) {
			return array_filter($assets, function($key) {
				$assetsParts = explode('/', $key);
				$fileName = explode('.', end($assetsParts));
				if ($fileName[1] == 'js') { return true; }
			}, ARRAY_FILTER_USE_KEY);
		}
	}

	function getApplicableVendorScripts() {
		global $template;
		if (file_exists($this->url)) {
			$file_headers = get_file_data(
				$template,
				['libs' => 'Libraries']
			);

			if (!empty($file_headers['libs'])) {
				return explode(' ', $file_headers['libs']);
			} else {
				return false;
			}
		}
		return false;
	}
}