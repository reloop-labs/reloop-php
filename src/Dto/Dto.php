<?php

namespace Reloop\Dto;

final class Dto
{
    public static function body(object|array $params): array
    {
        if (is_array($params)) {
            return $params;
        }

        return $params->toArray();
    }

    public static function query(object|array $params): array
    {
        if (is_array($params)) {
            return $params;
        }

        return $params->toArray();
    }
}
