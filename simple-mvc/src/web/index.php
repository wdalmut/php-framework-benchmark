<?php

if (isset($_GET['debug'])) {
    xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);

    define('START_TIME', microtime(true));
    define('START_MEMORY_USAGE', memory_get_usage());
}

require_once __DIR__ . '/../../library/Loader.php';

Loader::classmap();

$app = new Application();
$app->setControllerPath(__DIR__ . '/../app/controllers');

$app->bootstrap("view", function(){
    $view = new View();
    $view->setViewPath(__DIR__ . '/../app/views');

    return $view;
});

$app->bootstrap("layout", function(){
    $layout = new Layout();
    $layout->setViewPath(__DIR__ . '/../app/layouts');

    return $layout;
});

$app->run();

$xhprof_data = xhprof_disable();

if (!isset($_GET['debug'])) {
    die();
}

echo "Page rendered in <b>"
    . round((microtime(true) - START_TIME), 5) * 1000 ." ms</b>, taking <b>"
    . round((memory_get_usage() - START_MEMORY_USAGE) / 1024, 2) ." KB</b>";
$f = get_included_files();
echo ", include files: ".count($f);

$XHPROF_ROOT = realpath(dirname(__FILE__) .'/../../..');
include_once $XHPROF_ROOT . "/xhprof/xhprof_lib/utils/xhprof_lib.php";
include_once $XHPROF_ROOT . "/xhprof/xhprof_lib/utils/xhprof_runs.php";

// save raw data for this profiler run using default
// implementation of iXHProfRuns.
$xhprof_runs = new XHProfRuns_Default();

// save the run under a namespace "xhprof_foo"
$run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_foo");

echo ", xhprof <a href=\"http://xhprof.pfb.example.com/xhprof_html/index.php?run=$run_id&source=xhprof_foo\">url</a>";
