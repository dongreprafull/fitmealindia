<?php
declare(strict_types=1);
namespace Fitmeal\Core;

/**
 * Body-metrics and fat-loss calculations.
 *
 * All formulas are standard, widely-published estimates (Mifflin-St Jeor for
 * BMR, WHO BMI bands for the "ideal weight" range). They are educational
 * screening numbers, not medical advice or a diagnosis.
 */
final class FatLoss
{
    /** Convert feet + inches to centimetres. */
    public static function feetInchesToCm(int $feet, float $inches): float
    {
        $totalInches = ($feet * 12) + $inches;
        return round($totalInches * 2.54, 1);
    }

    /** Convert centimetres to [feet, inches] (inches rounded to nearest whole). */
    public static function cmToFeetInches(float $cm): array
    {
        $totalInches = $cm / 2.54;
        $feet = (int) floor($totalInches / 12);
        $inches = (int) round($totalInches - ($feet * 12));
        if ($inches === 12) { $feet++; $inches = 0; }
        return [$feet, $inches];
    }

    public static function bmi(float $weightKg, float $heightCm): float
    {
        $heightM = $heightCm / 100;
        if ($heightM <= 0) return 0.0;
        return round($weightKg / ($heightM * $heightM), 1);
    }

    public static function bmiCategory(float $bmi): string
    {
        if ($bmi <= 0) return 'unknown';
        if ($bmi < 18.5) return 'underweight';
        if ($bmi < 25) return 'normal';
        if ($bmi < 30) return 'overweight';
        return 'obese';
    }

    /**
     * Ideal body weight (Devine formula), which — unlike the BMI band —
     * differs by sex, matching the usual "height-weight chart for men and
     * women". Height below 5 ft is clamped so the formula does not go
     * negative for shorter/child-range inputs.
     */
    public static function idealBodyWeight(float $heightCm, string $gender): float
    {
        $inchesOver5Ft = max(0.0, ($heightCm / 2.54) - 60);
        $base = $gender === 'female' ? 45.5 : 50.0;
        return round($base + (2.3 * $inchesOver5Ft), 1);
    }

    /**
     * Healthy weight range for a given height and sex: ±10% around the
     * Devine ideal body weight. Returns [minKg, maxKg].
     */
    public static function idealWeightRange(float $heightCm, string $gender): array
    {
        $ideal = self::idealBodyWeight($heightCm, $gender);
        return [round($ideal * 0.9, 1), round($ideal * 1.1, 1)];
    }

    /** How many kg above the healthy-range maximum (0 if already within/under it). */
    public static function excessWeight(float $weightKg, float $idealMaxKg): float
    {
        return $weightKg > $idealMaxKg ? round($weightKg - $idealMaxKg, 1) : 0.0;
    }

    /** Mifflin-St Jeor basal metabolic rate (kcal/day). */
    public static function bmr(float $weightKg, float $heightCm, int $age, string $gender): float
    {
        $base = (10 * $weightKg) + (6.25 * $heightCm) - (5 * $age);
        return $gender === 'female' ? $base - 161 : $base + 5;
    }

    public static function activityMultiplier(string $level): float
    {
        return match ($level) {
            'light' => 1.375,
            'moderate' => 1.55,
            'high' => 1.725,
            default => 1.2, // sedentary
        };
    }

    /** Total daily energy expenditure (kcal/day) at maintenance. */
    public static function tdee(float $weightKg, float $heightCm, int $age, string $gender, string $activityLevel): float
    {
        return self::bmr($weightKg, $heightCm, $age, $gender) * self::activityMultiplier($activityLevel);
    }

    /**
     * Daily calorie target for sustainable fat loss: roughly a 20% deficit
     * off maintenance (~0.4–0.6 kg/week for most people), never pushed below
     * a safe floor.
     */
    public static function targetCaloriesForFatLoss(float $tdee, string $gender): int
    {
        $floor = $gender === 'female' ? 1200 : 1500;
        $target = (int) round($tdee * 0.8);
        return max($floor, $target);
    }

    /** Rough estimate of weeks to close the gap to the top of the ideal range, at ~0.5kg/week. */
    public static function estimatedWeeksToIdeal(float $excessKg): int
    {
        if ($excessKg <= 0) return 0;
        return (int) max(1, ceil($excessKg / 0.5));
    }

