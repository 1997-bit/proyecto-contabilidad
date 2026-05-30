<h2>TEST REAL PLANILLA</h2>

<?php
bcscale(8);

$N = 5000;

$floatTotal = 0.0;
$bcTotal = '0.00';

for ($i = 0; $i < $N; $i++) {

    // input controlado (decimales reales)
    $sal = 1000 + ($i % 37) + ($i / 1000);

    // ================= FLOAT =================
    $css = $sal * 0.0975;
    $edu = $sal * 0.0125;
    $bon = $sal * 0.10;

    $bruto = ($sal / 2) + $bon;

    $ded = $css + $edu;
    $neto = $bruto - $ded;

    // redondeo típico de sistemas malos
    $neto = round($neto, 2);

    $floatTotal += $neto;

    // ================= BCMATH =================
    $s = number_format($sal, 2, '.', '');

    $css_b = bcmul($s, '0.0975', 8);
    $edu_b = bcmul($s, '0.0125', 8);
    $bon_b = bcmul(bcdiv($s, '2', 8), '0.10', 8);

    $bruto_b = bcadd(bcdiv($s, '2', 8), $bon_b, 8);
    $ded_b = bcadd($css_b, $edu_b, 8);

    $neto_b = bcsub($bruto_b, $ded_b, 2);

    $bcTotal = bcadd($bcTotal, $neto_b, 2);
}
?>

<h3>FLOAT TOTAL</h3>
<p><?= $floatTotal ?></p>

<h3>BCMATH TOTAL</h3>
<p><?= $bcTotal ?></p>

<h3>DIFERENCIA</h3>
<p><?= $floatTotal - (float)$bcTotal ?></p>