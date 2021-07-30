<?php

namespace Skeleton;

class TimberSetup {
	function __construct() {
		if ( !class_exists('Timber')) {
			add_action(
				'admin_notices',
				function() {
					echo '<div class="error"><p><b>Timber</b> not activated. Make sure you activate the plugin in <a href="'
					. esc_url( admin_url( 'plugins.php#timber' ) ) . '">'
					. esc_url( admin_url( 'plugins.php' ) ) . '</a></p></div>';
				}
			);
			return false;
		}
		
		$timber = new \Timber\Timber();
		$timber::$dirname = array('templates', 'blocks');
		add_filter( 'timber/twig', [$this, 'add_to_twig']);
	}

	function add_to_twig( $twig ) {
		//Using Twig Extensions https://twig.symfony.com/doc/3.x/api.html#using-extensions
		$twig->addExtension(new \Twig\Extension\StringLoaderExtension());
		return $twig;
	}
}




