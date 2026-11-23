<section class="page-head"><span class="eyebrow">YOUR MEAL PLAN</span><h1><?= (int)$plan['plan_days'] ?>-Day Fat Loss Meal Plan</h1><p>Daily calorie target: <strong><?= (int)$plan['target_calories'] ?> kcal</strong> · Based on BMI <?= Fitmeal\Core\Security::e((string)$plan['bmi']) ?>, healthy range <?= $plan['ideal_weight_min'] ?>–<?= $plan['ideal_weight_max'] ?> kg<?= $plan['excess_weight_kg'] > 0 ? ', excess ~'.$plan['excess_weight_kg'].' kg' : '' ?>.</p></section>

<div class="table-wrap plan-table">
  <table>
    <thead><tr><th>Day</th><th>Breakfast</th><th>Lunch</th><th>Evening snack</th><th>Dinner</th><th>Total kcal</th></tr></thead>
    <tbody>
    <?php foreach ($plan['plan_json']['days'] as $day): ?>
      <tr>
        <td>Day <?= $day['day'] ?></td>
        <?php foreach (['breakfast','lunch','evening_snack','dinner'] as $slot): $m = $day['meals'][$slot]; ?>
          <td><strong><?= Fitmeal\Core\Security::e($m['name']) ?></strong><br><small><?= Fitmeal\Core\Security::e($m['note']) ?> · <?= $m['kcal'] ?> kcal</small></td>
        <?php endforeach; ?>
        <td><?= $day['total_calories'] ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<p class="disclaimer">Yeh plan ek rule-based calorie estimate par bana hai, medical/clinical diet prescription nahi hai. Allergies, medical conditions ya pregnancy ke case mein qualified professional se review karwayein.</p>
<a class="btn secondary" href="/dashboard">Back to dashboard</a>
