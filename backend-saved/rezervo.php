<?php
require __DIR__ . '/inc/bootstrap.php';
$items = active_packages();
$selectedSlug = trim((string)($_GET['paketa'] ?? $_POST['package'] ?? ''));
$selected = null;
foreach ($items as $candidate) if (($candidate['slug'] ?? '') === $selectedSlug) { $selected = $candidate; break; }
if (!$selected && $items) $selected = $items[0];
$error = '';
$sentLink = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!valid_csrf()) {
        $error = 'Sesioni skadoi. Rifresko faqen dhe provo përsëri.';
    } else {
        $name = trim((string)($_POST['name'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $persons = trim((string)($_POST['persons'] ?? '1'));
        $message = trim((string)($_POST['message'] ?? ''));
        $requestedSlug = trim((string)($_POST['package'] ?? ''));
        $requested = null;
        foreach ($items as $candidate) if (($candidate['slug'] ?? '') === $requestedSlug) { $requested = $candidate; break; }
        if (!$requested) $error = 'Zgjidh një paketë të vlefshme.';
        elseif ($name === '' || $phone === '') $error = 'Plotëso emrin dhe numrin e telefonit.';
        elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Shkruaj një adresë emaili të vlefshme ose lëre bosh.';
        else {
            $selected = $requested;
            $text = "Përshëndetje UMRA Travel!\nDëshiroj informacion për rezervim.\n\nPaketa: " . ($requested['title'] ?? '') . "\nData: " . ($requested['date'] ?? 'Sipas kërkesës') . "\nEmri: {$name}\nTelefoni: {$phone}\nEmail: " . ($email ?: 'Nuk u dha') . "\nUdhëtarë: {$persons}\nKërkesë shtesë: " . ($message ?: 'Nuk ka');
            $sentLink = wa_link($text);
        }
    }
}
site_header('Kërko rezervim', 'paketa');
?>
<main>
  <section class="booking-heading"><div class="wrap"><p class="eyebrow">Kërkesë për rezervim</p><h1>Një hap i vogël drejt udhëtimit.</h1><p>Plotëso të dhënat dhe vazhdo bisedën me ekipin tonë në WhatsApp.</p></div></section>
  <section class="booking-wrap"><div class="wrap booking-layout">
    <?php if ($selected): ?><aside class="booking-summary"><img src="<?= h($selected['image'] ?? 'assets/images/umra-package-poster.jpg') ?>" alt="" loading="lazy"><p class="eyebrow" style="margin:15px 0 0"><?= h($selected['tag'] ?? 'UMRA Travel') ?></p><h2><?= h($selected['title']) ?></h2><div class="date"><?= h($selected['date'] ?? 'Datat sipas kërkesës') ?></div><p><?= h($selected['summary'] ?? '') ?></p><p>Çmimi nga <strong><?= h($selected['price'] ?? 'Na kontaktoni') ?></strong> / person</p></aside><?php endif; ?>
    <div class="booking-form"><h2>Të dhënat e udhëtarit</h2><p>Kërkesa jote nuk konfirmon ende vendin. Ekipi do të të kontaktojë për disponueshmërinë dhe hollësitë.</p>
      <?php if ($error): ?><div class="feedback" role="alert"><?= h($error) ?></div><?php endif; ?>
      <?php if ($sentLink): ?><div class="success-panel" role="status"><strong>Kërkesa u përgatit.</strong><br>Kontrollo mesazhin dhe hape WhatsApp-in për t’ia dërguar UMRA Travel.<p><a href="<?= h($sentLink) ?>" target="_blank" rel="noopener">Vazhdo në WhatsApp ↗</a></p></div><?php else: ?>
      <form method="post" action="rezervo.php" class="form-grid">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <div class="form-field full"><label for="package">Paketa *</label><select id="package" name="package" required><?php foreach ($items as $item): ?><option value="<?= h($item['slug']) ?>" <?= $selected && $selected['slug'] === $item['slug'] ? 'selected' : '' ?>><?= h($item['title']) ?> · <?= h($item['date'] ?? '') ?></option><?php endforeach; ?></select></div>
        <div class="form-field"><label for="name">Emri dhe mbiemri *</label><input id="name" name="name" autocomplete="name" required value="<?= h($_POST['name'] ?? '') ?>" placeholder="Emri yt"></div>
        <div class="form-field"><label for="phone">Numri i telefonit *</label><input id="phone" name="phone" autocomplete="tel" required value="<?= h($_POST['phone'] ?? '') ?>" placeholder="+355 ..."></div>
        <div class="form-field"><label for="email">Email (opsional)</label><input id="email" name="email" type="email" autocomplete="email" value="<?= h($_POST['email'] ?? '') ?>" placeholder="emri@email.com"></div>
        <div class="form-field"><label for="persons">Numri i udhëtarëve</label><select id="persons" name="persons"><?php for ($n=1;$n<=10;$n++): ?><option value="<?= $n ?>" <?= (string)($_POST['persons'] ?? '1') === (string)$n ? 'selected' : '' ?>><?= $n ?> <?= $n === 1 ? 'person' : 'persona' ?></option><?php endfor; ?></select></div>
        <div class="form-field full"><label for="message">Shënim shtesë (opsional)</label><textarea id="message" name="message" placeholder="P.sh. preferencë për dhomë ose pyetje për udhëtimin"><?= h($_POST['message'] ?? '') ?></textarea></div>
        <div class="form-field full"><button class="button button-dark form-submit" type="submit">Përgatit kërkesën <span>↗</span></button><p class="form-note">Mesazhi përgatitet në këtë faqe dhe dërgohet vetëm pasi të vazhdosh në WhatsApp. Të dhënat nuk ruhen në këtë website.</p></div>
      </form><?php endif; ?>
    </div>
  </div></section>
</main>
<?php site_footer(); ?>
