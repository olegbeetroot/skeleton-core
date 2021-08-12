<?php

namespace SkeletonTheme;

class Core {
    function __construct() {
        $fileStructure = new FileStructure();
        $syncFields = new SyncFields();
        $timberSetup = new TimberSetup();
        $fileStructure = new EnqueueScripts();
        $blocksResolver = new BlocksResolver();
        $dashboardCustomizer = new DashboardCustomizer();
    }
}
