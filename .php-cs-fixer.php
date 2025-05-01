<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->name("*.php")
    ->exclude("vendor");

return (new PhpCsFixer\Config())
    ->setRules([
        "@PER-CS2.0" => true,
        "line_ending" => false,
        "single_quote" => false,
        "trailing_comma_in_multiline" => [
            "elements" => ["arrays"],
        ],
    ])
    ->setFinder($finder);