    /**
     * Reference table used to draw the "height vs ideal weight" chart: for a
     * spread of heights, the Devine ideal body weight for the given sex.
     * Returns rows of [height_cm, ideal_kg].
     */
    public static function heightWeightTable(string $gender, int $minCm = 140, int $maxCm = 200, int $stepCm = 5): array
    {
        $rows = [];
        for ($h = $minCm; $h <= $maxCm; $h += $stepCm) {
            $rows[] = ['height_cm' => $h, 'ideal_kg' => self::idealBodyWeight((float) $h, $gender)];
        }
        return $rows;
    }

    /**
     * Server-rendered male-vs-female height/ideal-weight line chart (SVG
     * markup only, no <svg> wrapper) with an optional highlighted point for
     * the person's own height/weight. Mirrors the client-side chart drawn by
     * app.js, but works even before any JavaScript runs.
     */
    public static function heightWeightChartSvg(?float $heightCm = null, ?float $weightKg = null, string $gender = 'male', int $minCm = 135, int $maxCm = 200): string
    {
        $heights = [];
        for ($h = $minCm; $h <= $maxCm; $h += 5) $heights[] = $h;

        $male = array_map(fn($h) => self::idealBodyWeight((float) $h, 'male'), $heights);
        $female = array_map(fn($h) => self::idealBodyWeight((float) $h, 'female'), $heights);
        $all = array_merge($male, $female, $weightKg !== null ? [$weightKg] : []);
        $minW = min($all) - 5;
        $maxW = max($all) + 5;

        $padL = 42; $padR = 14; $padT = 14; $padB = 30;
        $w = 460; $h = 300;
        $plotW = $w - $padL - $padR; $plotH = $h - $padT - $padB;
        $x = fn($hgt) => $padL + (($hgt - $minCm) / ($maxCm - $minCm)) * $plotW;
        $y = fn($kg) => $padT + $plotH - ((($kg - $minW) / ($maxW - $minW)) * $plotH);

        $svg = '';
        for ($i = 0; $i <= 5; $i++) {
            $kg = $minW + (($maxW - $minW) / 5) * $i;
            $yy = round($y($kg), 1);
            $svg .= sprintf('<line x1="%d" x2="%d" y1="%s" y2="%s" stroke="#e2e9e3" stroke-width="1"/>', $padL, $w - $padR, $yy, $yy);
            $svg .= sprintf('<text x="%d" y="%s" text-anchor="end" font-size="10" fill="#64756d">%d</text>', $padL - 6, round($yy + 4, 1), round($kg));
        }
        foreach ([137, 152, 167, 183, 198] as $hgt) {
            $totalIn = (int) round($hgt / 2.54);
            $label = intdiv($totalIn, 12) . "'" . ($totalIn % 12) . '"';
            $svg .= sprintf('<text x="%s" y="%d" text-anchor="middle" font-size="10" fill="#64756d">%s</text>', round($x($hgt), 1), $h - $padB + 16, $label);
        }

        $path = fn(array $series) => implode(' ', array_map(
            fn($i, $kg) => ($i === 0 ? 'M' : 'L') . round($x($heights[$i]), 1) . ',' . round($y($kg), 1),
            array_keys($series), $series
        ));
        $svg .= sprintf('<path d="%s" fill="none" stroke="#2f6fb3" stroke-width="2.5"/>', $path($male));
        $svg .= sprintf('<path d="%s" fill="none" stroke="#c44d7e" stroke-width="2.5"/>', $path($female));

        if ($heightCm !== null && $weightKg !== null) {
            $svg .= sprintf('<circle cx="%s" cy="%s" r="6" fill="#176b4b" stroke="#fff" stroke-width="2"/>', round($x($heightCm), 1), round($y($weightKg), 1));
            $idealY = round($y(self::idealBodyWeight($heightCm, $gender)), 1);
            $svg .= sprintf('<circle cx="%s" cy="%s" r="4" fill="none" stroke="#176b4b" stroke-width="2" stroke-dasharray="2,2"/>', round($x($heightCm), 1), $idealY);
        }
        return $svg;
    }
}
