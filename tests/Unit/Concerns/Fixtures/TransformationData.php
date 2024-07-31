<?php

declare(strict_types=1);

namespace Honeystone\DtoTools\Tests\Unit\Concerns\Fixtures;

use Honeystone\DtoTools\Concerns\HasTransferableData;
use Honeystone\DtoTools\Contracts\Transferable;

final readonly class TransformationData implements Transferable
{
    use HasTransferableData;

    public function __construct(
        public string $value = '',
        public self|array|null $data = null,
    ) {
    }

    /**
     * @param array<string, string> $parameters
     *
     * @return array<string, string>
     */
    protected static function transformIncoming(array $parameters): array
    {
        return [
            'value' => strtoupper($parameters['value'] ?? ''),
            'data' => $parameters['data'] ?? null,
        ];
    }

    /**
     * @param array<string, string> $parameters
     *
     * @return array<string, string>
     */
    protected function transformOutgoing(array $parameters): array
    {
        return [
            'transformed' => $parameters['value'],
            'data' => $parameters['data'],
        ];
    }
}
