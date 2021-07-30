<?php

namespace SkeletonTheme;

class BlocksResolver
{
	function __construct()
	{
		$this->manifest = new Manifest();

		if ( !function_exists('acf_register_block')) {
			add_action(
				'admin_notices',
				function() {
					echo '<div class="error"><p><b>Advanced Custom Fields PRO</b> not activated. Make sure you activate the plugin in <a href="'
					. esc_url( admin_url( 'plugins.php#advanced-custom-fields-pro' ) ) . '">'
					. esc_url( admin_url( 'plugins.php' ) ) . '</a></p></div>';
				}
			);

			return false;
		}
		add_action('acf/init', [$this, 'findBlocks']);
		add_filter( 'block_categories_all', [$this, 'skeleton_new_block_category']);
	}

	public function skeleton_new_block_category( $categories ) {
		// Plugin’s block category title and slug.
		$block_category = array( 'title' => esc_html__( 'Custom blocks', 'text-domain' ), 'slug' => 'custom-blocks' );
		$category_slugs = wp_list_pluck( $categories, 'slug' );
		if ( ! in_array( $block_category['slug'], $category_slugs, true ) ) {
				$categories = array_merge(
						array(
								array(
										'title' => $block_category['title'], // Required
										'slug'  => $block_category['slug'], // Required
										'icon'  => 'wordpress', // Slug of a WordPress Dashicon or custom SVG
								),
							),
							$categories
				);
		}
		
		return $categories;
	}

	public function findBlocks() {
		// Get blocks from directory by absolute path
		$paths = $this->findAllBlockPaths('blocks');

		// Loop through found blocks
		foreach ($paths as $path) {
			$dir = new \DirectoryIterator(locate_template($path));
			foreach ($dir as $file_info) {
				$file = $file_info->getFilename();
				$pathName = $file_info->getPathName();
				$slug = str_replace('.php', '',  $file);
				if ($file_info->isFile() && $file_info->getExtension() == 'php' && !strpos($file,'-fields') && !empty($pathName)) {
					$data = $this->convertHeadersToParams($pathName, $slug, $path, $file);
					if ($data !== false && !empty($data['title'])) {

						//register block fields
						$fieldsRegistationFile = dirname(__FILE__).'/../'. $path . '/' . $slug . '-fields.php';
						if (file_exists($fieldsRegistationFile )) {
							require($fieldsRegistationFile);
						}
						$result = acf_register_block_type($data);
					}
				}
			}
		}
	}

	private function findAllBlockPaths($blocksDir) {
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

		return $paths;
	}

