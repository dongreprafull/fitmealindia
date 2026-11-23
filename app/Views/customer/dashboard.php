<section class="page-head"><span class="eyebrow">CUSTOMER PORTAL</span><h1>Namaste, <?= Fitmeal\Core\Security::e($_SESSION['user']['name'] ?? 'Customer') ?></h1><p>Your FITMEAL INDIA account.</p></section>
<div class="grid two">
<?php if ($latestPlan): ?>
<article class="plain-card">
  <h3>🥗 Aapka fat-loss meal plan</h3>
  <p>Daily target: <strong><?= (int)$latestPlan['target_calories'] ?> kcal</strong> · Excess weight: <strong><?= $latestPlan['excess_weight_kg'] > 0 ? $latestPlan['excess_weight_kg'].' kg' : 'None' ?></strong></p>
  <a class="btn primary" href="/my-plan/<?= (int)$latestPlan['id'] ?>">Poora <?= (int)$latestPlan['plan_days'] ?>-day plan dekhein</a>
</article>
<?php else: ?>
<article class="plain-card"><h3>Your meal assessment</h3><p>Share your food preferences and routine — submit karte hi turant ek fat-loss meal plan milega.</p><a class="btn primary" href="/assessment">Complete assessment</a></article>
<?php endif; ?>
<article class="plain-card"><h3>Plans & pricing</h3><p>Explore available meal-planning packages.</p><a class="btn secondary" href="/plans">View plans</a></article>
</div>
<section class="section"><h2>Recent assessments</h2><?php if (empty($assessments)): ?><div class="notice">No assessments submitted yet.</div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Reference</th><th>Status</th><th>Screening</th><th>Submitted</th></tr></thead><tbody><?php foreach ($assessments as $a): ?><tr><td><?= Fitmeal\Core\Security::e($a['assessment_code']) ?></td><td><?= Fitmeal\Core\Security::e($a['status']) ?></td><td><?= Fitmeal\Core\Security::e($a['screening_status']) ?></td><td><?= Fitmeal\Core\Security::e($a['submitted_at'] ?? '') ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section><form method="post" action="/logout"><input type="hidden" name="_csrf" value="<?= Fitmeal\Core\Security::csrfToken() ?>"><button class="btn secondary">Logout</button></form>
