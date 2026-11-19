<?php
require __DIR__ . '/inc/bootstrap.php';
site_header('Një udhëtim me kuptim', 'home');
$all = active_packages();
$featured = array_values(array_filter($all, function ($p) {
    return strpos((string)($p['slug'] ?? ''), 'ramazan') === false && ($p['slug'] ?? '') !== 'umre-e-personalizuar';
}));
$featured = array_slice($featured, 0, 3);
$ramadan = array_values(array_filter($all, function ($p) { return strpos((string)($p['slug'] ?? ''), 'ramazan') !== false; }));
?>
<main>
  <section class="hero">
    <div class="hero-media" aria-hidden="true">
      <video autoplay muted loop playsinline poster="assets/images/custom-umra-poster.jpg">
        <source src="assets/images/umra-2026.mp4" type="video/mp4">
      </video>
    </div>
    <div class="hero-shade"></div>
    <div class="hero-content">
      <div class="hero-copy">
        <p class="eyebrow">UMRA TRAVEL · MEKË &amp; MEDINË</p>
        <h1>Ka udhëtime që të çojnë <em>më pranë.</em></h1>
        <p class="hero-lede">Një udhëtim që nuk matet vetëm me kilometra, por me ndjenja. Ne kujdesemi për organizimin, që ti të përqendrohesh te përjetimi.</p>
        <div class="hero-actions">
          <a class="button" href="paketa.php">Zbulo paketat <span>↗</span></a>
          <a class="button button-outline" href="paketa.php?slug=umre-e-personalizuar">Planifiko Umren tënde</a>
        </div>
        <div class="hero-note"><i>✳</i><span>Udhëtime në grup dhe Umre e personalizuar për individë e familje</span></div>
      </div>
      <div class="hero-stamp"><span>Një përjetim pranë vendeve të shenjta</span></div>
      <div class="hero-foot"><span>Shqipëri <b>·</b> Mekë <b>·</b> Medinë</span><span><span>Me përkushtim</span><span>Me qetësi</span><span>Me kujdes</span></span></div>
    </div>
  </section>

  <section class="section" id="paketat">
    <div class="wrap">
      <div class="section-head">
        <div><p class="eyebrow section-kicker">Udhëtimi yt nis këtu</p><h2>Paketa të zgjedhura</h2></div>
        <div><p>Data të planifikuara për vitin 2026 dhe Ramazanin 2027. Çdo udhëtim mund të nisë me një bisedë të thjeshtë.</p><a class="text-link" href="paketa.php">Shiko të gjitha paketat <span>↗</span></a></div>
      </div>
      <div class="package-grid">
        <?php foreach ($featured as $package) package_card($package); ?>
      </div>
    </div>
  </section>

  <section class="manifesto">
    <div class="wrap manifesto-grid">
      <div><p class="eyebrow">Përtej një udhëtimi</p><h2>Një hap drejt Shtëpisë së Tij. <em>Një kujtim për gjithë jetën.</em></h2></div>
      <div><p>Ka vende që i viziton për t’i fotografuar. E pastaj ka udhëtime që i mban me vete edhe kur kthehesh. UMRA Travel organizon përvojën tënde në Mekë dhe Medinë me kujdes, qartësi dhe shoqërim.</p><a class="text-link" href="rreth-umres.php">Mëso për Umren dhe Haxhin <span>↗</span></a></div>
    </div>
  </section>

  <section class="section section-tight">
    <div class="wrap service-strip">
      <div class="service-item"><span class="service-icon">✈</span><div><strong>Udhëtim i organizuar</strong><small>Fluturim dhe logjistikë e planifikuar</small></div></div>
      <div class="service-item"><span class="service-icon">⌂</span><div><strong>Mekë dhe Medinë</strong><small>Akomodim pranë vendeve të shenjta</small></div></div>
      <div class="service-item"><span class="service-icon">☾</span><div><strong>Shoqërim në grup</strong><small>Udhëheqës gjatë udhëtimit</small></div></div>
      <div class="service-item"><span class="service-icon">♡</span><div><strong>Sadaka për udhëtarët</strong><small>10 € për çdo umrexhi, sipas paketës</small></div></div>
    </div>
  </section>

  <section class="section section-tight" style="padding-top:20px">
    <div class="wrap story-grid">
      <div class="story-image"><img src="assets/images/custom-umra-poster.jpg" alt="Pamje nga Meka dhe Medina në materialin e UMRA Travel" loading="lazy"></div>
      <div class="story-copy"><p class="eyebrow section-kicker">Për ty, sipas mënyrës tënde</p><h2>Umre e personalizuar</h2><p>Zgjidh vetë datat e udhëtimit, aeroportin, hotelin dhe kohëzgjatjen e qëndrimit. Ne të ndihmojmë të organizosh hollësitë, për një udhëtim të përshtatur për ty, familjen apo grupin tënd.</p><blockquote class="story-quote">“Ne kujdesemi për organizimin e çdo detaji, që ti ta përjetosh udhëtimin me qetësi dhe përkushtim.”</blockquote><a class="button button-dark" href="rezervo.php?paketa=umre-e-personalizuar">Kërko planifikim <span>↗</span></a></div>
    </div>
  </section>

  <?php if ($ramadan): ?>
  <section class="section section-tight">
    <div class="wrap">
      <div class="section-head"><div><p class="eyebrow section-kicker">Ramazan 2027</p><h2>Ramazani pranë Qabes</h2></div><p>Dy nisje të publikuara për Ramazan, me çmime dhe konfigurime dhomash të ndryshme.</p></div>
      <div class="package-grid"><?php foreach ($ramadan as $package) package_card($package); ?></div>
    </div>
  </section>
  <?php endif; ?>

  <section class="faq-section section" id="pyetje">
    <div class="wrap faq-layout">
      <div class="faq-side"><p class="eyebrow section-kicker">Para se të niseni</p><h2>Pyetjet e shpeshta</h2><p>Nëse nuk e gjen përgjigjen që kërkon, na shkruaj. Ekipi ynë të ndihmon me informacion për paketën dhe përgatitjen.</p><a class="text-link" href="kontakt.php">Na bëj një pyetje <span>↗</span></a></div>
      <div class="faq-list">
        <details class="faq-item"><summary>Çfarë përfshihet në paketat e grupit?</summary><p>Përmbajtja varet nga paketa. Paketat e publikuara përfshijnë shërbime si vizë, akomodim, transport lokal, tren ekspres dhe udhëheqës grupi. Hollësitë shfaqen te secila paketë dhe konfirmohen nga agjencia.</p></details>
        <details class="faq-item"><summary>A mund t’i zgjedh vetë datat e udhëtimit?</summary><p>Po. Umra e personalizuar u mundëson individëve dhe familjeve të kërkojnë data, aeroport, hotel dhe kohëzgjatje sipas preferencës.</p></details>
        <details class="faq-item"><summary>Si mund të rezervoj vendin?</summary><p>Zgjidh paketën dhe plotëso formularin e shkurtër. Kërkesa përgatitet në WhatsApp; ajo dërgohet vetëm pasi ta hapësh dhe ta konfirmosh mesazhin.</p></details>
        <details class="faq-item"><summary>Çfarë dokumentesh duhen për udhëtim?</summary><p>Kërkesat mund të varen nga dokumentet dhe rregullat në fuqi. Na kontakto për udhëzimet e përditësuara për udhëtimin tënd.</p></details>
        <details class="faq-item"><summary>A ka kontribut bamirësie në paketë?</summary><p>Në paketat për të cilat është publikuar ky informacion, 10 € nga pagesa e çdo udhëtari shkojnë për sadaka. Konfirmoje këtë hollësi me ekipin gjatë rezervimit.</p></details>
      </div>
    </div>
  </section>

  <section class="cta-band"><div class="wrap cta-band-inner"><div><h2>Kur zemra të thërret, ne ndihmojmë me rrugën.</h2><p>Na trego si e imagjinon udhëtimin tënd.</p></div><a class="button button-dark" href="kontakt.php">Bisedo me UMRA Travel <span>↗</span></a></div></section>
</main>
<?php site_footer(); ?>
