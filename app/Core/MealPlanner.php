<?php
declare(strict_types=1);
namespace Fitmeal\Core;

/**
 * Generates a simple, calorie-aware, home-style Indian meal plan.
 *
 * This is a rule-based planner (no external AI call): it picks items from a
 * small curated food database so that each day's meals land close to the
 * person's daily calorie target, while respecting their food preference.
 * It is meant as a practical starting point, not a clinical diet
 * prescription — the UI always carries that disclaimer alongside it.
 */
final class MealPlanner
{
    /** Share of the daily calorie target assigned to each meal slot. */
    private const SLOT_SHARE = [
        'breakfast' => 0.25,
        'lunch' => 0.35,
        'evening_snack' => 0.10,
        'dinner' => 0.30,
    ];

    /**
     * Each item: name, approx kcal for the stated portion, a one-line note,
     * and `suitable_for` — the food preferences it is appropriate for.
     * vegan ⊂ vegetarian ⊂ eggetarian ⊂ non_vegetarian in terms of who can eat it.
     */
    private const FOODS = [
        'breakfast' => [
            ['name' => 'Vegetable poha (light oil)', 'kcal' => 220, 'note' => 'Peanuts, peas, lemon', 'suitable_for' => ['vegan','vegetarian','eggetarian','non_vegetarian']],
            ['name' => 'Moong dal chilla (2) + mint chutney', 'kcal' => 240, 'note' => 'High protein, low oil', 'suitable_for' => ['vegan','vegetarian','eggetarian','non_vegetarian']],
            ['name' => 'Vegetable daliya (broken wheat)', 'kcal' => 230, 'note' => 'High fibre porridge', 'suitable_for' => ['vegan','vegetarian','eggetarian','non_vegetarian']],
            ['name' => 'Masala oats with vegetables', 'kcal' => 210, 'note' => 'Quick, fibre-rich', 'suitable_for' => ['vegan','vegetarian','eggetarian','non_vegetarian']],
            ['name' => 'Idli (3) with sambar', 'kcal' => 250, 'note' => 'Steamed, no oil', 'suitable_for' => ['vegan','vegetarian','eggetarian','non_vegetarian']],
            ['name' => 'Sprouts bhel (moong + chana, veggies, lemon)', 'kcal' => 200, 'note' => 'Raw, high fibre', 'suitable_for' => ['vegan','vegetarian','eggetarian','non_vegetarian']],
            ['name' => 'Besan-paneer chilla (2)', 'kcal' => 280, 'note' => 'Protein-rich', 'suitable_for' => ['vegetarian','eggetarian','non_vegetarian']],
            ['name' => 'Curd with fruit and chia seeds', 'kcal' => 230, 'note' => 'Probiotic, light', 'suitable_for' => ['vegetarian','eggetarian','non_vegetarian']],
            ['name' => 'Egg white bhurji (3) + multigrain toast', 'kcal' => 260, 'note' => 'High protein, low fat', 'suitable_for' => ['eggetarian','non_vegetarian']],
            ['name' => 'Boiled eggs (2) + brown bread', 'kcal' => 270, 'note' => 'High protein', 'suitable_for' => ['eggetarian','non_vegetarian']],
        ],
        'lunch' => [
            ['name' => 'Roti (2) + mixed veg sabzi + dal + salad', 'kcal' => 520, 'note' => 'Balanced thali, go light on ghee', 'suitable_for' => ['vegan','vegetarian','eggetarian','non_vegetarian']],
            ['name' => 'Brown rice + rajma + salad', 'kcal' => 540, 'note' => 'High fibre, high protein', 'suitable_for' => ['vegan','vegetarian','eggetarian','non_vegetarian']],
            ['name' => 'Roti (2) + chana masala + salad', 'kcal' => 530, 'note' => 'Protein-rich', 'suitable_for' => ['vegan','vegetarian','eggetarian','non_vegetarian']],
            ['name' => 'Sprouts and vegetable khichdi', 'kcal' => 480, 'note' => 'Light, one-pot', 'suitable_for' => ['vegan','vegetarian','eggetarian','non_vegetarian']],
            ['name' => 'Roti (2) + paneer bhurji + salad', 'kcal' => 560, 'note' => 'Protein-rich', 'suitable_for' => ['vegetarian','eggetarian','non_vegetarian']],
            ['name' => 'Curd rice + vegetable salad', 'kcal' => 480, 'note' => 'Light, cooling', 'suitable_for' => ['vegetarian','eggetarian','non_vegetarian']],
            ['name' => 'Moong dal khichdi + curd', 'kcal' => 500, 'note' => 'Easy to digest', 'suitable_for' => ['vegetarian','eggetarian','non_vegetarian']],
            ['name' => 'Egg curry (2 eggs) + roti (2) + salad', 'kcal' => 540, 'note' => 'High protein', 'suitable_for' => ['eggetarian','non_vegetarian']],
            ['name' => 'Grilled chicken breast + roti (1) + salad', 'kcal' => 500, 'note' => 'Lean protein', 'suitable_for' => ['non_vegetarian']],
            ['name' => 'Light fish curry + brown rice (small) + salad', 'kcal' => 510, 'note' => 'Omega-3, lean protein', 'suitable_for' => ['non_vegetarian']],
        ],
        'evening_snack' => [
            ['name' => 'Roasted chana (1 small bowl)', 'kcal' => 150, 'note' => 'High fibre, filling', 'suitable_for' => ['vegan','vegetarian','eggetarian','non_vegetarian']],
            ['name' => 'Seasonal fruit bowl', 'kcal' => 100, 'note' => 'Apple, papaya or guava', 'suitable_for' => ['vegan','vegetarian','eggetarian','non_vegetarian']],
            ['name' => 'Sprouts chaat', 'kcal' => 140, 'note' => 'Lemon, onion, tomato', 'suitable_for' => ['vegan','vegetarian','eggetarian','non_vegetarian']],
            ['name' => 'Handful of almonds and walnuts', 'kcal' => 160, 'note' => 'Healthy fats', 'suitable_for' => ['vegan','vegetarian','eggetarian','non_vegetarian']],
            ['name' => 'Green tea + roasted makhana', 'kcal' => 120, 'note' => 'Light, crunchy', 'suitable_for' => ['vegan','vegetarian','eggetarian','non_vegetarian']],
            ['name' => 'Buttermilk (chaas)', 'kcal' => 60, 'note' => 'Light, digestive', 'suitable_for' => ['vegetarian','eggetarian','non_vegetarian']],
            ['name' => 'Boiled egg (1)', 'kcal' => 78, 'note' => 'Quick protein', 'suitable_for' => ['eggetarian','non_vegetarian']],
        ],
        'dinner' => [
            ['name' => 'Roti (1-2) + palak dal + salad', 'kcal' => 400, 'note' => 'Light, iron-rich', 'suitable_for' => ['vegan','vegetarian','eggetarian','non_vegetarian']],
            ['name' => 'Moong dal soup + stir-fried vegetables', 'kcal' => 320, 'note' => 'Very light', 'suitable_for' => ['vegan','vegetarian','eggetarian','non_vegetarian']],
            ['name' => 'Light vegetable khichdi', 'kcal' => 350, 'note' => 'Easy on digestion', 'suitable_for' => ['vegan','vegetarian','eggetarian','non_vegetarian']],
            ['name' => 'Vegetable soup + grilled paneer salad', 'kcal' => 320, 'note' => 'High protein, light', 'suitable_for' => ['vegetarian','eggetarian','non_vegetarian']],
            ['name' => 'Stuffed vegetable roti (1) + curd', 'kcal' => 360, 'note' => 'Balanced', 'suitable_for' => ['vegetarian','eggetarian','non_vegetarian']],
            ['name' => 'Egg bhurji (2 eggs) + salad', 'kcal' => 300, 'note' => 'High protein, low carb', 'suitable_for' => ['eggetarian','non_vegetarian']],
            ['name' => 'Chicken soup/stew + salad', 'kcal' => 350, 'note' => 'Lean protein, light', 'suitable_for' => ['non_vegetarian']],
            ['name' => 'Grilled fish + steamed vegetables', 'kcal' => 380, 'note' => 'Omega-3, lean protein', 'suitable_for' => ['non_vegetarian']],
        ],
    ];

