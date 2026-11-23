<?php $p = $prefill ?? []; $r = $result ?? null; ?>
<section class="page-head"><span class="eyebrow">FREE · NO SIGN-UP NEEDED</span><h1>Apna free 3-day fat loss plan paayein</h1><p>Apni height, weight aur food preference batayein — turant ek 3-din ka Indian meal plan paayein jo aapke calorie target ke hisaab se bana ho.</p></section>

<?php if (!empty($errors)): ?><div class="alert error"><?php foreach ($errors as $err): ?><?= Fitmeal\Core\Security::e($err) ?><br><?php endforeach; ?></div><?php endif; ?>

<div class="grid two free-plan-layout">
<form class="form-card" method="post" action="/free-plan">
  <input type="hidden" name="_csrf" value="<?= Fitmeal\Core\Security::csrfToken() ?>">
  <label>Naam (optional)<input name="name" maxlength="150" value="<?= Fitmeal\Core\Security::e($r['name'] ?? '') ?>"></label>
  <label>Mobile (optional, plan WhatsApp pe chahiye to)<input name="mobile" maxlength="20"></label>
  <label>Gender
    <select name="gender" required>
      <option value="male" <?= ($p['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
      <option value="female" <?= ($p['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
    </select>
  </label>
  <label>Age (18–100)<input type="number" name="age" min="18" max="100" required></label>
  <div class="form-grid">
    <label>Height – feet<input type="number" name="height_feet" min="3" max="7" value="<?php $fi = !empty($p['height_cm']) ? Fitmeal\Core\FatLoss::cmToFeetInches((float)$p['height_cm']) : [5,6]; echo $fi[0]; ?>" required></label>
    <label>Height – inches<input type="number" name="height_inches" min="0" max="11" value="<?= $fi[1] ?? 6 ?>" required></label>
  </div>
  <label>Weight (kg)<input type="number" name="weight_kg" min="25" max="300" step="0.1" value="<?= Fitmeal\Core\Security::e(isset($p['weight']) ? (string)$p['weight'] : '') ?>" required></label>
  <label>Food preference
    <select name="food_preference" required>
      <option value="vegetarian">Vegetarian</option>
      <option value="vegan">Vegan</option>
      <option value="eggetarian">Eggetarian</option>
      <option value="non_vegetarian">Non-vegetarian</option>
    </select>
  </label>
  <label>Activity level
    <select name="activity_level">
      <option value="sedentary">Mostly sitting</option>
      <option value="light">Light activity</option>
      <option value="moderate">Moderately active</option>
      <option value="high">Highly active</option>
    </select>
  </label>
  <button class="btn primary full" type="submit">Mera free 3-day plan dikhayein</button>
  <p class="micro">Adults ke liye. Yeh general meal-planning information hai, medical advice nahi.</p>
</form>

<div class="plain-card">
  <h3>Yeh plan aapko kya dega?</h3>
  <ul>
    <li>Aapki height/weight ke hisaab se healthy weight range</li>
    <li>Kitna weight/fat excess hai, uska andaza</li>
    <li>3 din ka breakfast, lunch, evening snack, dinner</li>
    <li>Aapke food preference (veg/egg/non-veg/vegan) ke hisaab se</li>
  </ul>
  <p class="form-note" style="text-align:left">Poora 30/90-day personalized plan aur professional review ke liye <a href="/register">free account banayein</a>.</p>
</div>
</div>

<?php if ($r): ?>
<section class="section">
  <div class="section-heading"><span class="eyebrow">YOUR RESULT</span><h2>Aapka body check aur 3-day plan</h2></div>
  <div class="grid four stats">
    <article><span>BMI</span><strong><?= Fitmeal\Core\Security::e((string)$r['bmi']) ?></strong></article>
    <article><span>Healthy weight range</span><strong><?= $r['ideal_min'] ?>–<?= $r['ideal_max'] ?> kg</strong></article>
    <article><span>Extra weight</span><strong><?= $r['excess'] > 0 ? $r['excess'].' kg' : 'None 🎉' ?></strong></article>
    <article><span>Daily calorie target</span><strong><?= $r['target_calories'] ?> kcal</strong></article>
  </div>
  <?php if ($r['excess'] > 0): ?>
    <div class="notice">Aapka weight healthy range se karib <strong><?= $r['excess'] ?> kg</strong> zyada hai. Safe pace (~0.5 kg/week) se, healthy range tak pahunchne mein lagbhag <strong><?= $r['weeks_to_ideal'] ?> weeks</strong> lag sakte hain — niche diya gaya meal plan isi calorie target (<?= $r['target_calories'] ?> kcal/din) par based hai.</div>
  <?php else: ?>
    <div class="notice">Aap already healthy weight range mein hain. Yeh plan aapko maintain karne mein madad karega.</div>
  <?php endif; ?>

  <div class="table-wrap plan-table">
    <table>
      <thead><tr><th>Day</th><th>Breakfast</th><th>Lunch</th><th>Evening snack</th><th>Dinner</th><th>Total kcal</th></tr></thead>
      <tbody>
      <?php foreach ($r['plan']['days'] as $day): ?>
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
  <section class="cta-band"><div><h2>Poora custom plan chahiye?</h2><p>Free account banayein, apna assessment bharein — hum turant ek personalized fat-loss meal plan dashboard mein de denge.</p></div><a class="btn light" href="/register">Shuru karein</a></section>
</section>
<?php endif; ?>
