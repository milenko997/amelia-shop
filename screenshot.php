<?php
// Redirect to the generated SVG screenshot
header( 'Content-Type: image/svg+xml' );
?>
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="900" viewBox="0 0 1200 900">
  <rect width="1200" height="900" fill="#faf8f7"/>
  <rect width="1200" height="64" fill="#fff"/>
  <text x="60" y="42" font-family="Georgia,serif" font-size="26" font-weight="700" fill="#2c2c2c">Amelia <tspan fill="#c8a4a5">Shop</tspan></text>
  <rect y="64" width="1200" height="360" fill="#f3eeed"/>
  <text x="600" y="230" font-family="Georgia,serif" font-size="56" font-weight="700" fill="#2c2c2c" text-anchor="middle">Feel Beautiful,</text>
  <text x="600" y="300" font-family="Georgia,serif" font-size="52" font-style="italic" fill="#a8787a" text-anchor="middle">Every Single Day</text>
  <rect x="480" y="340" width="240" height="52" rx="26" fill="#c8a4a5"/>
  <text x="600" y="373" font-family="Arial,sans-serif" font-size="16" font-weight="700" fill="#fff" text-anchor="middle">SHOP NOW</text>
  <rect y="424" width="1200" height="476" fill="#faf8f7"/>
  <text x="600" y="480" font-family="Georgia,serif" font-size="28" font-weight="700" fill="#2c2c2c" text-anchor="middle">Shop by Category</text>
  <?php
  $cats = [['Lingerie','60'],['Underwear','300'],['Bras','540'],['Socks','780'],['Pajamas','1020']];
  foreach ($cats as $c) {
    $x = (int)$c[1];
    echo '<rect x="' . ($x+10) . '" y="510" width="180" height="240" rx="16" fill="#e8d0d1"/>';
    echo '<text x="' . ($x+100) . '" y="650" font-family="Georgia,serif" font-size="16" font-weight="700" fill="#2c2c2c" text-anchor="middle">' . $c[0] . '</text>';
  }
  ?>
</svg>
