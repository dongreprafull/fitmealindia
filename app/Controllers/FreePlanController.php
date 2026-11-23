<?php
declare(strict_types=1);
namespace Fitmeal\Controllers;

use Fitmeal\Core\{DB, FatLoss, MealPlanner, Security, View};

/**
 * Public, no-login "free 3-day plan" lead flow: visitor enters basic body
 * metrics, gets an instant 3-day Indian fat-loss meal plan, and is invited
 * to register for a full custom plan.
 */
final class FreePlanController
{
    public function show(): void
    {
        $prefill = [
            'height_cm' => isset($_GET['height_cm']) ? (float) $_GET['height_cm'] : null,
            'weight' => isset($_GET['weight']) ? (float) $_GET['weight'] : null,
            'gender' => $_GET['gender'] ?? null,
        ];
        View::render('public/free-plan', ['title' => 'Free 3-Day Fat Loss Plan', 'prefill' => $prefill, 'result' => null]);
    }

    public function generate(): void
    {
        Security::verifyCsrf();

        $feet = (int) ($_POST['height_feet'] ?? 0);
        $inches = (float) ($_POST['height_inches'] ?? 0);
        $heightCm = FatLoss::feetInchesToCm($feet, $inches);
        $weight = (float) ($_POST['weight_kg'] ?? 0);
        $age = (int) ($_POST['age'] ?? 0);
        $gender = ($_POST['gender'] ?? '') === 'female' ? 'female' : 'male';
        $activity = $_POST['activity_level'] ?? 'sedentary';
        $foodPreference = $_POST['food_preference'] ?? 'vegetarian';
        $name = trim($_POST['name'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');

        $errors = [];
        if ($heightCm < 120 || $heightCm > 215) $errors[] = 'Please enter a realistic height.';
        if ($weight < 25 || $weight > 300) $errors[] = 'Please enter a realistic weight (25–300 kg).';
        if ($age < 18 || $age > 100) $errors[] = 'This free plan is for adults aged 18 and above.';
        if (!in_array($foodPreference, ['vegan', 'vegetarian', 'eggetarian', 'non_vegetarian'], true)) $errors[] = 'Please choose a valid food preference.';

        if ($errors !== []) {
            View::render('public/free-plan', [
                'title' => 'Free 3-Day Fat Loss Plan',
                'prefill' => ['height_cm' => $heightCm, 'weight' => $weight, 'gender' => $gender],
                'result' => null,
                'errors' => $errors,
            ]);
            return;
        }

        $bmi = FatLoss::bmi($weight, $heightCm);
        [$idealMin, $idealMax] = FatLoss::idealWeightRange($heightCm, $gender);
        $excess = FatLoss::excessWeight($weight, $idealMax);
        $bmr = FatLoss::bmr($weight, $heightCm, $age, $gender);
        $tdee = FatLoss::tdee($weight, $heightCm, $age, $gender, $activity);
        $targetCalories = FatLoss::targetCaloriesForFatLoss($tdee, $gender);
        $plan = MealPlanner::generate(3, $targetCalories, $foodPreference);

        try {
            $pdo = DB::connection();
            $stmt = $pdo->prepare(
                'INSERT INTO generated_plans
                    (source, name, mobile, gender, age, height_cm, weight_kg, activity_level, food_preference, goal,
                     bmi, ideal_weight_min, ideal_weight_max, excess_weight_kg, bmr, tdee, target_calories, plan_days, plan_json)
                 VALUES ("lead", ?, ?, ?, ?, ?, ?, ?, ?, "fat_loss", ?, ?, ?, ?, ?, ?, ?, 3, ?)'
            );
            $stmt->execute([
                $name !== '' ? $name : null,
                $mobile !== '' ? $mobile : null,
                $gender, $age, $heightCm, $weight, $activity, $foodPreference,
                $bmi, $idealMin, $idealMax, $excess, (int) round($bmr), (int) round($tdee), $targetCalories,
                json_encode($plan, JSON_THROW_ON_ERROR),
            ]);
        } catch (\Throwable $e) {
            // Lead capture is best-effort; never block the visitor from seeing their plan.
        }

        View::render('public/free-plan', [
            'title' => 'Your Free 3-Day Fat Loss Plan',
            'prefill' => ['height_cm' => $heightCm, 'weight' => $weight, 'gender' => $gender],
            'result' => [
                'bmi' => $bmi,
                'bmi_category' => FatLoss::bmiCategory($bmi),
                'ideal_min' => $idealMin,
                'ideal_max' => $idealMax,
                'excess' => $excess,
                'weeks_to_ideal' => FatLoss::estimatedWeeksToIdeal($excess),
                'bmr' => (int) round($bmr),
                'tdee' => (int) round($tdee),
                'target_calories' => $targetCalories,
                'plan' => $plan,
                'name' => $name,
            ],
        ]);
    }
}
