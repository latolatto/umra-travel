<?php
require __DIR__ . '/inc/bootstrap.php';
$slug = trim((string)($_GET['slug'] ?? ''));
if ($slug !== '') {
    $package = find_package($slug);
    if (!$package || (isset($package['active']) && !$package['active'])) {
        http_response_code(404);
        site_header('Paketa nuk u gjet', 'paketa');
        ?>
        <main><section class="page-hero compact"><div class="wrap"><p class="eyebrow">UMRA TRAVEL</p><h1>Kjo paketë nuk u gjet.</h1><p>Mund të shikosh udhëtimet e tjera të publikuara ose të na kontaktosh për ndihmë.</p><div class="hero-actions"><a class="button" href="paketa.php">Shiko paketat</a></div></div></section></main>
        <?php site_footer(); exit;
    }
    site_header($package['title'], 'paketa');
    $features = is_array($package['features'] ?? null) ? $package['features'] : [];
    ?>
    <main>
      <section class="page-hero compact"><div class="wrap"><p class="eyebrow"><?= h($package['tag'] ?? 'UMRA Travel') ?></p><h1><?= h($package['title']) ?></h1><p><?= h($package['date'] ?? '') ?> <span style="color:var(--gold-light)">·</span> Udhëtim drejt Mekës dhe Medinës</p></div></section>
      <section class="section"><div class="wrap detail-top">
        <div class="detail-image"><img src="<?= h($package['image'] ?? 'assets/images/umra-package-poster.jpg') ?>" alt="<?= h($package['title']) ?>" fetchpriority="high"></div>
        <div class="detail-copy"><p class="eyebrow section-kicker">Detajet e udhëtimit</p><h2><?= h($package['title']) ?></h2><p><?= h($package['description'] ?? $package['summary'] ?? '') ?></p>
          <div class="detail-price"><small><?= stripos((string)($package['price'] ?? ''), 'kontaktoni') !== false ? 'Çmimi sipas kërkesës' : 'Çmimi fillestar, për person' ?></small><strong><?= h($package['price'] ?? 'Na kontaktoni') ?></strong></div>
          <ul class="feature-list"><?php foreach ($features as $feature): ?><li><?= h($feature) ?></li><?php endforeach; ?></ul>
          <div class="detail-actions"><a class="button button-dark" href="rezervo.php?paketa=<?= rawurlencode((string)$package['slug']) ?>">Kërko rezervim <span>↗</span></a><a class="button button-light" href="https://wa.me/355683195901" target="_blank" rel="noopener">Pyet në WhatsApp</a></div>
        </div>
      </div></section>
      <section class="section-tight" style="background:#f0efe7"><div class="wrap"><div class="cta-band-inner"><div><h2 style="margin:0;color:var(--petrol);font:500 31px/1.15 var(--serif)">Dëshiron të dish më shumë?</h2><p style="margin:8px 0 0;color:var(--muted);font-size:11px">Na shkruaj për disponueshmërinë, përfshirjet dhe hapat e rezervimit.</p></div><a class="button button-dark" href="kontakt.php">Kontakto agjencinë <span>↗</span></a></div></div></section>
    </main>
    <?php site_footer(); exit; ?>
<?php
}

site_header('Paketat e Umres', 'paketa');
$items = active_packages();
?>
<main>
  <section class="page-hero"><div class="wrap"><p class="eyebrow">Udhëtimet 2026–2027</p><h1>Gjej udhëtimin që të flet zemrës.</h1><p>Udhëtime në grup për data të caktuara, paketa të veçanta të Ramazanit dhe Umre e organizuar sipas dëshirës sate.</p></div></section>
  <section class="package-listing"><div class="wrap">
    <div class="section-head"><div><p class="eyebrow section-kicker">Të publikuara nga UMRA Travel</p><h2>Paketat e udhëtimit</h2></div><p>Çmimi dhe disponueshmëria mund të ndryshojnë. Kontakto agjencinë për konfirmim para rezervimit.</p></div>
    <?php if ($items): ?><div class="package-grid"><?php foreach ($items as $package) package_card($package); ?></div><?php else: ?><p>Aktualisht nuk ka paketa të publikuara. Na kontakto për më shumë informacion.</p><?php endif; ?>
    <div class="package-note"><strong>Shënim:</strong> Paketat e Ramazanit kanë hollësi dhe çmime sipas datës dhe dhomës. Çdo kontribut sadakaje vlen vetëm për paketat ku është shënuar. Kërkesa për rezervim nuk përbën konfirmim të vendit.</div>
  </div></section>
  <section class="cta-band"><div class="wrap cta-band-inner"><div><h2>Nuk po gjen datën e përshtatshme?</h2><p>Pyet për Umren e personalizuar.</p></div><a class="button button-dark" href="rezervo.php?paketa=umre-e-personalizuar">Planifiko udhëtimin <span>↗</span></a></div></section>
</main>
<?php site_footer(); ?>
