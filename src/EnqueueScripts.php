<?php

namespace Skeleton;

class EnqueueScripts {

	function __construct() {
		$this->manifest = new Manifest();
		add_action('wp_enqueue_scripts', [$this, 'skeleton_manifest'], 0);
		add_action('wp_enqueue_scripts', [$this, 'skeleton_vendor_libraries'], 1);
		add_action('wp_enqueue_scripts', [$this, 'skeleton_core_assets'], 2);
	}

	function skeleton_manifest() {
		wp_enqueue_script('manifest', $this->manifest->getVersionedAsset('/js/manifest.js'), false, null, true);
	}

	function skeleton_vendor_libraries() {
		$vendorScripts = $this->manifest->getApplicableVendorScripts();
		if (!empty($vendorScripts)) {
			foreach($vendorScripts as $vendorScript) {
				wp_enqueue_script('vendor-'.$vendorScript, $this->manifest->getVersionedAsset('/js/vendor/'.$vendorScript.'.js'), false, null, true);
			}
		}
	}

	function skeleton_core_assets() {
		wp_enqueue_style('app', $this->manifest->getVersionedAsset('/css/app.css'), false, null);
		wp_enqueue_script('app', $this->manifest->getVersionedAsset('/js/app.js'), false, null, true);

		$templateStyles = $this->manifest->getApplicableStyles();
		if (!empty($templateStyles)) {
			foreach($templateStyles as $key => $templateStyle) {
				$idKeySearchArray = explode('/', $key);
				$idKey = explode('.', end($idKeySearchArray));
				wp_enqueue_style('template-'.$idKey[0], $this->manifest->getVersionedAsset($key), false, null);
			}
		}

		$templateScripts = $this->manifest->getApplicableScripts();
		if (!empty($templateScripts)) {
			foreach($templateScripts as $key => $templateScript) {
				$idKeySearchArray = explode('/', $key);
				$idKey = explode('.', end($idKeySearchArray));
				wp_enqueue_script('template-'.$idKey[0], $this->manifest->getVersionedAsset($key), false, null, true);
			}
		}
	}
}

	