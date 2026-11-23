<section class="page-head"><span class="eyebrow">ASSESSMENT</span><h1><?= Fitmeal\Core\Security::e($assessment['assessment_code']) ?></h1><p><?= Fitmeal\Core\Security::e($assessment['name']) ?> · <?= Fitmeal\Core\Security::e($assessment['email']) ?><?= $assessment['mobile'] ? ' · '.Fitmeal\Core\Security::e($assessment['mobile']) : '' ?></p><a class="btn secondary" href="/admin">Back to dashboard</a></section>

<div class="grid two">
<article class="plain-card">
  <h3>Assessment answers</h3>
  <table><tbody>
  <?php foreach ($assessment['answers'] as $k=>$v): ?>
    <tr><th style="text-align:left;padding-right:12px"><?= Fitmeal\Core\Security::e((string)$k) ?></th><td><?= Fitmeal\Core\Security::e(is_bool($v)?($v?'yes':'no'):(string)$v) ?></td></tr>
  <?php endforeach; ?>
  </tbody></table>
</article>

<article class="plain-card">
  <h3>Update status</h3>
  <form method="post" action="/admin/assessments/<?= (int)$assessment['id'] ?>/status">
    <input type="hidden" name="_csrf" value="<?= Fitmeal\Core\Security::csrfToken() ?>">
    <label>Status<select name="status">
      <?php foreach (['draft','submitted','reviewed','closed'] as $opt): ?>
        <option value="<?= $opt ?>" <?= $assessment['status']===$opt?'selected':'' ?>><?= $opt ?></option>
      <?php endforeach; ?>
    </select></label>
    <label>Screening<select name="screening_status">
      <?php foreach (['pending','eligible','professional_review','referred','rejected'] as $opt): ?>
        <option value="<?= $opt ?>" <?= $assessment['screening_status']===$opt?'selected':'' ?>><?= $opt ?></option>
      <?php endforeach; ?>
    </select></label>
    <button class="btn primary" type="submit">Save</button>
  </form>
</article>
</div>

<section class="section">
<h2>Generated meal plan</h2>
<?php if (!$plan): ?>
  <div class="notice">No plan generated for this assessment yet.</div>
<?php else: ?>
  <p>Target calories: <strong><?= (int)$plan['target_calories'] ?> kcal/day</strong> · BMI <?= Fitmeal\Core\Security::e((string)$plan['bmi']) ?> · Excess <?= $plan['excess_weight_kg'] ?> kg</p>
  <div class="table-wrap plan-table">
    <table>
      <thead><tr><th>Day</th><th>Breakfast</th><th>Lunch</th><th>Evening snack</th><th>Dinner</th><th>Total kcal</th></tr></thead>
      <tbody>
      <?php foreach ($plan['plan_json']['days'] as $day): ?>
        <tr>
          <td>Day <?= $day['day'] ?></td>
          <?php foreach (['breakfast','lunch','evening_snack','dinner'] as $slot): $m = $day['meals'][$slot]; ?>
            <td><strong><?= Fitmeal\Core\Security::e($m['name']) ?></strong><br><small><?= $m['kcal'] ?> kcal</small></td>
          <?php endforeach; ?>
          <td><?= $day['total_calories'] ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
</section>
