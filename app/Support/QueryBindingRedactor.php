<?php

namespace App\Support;

class QueryBindingRedactor
{
    public const REDACTED = '[redacted]';

    private const ENCRYPTED_PAYLOAD_PREFIX = 'eyJpdiI6';

    private const COLUMN = '`?(\w+)`?\)?';

    public function __construct(private readonly array $sensitiveColumns) {}

    public function redact(string $sql, array $bindings): array
    {
        $columns = $this->columnsForPlaceholders($sql);
        $index   = 0;

        foreach ($bindings as $key => $value) {
            $column = is_string($key) ? ltrim($key, ':') : ($columns[$index++] ?? null);

            if ($this->shouldRedact($column, $value, isNamed: is_string($key))) {
                $bindings[$key] = self::REDACTED;
            }
        }

        return $bindings;
    }

    private function shouldRedact(?string $column, mixed $value, bool $isNamed): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value) && str_starts_with($value, self::ENCRYPTED_PAYLOAD_PREFIX)) {
            return true;
        }

        if ($column === null) {
            return ! $isNamed;
        }

        return in_array(strtolower($column), $this->sensitiveColumns, true);
    }

    private function columnsForPlaceholders(string $sql): array
    {
        $insert         = $this->insertValuesRegion($sql);
        $insertPosition = 0;

        return array_map(function (int $offset) use ($sql, $insert, &$insertPosition) {
            if ($insert && $offset >= $insert['start'] && $offset < $insert['end']) {
                return $insert['columns'][$insertPosition++ % count($insert['columns'])];
            }

            return $this->columnBefore(substr($sql, max(0, $offset - 200), min($offset, 200)));
        }, $this->placeholderOffsets($sql));
    }

    private function columnBefore(string $prefix): ?string
    {
        $patterns = [
            '/' . self::COLUMN . '\s*(?:=|<=>|!=|<>|<=|>=|<|>|\blike|\bnot\s+like)\s*$/i',
            '/' . self::COLUMN . '\s+(?:not\s+)?in\s*\((?:\s*\?\s*,)*\s*$/i',
            '/' . self::COLUMN . '\s+(?:not\s+)?between\s+(?:\?\s+and\s+)?$/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $prefix, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    private function insertValuesRegion(string $sql): ?array
    {
        if (! preg_match('/^\s*insert\b[^(]*\(([^()]*)\)\s*values\s*/i', $sql, $matches, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        preg_match_all('/`?(\w+)`?/', $matches[1][0], $columns);

        $start = $matches[0][1] + strlen($matches[0][0]);
        $end   = preg_match('/\bon\s+duplicate\s+key\s+update\b/i', $sql, $upsert, PREG_OFFSET_CAPTURE, $start)
            ? $upsert[0][1]
            : strlen($sql);

        return $columns[1] ? ['columns' => $columns[1], 'start' => $start, 'end' => $end] : null;
    }

    private function placeholderOffsets(string $sql): array
    {
        $offsets = [];
        $quote   = null;

        for ($i = 0, $length = strlen($sql); $i < $length; $i++) {
            $char = $sql[$i];

            if ($quote !== null) {
                if ($char === '\\') {
                    $i++;
                } elseif ($char === $quote) {
                    $quote = null;
                }

                continue;
            }

            if (in_array($char, ["'", '"', '`'], true)) {
                $quote = $char;
            } elseif ($char === '?') {
                $offsets[] = $i;
            }
        }

        return $offsets;
    }
}
