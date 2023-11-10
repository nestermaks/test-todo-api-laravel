<?php

namespace App\Enums;

use Str;

enum Status: int
{
    case Todo = 0;
    case Done = 1;

    public static function fromName(string $name): self
    {
        foreach (self::cases() as $status) {
            if (Str::lower($name) === Str::lower($status->name)) {
                return $status;
            }
        }

        throw new \ValueError("there is no such status as '$name'");
    }

    public function label(): string
    {
        return Str::lower($this->name);
    }
}