    /**
     * @return array{target_calories:int, days: list<array{day:int, total_calories:int, meals: array<string, array{name:string, kcal:int, note:string}>}>}
     */
    public static function generate(int $days, int $targetCalories, string $foodPreference): array
    {
        $foodPreference = in_array($foodPreference, ['vegan', 'vegetarian', 'eggetarian', 'non_vegetarian'], true)
            ? $foodPreference : 'vegetarian';

        $plan = [];
        for ($day = 1; $day <= $days; $day++) {
            $meals = [];
            $total = 0;
            foreach (self::SLOT_SHARE as $slot => $share) {
                $slotTarget = (int) round($targetCalories * $share);
                $eligible = array_values(array_filter(
                    self::FOODS[$slot],
                    fn (array $item) => in_array($foodPreference, $item['suitable_for'], true)
                ));
                if ($eligible === []) {
                    $eligible = self::FOODS[$slot]; // should not happen; vegetarian items always exist
                }
                usort($eligible, fn ($a, $b) => abs($a['kcal'] - $slotTarget) <=> abs($b['kcal'] - $slotTarget));
                $pool = array_slice($eligible, 0, min(4, count($eligible)));
                $chosen = $pool[($day - 1) % count($pool)];
                $meals[$slot] = $chosen;
                $total += $chosen['kcal'];
            }
            $plan[] = ['day' => $day, 'total_calories' => $total, 'meals' => $meals];
        }

        return ['target_calories' => $targetCalories, 'days' => $plan];
    }
}
