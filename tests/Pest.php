<?php

/**
 * Pest configuration for the suite.
 *
 * No WordPress and no database: WordPress functions are mocked per test with
 * Brain Monkey.
 */

declare(strict_types=1);

use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

/*
 * Brain Monkey around every test.
 *
 * setUp() defines WordPress' hook functions and arms Patchwork; tearDown()
 * verifies every expectation the test set and removes every mock, so nothing
 * leaks into the next test.
 *
 * The assertion count: Brain Monkey's expectations are Mockery expectations,
 * which PHPUnit does not count. Without adding them, a test whose only check is
 * `Functions\expect(...)->once()` reports as risky, and failOnRisky fails it.
 */
uses()
    ->beforeEach(function (): void {
        setUp();
    })
    ->afterEach(function (): void {
        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());

        tearDown();
    })
    ->in('Unit');

/**
 * Stand-ins for the WordPress functions the shortcode classes call.
 *
 * Escaping and translation functions return their input; the sanitizers and
 * helpers below do the minimum their real versions do for the inputs the
 * tests use. Call this from a test or a beforeEach() inside Unit.
 */
function stubWordPress(): void
{
    Brain\Monkey\Functions\stubEscapeFunctions();
    Brain\Monkey\Functions\stubTranslationFunctions();
    Brain\Monkey\Functions\stubs([
        'esc_url_raw' => fn ($url) => $url,
        'sanitize_html_class' => fn ($class) => preg_replace('/[^A-Za-z0-9_-]/', '', (string) $class),
        'sanitize_text_field' => fn ($text) => trim(strip_tags((string) $text)),
        'wp_kses_post' => fn ($content) => $content,
        'wpautop' => fn ($text) => '<p>'.$text.'</p>',
        'absint' => fn ($value) => abs((int) $value),
        'wp_unslash' => fn ($value) => $value,
        'do_shortcode' => fn ($content) => $content,
        'apply_filters' => fn ($tag, $value) => $value,
    ]);
}
