<?php

namespace App\Day03;

use Stringable;

class Number implements Stringable
{
    public int $id;
    public int $x1;
    public int $x2;
    public int $length;

    public function __construct(int $x, public int $y, public int $number)
    {
        $this->length = strlen((string)$number);
        $this->x1     = $x;
        $this->x2     = $x + $this->length - 1;
        $this->id     = spl_object_id($this);
    }

    public function __toString(): string
    {
        return $this->number . " @ $this->x1, $this->y";
    }
}
