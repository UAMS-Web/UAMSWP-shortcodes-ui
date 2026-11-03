<?php

declare(strict_types=1);

/**
 * Test bootstrap.
 *
 * The suite runs with no WordPress: WordPress functions are mocked per test
 * with Brain Monkey.
 */

require_once __DIR__ . '/../vendor/autoload.php';

/*
 * Patchwork first: it can only redefine functions declared in files loaded
 * after it.
 */
require_once __DIR__ . '/../vendor/antecedent/patchwork/Patchwork.php';

/*
 * Define the class under test only. The main plugin file is not loaded: it
 * registers hooks at file scope, and Brain Monkey's add_action() exists only
 * inside a test.
 */
require_once __DIR__ . '/../inc/class-uams-shortcodes.php';
