document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('bmi-form');
  if (!form) return;

  const chartSvg = document.getElementById('weight-chart');
  const NS = 'http://www.w3.org/2000/svg';

  function idealWeightKg(heightCm, gender) {
    const inchesOver5 = Math.max(0, (heightCm / 2.54) - 60);
    const base = gender === 'female' ? 45.5 : 50;
    return +(base + 2.3 * inchesOver5).toFixed(1);
  }

  function bmiCategory(bmi) {
    if (bmi < 18.5) return 'Underweight';
    if (bmi < 25) return 'Normal';
    if (bmi < 30) return 'Overweight';
    return 'Obese';
  }

  function drawChart(heightCm, weightKg, gender) {
    if (!chartSvg) return;
    chartSvg.innerHTML = '';

    const minH = 135, maxH = 200; // cm, roughly 4'5" to 6'7"
    const heights = [];
    for (let h = minH; h <= maxH; h += 5) heights.push(h);

    const maleSeries = heights.map(h => idealWeightKg(h, 'male'));
    const femaleSeries = heights.map(h => idealWeightKg(h, 'female'));
    const allWeights = maleSeries.concat(femaleSeries, [weightKg]);
    const minW = Math.min(...allWeights) - 5;
    const maxW = Math.max(...allWeights) + 5;

    const padL = 42, padR = 14, padT = 14, padB = 30;
    const w = 460, h = 300;
    const plotW = w - padL - padR, plotH = h - padT - padB;

    const x = (hgt) => padL + ((hgt - minH) / (maxH - minH)) * plotW;
    const y = (kg) => padT + plotH - (((kg - minW) / (maxW - minW)) * plotH);

    function axis() {
      const g = document.createElementNS(NS, 'g');
      // Y axis gridlines + labels
      const steps = 5;
      for (let i = 0; i <= steps; i++) {
        const kg = minW + ((maxW - minW) / steps) * i;
        const line = document.createElementNS(NS, 'line');
        line.setAttribute('x1', padL); line.setAttribute('x2', w - padR);
        line.setAttribute('y1', y(kg)); line.setAttribute('y2', y(kg));
        line.setAttribute('stroke', '#e2e9e3'); line.setAttribute('stroke-width', '1');
        g.appendChild(line);
        const label = document.createElementNS(NS, 'text');
        label.setAttribute('x', padL - 6); label.setAttribute('y', y(kg) + 4);
        label.setAttribute('text-anchor', 'end'); label.setAttribute('font-size', '10'); label.setAttribute('fill', '#64756d');
        label.textContent = Math.round(kg);
        g.appendChild(label);
      }
      // X axis labels (feet)
      [137, 152, 167, 183, 198].forEach((hgt) => {
        const label = document.createElementNS(NS, 'text');
        label.setAttribute('x', x(hgt)); label.setAttribute('y', h - padB + 16);
        label.setAttribute('text-anchor', 'middle'); label.setAttribute('font-size', '10'); label.setAttribute('fill', '#64756d');
        const totalIn = Math.round(hgt / 2.54);
        label.textContent = Math.floor(totalIn / 12) + "'" + (totalIn % 12) + '"';
        g.appendChild(label);
      });
      return g;
    }
    chartSvg.appendChild(axis());

    function linePath(series, color) {
      const d = series.map((kg, i) => (i === 0 ? 'M' : 'L') + x(heights[i]).toFixed(1) + ',' + y(kg).toFixed(1)).join(' ');
      const path = document.createElementNS(NS, 'path');
      path.setAttribute('d', d);
      path.setAttribute('fill', 'none');
      path.setAttribute('stroke', color);
      path.setAttribute('stroke-width', '2.5');
      return path;
    }
    chartSvg.appendChild(linePath(maleSeries, '#2f6fb3'));
    chartSvg.appendChild(linePath(femaleSeries, '#c44d7e'));

    // User's point
    const dot = document.createElementNS(NS, 'circle');
    dot.setAttribute('cx', x(heightCm).toFixed(1));
    dot.setAttribute('cy', y(weightKg).toFixed(1));
    dot.setAttribute('r', '6');
    dot.setAttribute('fill', '#176b4b');
    dot.setAttribute('stroke', '#fff');
    dot.setAttribute('stroke-width', '2');
    chartSvg.appendChild(dot);

    // User's ideal point (same height, ideal weight for their gender)
    const idealDot = document.createElementNS(NS, 'circle');
    idealDot.setAttribute('cx', x(heightCm).toFixed(1));
    idealDot.setAttribute('cy', y(idealWeightKg(heightCm, gender)).toFixed(1));
    idealDot.setAttribute('r', '4');
    idealDot.setAttribute('fill', 'none');
    idealDot.setAttribute('stroke', '#176b4b');
    idealDot.setAttribute('stroke-width', '2');
    idealDot.setAttribute('stroke-dasharray', '2,2');
    chartSvg.appendChild(idealDot);
  }

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const feet = Number(form.feet.value), inches = Number(form.inches.value);
    const heightCm = ((feet * 12) + inches) * 2.54;
    const weight = Number(form.weight.value);
    const gender = form.gender.value;
    const out = document.getElementById('bmi-result');
    if (!heightCm || !weight) { out.textContent = 'Please enter valid measurements.'; return; }

    const heightM = heightCm / 100;
    const bmi = weight / (heightM * heightM);
    const ideal = idealWeightKg(heightCm, gender);
    const idealMin = +(ideal * 0.9).toFixed(1), idealMax = +(ideal * 1.1).toFixed(1);
    const excess = weight > idealMax ? +(weight - idealMax).toFixed(1) : 0;
    const weeks = excess > 0 ? Math.max(1, Math.ceil(excess / 0.5)) : 0;

    let excessHtml;
    if (excess > 0) {
      excessHtml = `<p>Aapka weight healthy range se karib <strong>${excess} kg</strong> zyada hai (~${weeks} weeks safe pace par healthy range tak).</p>
        <a class="btn primary" href="/free-plan?height_cm=${heightCm.toFixed(1)}&weight=${weight}&gender=${gender}">Apna free 3-day fat loss plan paayein</a>`;
    } else {
      excessHtml = `<p>Aap healthy weight range mein hain 🎉 Maintain karne ke liye bhi hum free plan de sakte hain.</p>
        <a class="btn secondary" href="/free-plan?height_cm=${heightCm.toFixed(1)}&weight=${weight}&gender=${gender}">Free meal plan dekhein</a>`;
    }

    out.innerHTML = `<div class="notice">
      <strong>BMI: ${bmi.toFixed(1)} (${bmiCategory(bmi)})</strong><br>
      Healthy weight range (aapki height ke liye): <strong>${idealMin}–${idealMax} kg</strong>
      ${excessHtml}
    </div>`;

    drawChart(heightCm, weight, gender);
  });

  // Draw an initial chart with default form values so the card isn't empty.
  drawChart(((Number(form.feet.value) * 12) + Number(form.inches.value)) * 2.54, Number(form.weight.value) || 70, form.gender.value);
});
