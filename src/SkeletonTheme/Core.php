<?php

namespace SkeletonTheme;

class Core {
    function __construct() {
        $fileStructure = new FileStructure();
        $timberSetup = new TimberSetup();
        $fileStructure = new EnqueueScripts();
        $blocksResolver = new BlocksResolver();
        $dashboardCustomizer = new DashboardCustomizer();
    }
}