	private function convertHeadersToParams($pathName, $slug, $path, $file) {
		$file_headers = get_file_data(
			$pathName,
			[
				'title'                      => 'Block Name',
				'description'                => 'Description',
				'category'                   => 'Category',
				'icon'                       => 'Icon',
				'keywords'                   => 'Keywords',
				'mode'                       => 'Mode',
				'align'                      => 'Align',
				'post_types'                 => 'PostTypes',
				'supports_align'             => 'SupportsAlign',
				'supports_mode'              => 'SupportsMode',
				'supports_multiple'          => 'SupportsMultiple',
				'supports_anchor'            => 'SupportsAnchor',
				'enqueue_style'              => 'EnqueueStyle',
				'enqueue_script'             => 'EnqueueScript',
				'enqueue_assets'             => 'EnqueueAssets',
				'supports_custom_class_name' => 'SupportsCustomClassName',
				'supports_reusable'          => 'SupportsReusable',
				'example'                    => 'Example',
				'libs'											 =>	'Libraries',
				'supports_jsx'               => 'SupportsJSX',
				'parent'                     => 'Parent',
				'default_data'               => 'DefaultData'
			]
		);

		// Keywords exploding with quotes.
		$keywords = str_getcsv( $file_headers['keywords'], ' ', '"' );

		// Set up block data for registration.
		$data = array(
			'name'                       => $slug,
			'title'                      => $file_headers['title'],
			'description'                => $file_headers['description'],
			'category'                   => $file_headers['category'],
			'icon'                       => $file_headers['icon'],
			'keywords'                   => $keywords,
			'mode'                       => $file_headers['mode'],
			'align'                      => $file_headers['align'],
			'render_template'            => $path . $file,
			'enqueue_assets'             => $file_headers['enqueue_assets'],
			'supports_custom_class_name' => 'SupportsCustomClassName',
			'supports_reusable'          => 'SupportsReusable',
			'default_data'               => $file_headers['default_data'],
		);

		$data = array_filter( $data );

		// If the PostTypes header is set in the template, restrict this block
		// to those types.
		if ( ! empty( $file_headers['post_types'] ) ) {
			$data['post_types'] = explode( ' ', $file_headers['post_types'] );
		}
		// If the SupportsAlign header is set in the template, restrict this block
		// to those aligns.
		if ( ! empty( $file_headers['supports_align'] ) ) {
			$data['supports']['align'] =
				in_array( $file_headers['supports_align'], array( 'true', 'false' ), true ) ?
				filter_var( $file_headers['supports_align'], FILTER_VALIDATE_BOOLEAN ) :
				explode( ' ', $file_headers['supports_align'] );
		}
		// If the SupportsMode header is set in the template, restrict this block
		// mode feature.
		if ( ! empty( $file_headers['supports_mode'] ) ) {
			$data['supports']['mode'] =
				( 'true' === $file_headers['supports_mode'] ) ? true : false;
		}
		// If the SupportsMultiple header is set in the template, restrict this block
		// multiple feature.
		if ( ! empty( $file_headers['supports_multiple'] ) ) {
			$data['supports']['multiple'] =
				( 'true' === $file_headers['supports_multiple'] ) ? true : false;
		}
		// If the SupportsAnchor header is set in the template, restrict this block
		// anchor feature.
		if ( ! empty( $file_headers['supports_anchor'] ) ) {
			$data['supports']['anchor'] =
				( 'true' === $file_headers['supports_anchor'] ) ? true : false;
		}

		// If the SupportsCustomClassName is set to false hides the possibilty to
		// add custom class name.
		if ( ! empty( $file_headers['supports_custom_class_name'] ) ) {
			$data['supports']['customClassName'] =
				( 'true' === $file_headers['supports_custom_class_name'] ) ? true : false;
		}

		// If the SupportsReusable is set in the templates it adds a posibility to
		// make this block reusable.
		if ( ! empty( $file_headers['supports_reusable'] ) ) {
			$data['supports']['reusable'] =
				( 'true' === $file_headers['supports_reusable'] ) ? true : false;
		}

		// Gives a possibility to enqueue style. If not an absoulte URL than adds
		// theme directory.
		if ( ! empty( $file_headers['enqueue_style'] ) ) {
			if ( ! filter_var( $file_headers['enqueue_style'], FILTER_VALIDATE_URL ) ) {
				$data['enqueue_style'] =
					get_template_directory_uri() . '/' . $file_headers['enqueue_style'];
			} else {
				$data['enqueue_style'] = $file_headers['enqueue_style'];
			}
		} else {
			$styleFilename = str_replace('.php', '.css', $file);
			$styleAssets = $this->manifest->getVersionedAsset('/css/blocks/' .  $styleFilename);
		}

		// Gives a possibility to enqueue script. If not an absoulte URL than adds
		// theme directory.
		if ( ! empty( $file_headers['enqueue_script'] ) ) {
			if ( ! filter_var( $file_headers['enqueue_script'], FILTER_VALIDATE_URL ) ) {
				$data['enqueue_script'] =
					get_template_directory_uri() . '/' . $file_headers['enqueue_script'];
			} else {
				$data['enqueue_script'] = $file_headers['enqueue_script'];
			}
		} else {
			$scriptFilename = str_replace('.php', '.js', $file);
			$scriptAssets = $this->manifest->getVersionedAsset('/js/blocks/' .  $scriptFilename);
		}

		if (!empty($styleAssets) || !empty($scriptAssets)) {
			$data['enqueue_assets'] = function() use ($slug, $scriptAssets, $styleAssets, $file_headers) {
				if (!empty($styleAssets)) {
					wp_enqueue_style( 'block-'.$slug, $styleAssets, [], null);
				}
				if (!empty($scriptAssets)) {
					wp_enqueue_script( 'block-'.$slug, $scriptAssets, [], null, true);
				}

				//TODO: need to provide right order of vendor libraries
				// Detect extracted libraries for block
				// if (!empty($file_headers['libs'])) {
				// 	$vendorScripts = explode(' ', $file_headers['libs']);
				// 	if (!empty($vendorScripts)) {
				// 		foreach($vendorScripts as $vendorScript) {
				// 			wp_enqueue_script('vendor-'.$vendorScript, getVersionedAsset('/js/vendor/'.$vendorScript.'.js'), false, null, false);
				// 		}
				// 	}
				// }

			};
		}



		// Support for experimantal JSX.
		if ( ! empty( $file_headers['supports_jsx'] ) ) {
			// Leaving the experimaental part for 2 versions.
			$data['supports']['__experimental_jsx'] =
				( 'true' === $file_headers['supports_jsx'] ) ? true : false;
			$data['supports']['jsx']                =
				( 'true' === $file_headers['supports_jsx'] ) ? true : false;
		}

		// Support for "example".
		if ( ! empty( $file_headers['example'] ) ) {
			$json                       = json_decode( $file_headers['example'], true );
			$example_data               = ( null !== $json ) ? $json : array();
			$example_data['is_example'] = true;
			$data['example']            = array(
				'attributes' => array(
					'mode' => 'preview',
					'data' => $example_data,
				),
			);
		}

		// Support for "parent".
		if ( ! empty( $file_headers['parent'] ) ) {
			$data['parent'] = str_getcsv( $file_headers['parent'], ' ', '"' );
		}

		if (empty($data['name'])) {
			return false;
		} else {
			return $data;
		}
	}
}





