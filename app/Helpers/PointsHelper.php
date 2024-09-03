<?php

namespace App\Helpers;

class PointsHelper
{
    public static function calculate(string $score, string $prediction, bool $x2): int
    {
        $scoreEval = eval("return $score;");
        $predictionEval = eval("return $prediction;");

        $points = ($score === $prediction) ? 5
            : ($scoreEval === $predictionEval ? 3
                : ($scoreEval * $predictionEval > 0 ? 1 : 0));

        return $points * ($x2 + 1);
    }
}
