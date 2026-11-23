<section class="page-head"><span class="eyebrow">FREE BODY CHECK</span><h1>Check karein aapka extra fat/weight kitna hai</h1><p>Height (feet/inches) aur weight daalein — BMI, healthy weight range aur aapka excess weight turant dekhein, saath mein height ke hisaab se male/female weight chart.</p></section>

<div class="grid two bmi-layout">
  <form class="form-card" id="bmi-form">
    <label>Gender
      <select name="gender" id="bmi-gender">
        <option value="male">Male</option>
        <option value="female">Female</option>
      </select>
    </label>
    <div class="form-grid">
      <label>Height – feet<input type="number" name="feet" id="bmi-feet" min="3" max="7" value="5" required></label>
      <label>Height – inches<input type="number" name="inches" id="bmi-inches" min="0" max="11" value="6" required></label>
    </div>
    <label>Weight (kg)<input type="number" name="weight" id="bmi-weight" min="25" max="300" step="0.1" required></label>
    <button class="btn primary full" type="submit">Calculate</button>
    <div id="bmi-result" aria-live="polite"></div>
  </form>

  <div class="plain-card chart-card">
    <h3>Height ke hisaab se healthy weight (Male vs Female)</h3>
    <svg id="weight-chart" viewBox="0 0 460 300" role="img" aria-label="Height vs ideal weight chart for male and female"></svg>
    <div class="chart-legend">
      <span><i class="dot male"></i> Male ideal weight</span>
      <span><i class="dot female"></i> Female ideal weight</span>
      <span><i class="dot you"></i> Aap</span>
    </div>
  </div>
</div>

<p class="disclaimer">BMI aur ideal-weight estimates sirf screening/educational purpose ke liye hain. Yeh diagnosis nahi hai aur body composition, muscle mass, umar ya health conditions ko account nahi karte. Health concerns ke liye qualified professional se consult karein.</p>
