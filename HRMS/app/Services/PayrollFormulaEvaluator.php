<?php

namespace App\Services;

use RuntimeException;

/**
 * Evaluates a salary component's `formula` string (e.g. "BASIC*0.4+DA-500")
 * against a map of already-computed component amounts. Deliberately NOT an
 * eval()/create_function() wrapper — arithmetic only, no function calls, no
 * PHP execution. Every identifier must be a known component code in $values
 * or evaluation fails loudly rather than silently treating it as zero.
 */
class PayrollFormulaEvaluator
{
    /** @param array<string, float> $values component code (uppercase) => computed amount */
    public function evaluate(string $formula, array $values): float
    {
        $tokens = $this->tokenize($formula, array_keys($values));
        $rpn    = $this->toRpn($tokens);

        return $this->evalRpn($rpn, $values);
    }

    /** @return array<int, string> */
    private function tokenize(string $formula, array $knownCodes): array
    {
        $pattern = '/\s*(\d+\.?\d*|[A-Z_][A-Z0-9_]*|[+\-*\/()])\s*/';
        preg_match_all($pattern, strtoupper($formula), $matches);

        $tokens   = $matches[1];
        $consumed = implode('', $tokens);
        if (str_replace(' ', '', strtoupper($formula)) !== str_replace(' ', '', $consumed)) {
            throw new RuntimeException("Invalid character in formula: {$formula}");
        }

        foreach ($tokens as $token) {
            if (ctype_alpha($token[0]) || $token[0] === '_') {
                if (! in_array($token, $knownCodes, true)) {
                    throw new RuntimeException("Unknown component code '{$token}' in formula: {$formula}");
                }
            }
        }

        return $tokens;
    }

    /** Shunting-yard: infix tokens -> reverse Polish notation. */
    private function toRpn(array $tokens): array
    {
        $precedence = ['+' => 1, '-' => 1, '*' => 2, '/' => 2];
        $output     = [];
        $stack      = [];

        foreach ($tokens as $token) {
            if (is_numeric($token) || preg_match('/^[A-Z_]/', $token)) {
                $output[] = $token;
            } elseif ($token === '(') {
                $stack[] = $token;
            } elseif ($token === ')') {
                while ($stack !== [] && end($stack) !== '(') {
                    $output[] = array_pop($stack);
                }
                if (array_pop($stack) !== '(') {
                    throw new RuntimeException('Mismatched parentheses in formula.');
                }
            } else {
                while ($stack !== [] && end($stack) !== '(' && $precedence[end($stack)] >= $precedence[$token]) {
                    $output[] = array_pop($stack);
                }
                $stack[] = $token;
            }
        }

        while ($stack !== []) {
            $op = array_pop($stack);
            if ($op === '(') {
                throw new RuntimeException('Mismatched parentheses in formula.');
            }
            $output[] = $op;
        }

        return $output;
    }

    private function evalRpn(array $rpn, array $values): float
    {
        $stack = [];

        foreach ($rpn as $token) {
            if (is_numeric($token)) {
                $stack[] = (float) $token;
            } elseif (preg_match('/^[A-Z_]/', $token)) {
                $stack[] = (float) ($values[$token] ?? 0);
            } else {
                $b = array_pop($stack);
                $a = array_pop($stack);
                if ($a === null || $b === null) {
                    throw new RuntimeException('Malformed formula expression.');
                }
                $stack[] = match ($token) {
                    '+'     => $a + $b,
                    '-'     => $a - $b,
                    '*'     => $a * $b,
                    '/'     => $b == 0.0 ? 0.0 : $a / $b,
                    default => throw new RuntimeException("Unknown operator '{$token}'."),
                };
            }
        }

        if (count($stack) !== 1) {
            throw new RuntimeException('Malformed formula expression.');
        }

        return round($stack[0], 2);
    }
}
