<?php

/**
 * This file is part of Dixlase Menus.
 *
 * Copyright (C) 2026 exc-D inc.
 * https://exc-d.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace Plugins\DixlaseMenus\App\Services\MenuLinkSources;

use App\Contracts\PluginIntegration\LinkableProviderInterface;
use App\DTO\PluginIntegration\LinkableDTO;
use Plugins\DixlaseMenus\App\Contracts\MenuLinkSource;

/**
 * LinkableProviderInterface → MenuLinkSource ブリッジアダプター
 *
 * コアの LinkableProviderInterface を DixlaseMenus の
 * MenuLinkSource 契約にラップするアダプタークラス
 */
class LinkableProviderAdapter implements MenuLinkSource
{
    public function __construct(
        private LinkableProviderInterface $provider,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function getSourceType(): string
    {
        return $this->provider->getProviderKey();
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceLabel(): string
    {
        return $this->provider->getProviderLabel();
    }

    /**
     * {@inheritDoc}
     */
    public function getAvailableItems(): array
    {
        return array_map(
            fn (LinkableDTO $dto) => $this->dtoToArray($dto),
            $this->provider->getAvailableItems()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getItemById(int|string $itemId): ?array
    {
        $dto = $this->provider->getItemById((string) $itemId);

        return $dto ? $this->dtoToArray($dto) : null;
    }

    /**
     * {@inheritDoc}
     */
    public function isAvailable(): bool
    {
        return $this->provider->isAvailable();
    }

    /**
     * {@inheritDoc}
     */
    public function searchItems(string $query, int $limit = 20): array
    {
        return array_map(
            fn (LinkableDTO $dto) => $this->dtoToArray($dto),
            $this->provider->searchItems($query, $limit)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function generateUrl(int|string $itemId): ?string
    {
        $dto = $this->provider->getItemById((string) $itemId);

        return $dto?->url;
    }

    /**
     * {@inheritDoc}
     */
    public function isPublished(int|string $itemId): bool
    {
        return $this->provider->getItemById((string) $itemId) !== null;
    }

    /**
     * プロバイダーのアイコンクラスを取得
     */
    public function getIcon(): ?string
    {
        return $this->provider->getProviderIcon();
    }

    /**
     * LinkableDTO を配列形式に変換
     *
     * @return array{id: string, title: string, url: string, description?: string}
     */
    private function dtoToArray(LinkableDTO $dto): array
    {
        return [
            'id' => $dto->id,
            'title' => $dto->title,
            'url' => $dto->url,
        ];
    }
}
