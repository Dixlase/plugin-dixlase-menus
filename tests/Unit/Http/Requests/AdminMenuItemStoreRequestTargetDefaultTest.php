<?php

/**
 * This file is part of the Dixlase Menus plugin.
 *
 * Copyright (C) 2026 exc-D inc. and Dixlase contributors
 * https://exc-d.com
 */

namespace Plugins\DixlaseMenus\Tests\Unit\Http\Requests;

use PHPUnit\Framework\Attributes\DataProvider;
use Plugins\DixlaseMenus\App\Http\Requests\AdminMenuItemStoreRequest;
use ReflectionMethod;
use Tests\TestCase;

/**
 * The target `<select>` on the item create screen has an empty "default"
 * option. Before this guard the request only defaulted to `_self` when the
 * key was MISSING, so once core restores `ConvertEmptyStringsToNull` the
 * empty option arrives as `null`, passes the `nullable` rule, and the save
 * fails against `menu_items.target NOT NULL`. prepareForValidation() now
 * folds the three "no choice made" shapes (missing / null / '') into the
 * schema default so the save keeps working whether or not core trims empty
 * inputs upstream.
 */
class AdminMenuItemStoreRequestTargetDefaultTest extends TestCase
{
    /**
     * @return list<array{0: array<string, mixed>}>
     */
    public static function noChoiceInputs(): array
    {
        return [
            'target missing' => [[]],
            'target null' => [['target' => null]],
            'target empty string' => [['target' => '']],
        ];
    }

    #[DataProvider('noChoiceInputs')]
    public function test_target_defaults_to_self_when_no_choice_made(array $input): void
    {
        $request = new AdminMenuItemStoreRequest();
        $request->replace($input);

        $method = new ReflectionMethod($request, 'prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $this->assertSame('_self', $request->input('target'));
    }

    public function test_target_is_preserved_when_filled(): void
    {
        $request = new AdminMenuItemStoreRequest();
        $request->replace(['target' => '_blank']);

        $method = new ReflectionMethod($request, 'prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $this->assertSame('_blank', $request->input('target'));
    }
}
