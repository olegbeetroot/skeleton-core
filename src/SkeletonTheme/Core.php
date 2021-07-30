<?php

namespace SkeletonTheme;

require('src/BlocksResolver.php');
require('src/TimberSetup.php');
require('src/DashboardCustomizer.php');
require('src/FileStructure.php');
require('src/EnqueueScripts.php');
require('src/Manifest.php');

class Core {
    function __construct() {
        $fileStructure = new FileStructure();
        $timberSetup = new TimberSetup();
        $fileStructure = new EnqueueScripts();
        $blocksResolver = new BlocksResolver();
        $dashboardCustomizer = new DashboardCustomizer();
    }
}