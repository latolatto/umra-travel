<?php
require __DIR__ . '/inc/bootstrap.php';
$sentLink = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!valid_csrf()) {
        $error = 'Sesioni skadoi. Rifresko faqen dhe provo përsëri.';
    } else {
        $name = trim((string)($_POST['name'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $topic = trim((string)($_POST['topic'] ?? ''));
        $message = trim((string)($_POST['message'] ?? ''));
        if ($name === '' || $phone === '' || $message === '') {
            $error = 'Plotëso emrin, numrin e telefonit dhe pyetjen.';
        } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Shkruaj një adresë emaili të vlefshme ose lëre bosh.';
        } else {
            $text = "Përshëndetje UMRA Travel!\nKam një pyetje nga faqja web.\n\nEmri: {$name}\nTelefoni: {$phone}\nEmail: " . ($email ?: 'Nuk u dha') . "\nTema: " . ($topic ?: 'Pyetje e përgjithshme') . "\n\nMesazhi: {$message}";
            $sentLink = wa_link($text);
        }
    }
}
site_header('Kontakt', 'kontakt');
?>
<main>
  <section class="page-hero compact"><div class="wrap"><p class="eyebrow">Jemi pranë teje</p><h1>Le ta nisim me një bisedë.</h1><p>Pyet për paketat, datat, përgatitjen ose një udhëtim të personalizuar. Ekipi ynë të përgjigjet përmes telefonit, WhatsApp-it ose emailit.</p></div></section>
  <section class="section"><div class="wrap contact-layout">
    <div class="contact-intro"><p class="eyebrow section-kicker">Kontakto UMRA Travel</p><h1>Pyetjet e tua janë të mirëpritura.</h1><p>Na trego çfarë po kërkon dhe do të të ndihmojmë të gjesh informacionin përkatës. Nuk kemi publikuar adresë zyre fizike; komunikimi dhe rezervimet bëhen drejtpërdrejt.</p>
      <div class="contact-links">
        <a class="contact-link" href="tel:+355686000655"><span>☎</span><span><strong>Telefon</strong><small>+355 68 600 0655</small></span></a>
        <a class="contact-link" href="https://wa.me/355683195901" target="_blank" rel="noopener"><span>◉</span><span><strong>WhatsApp</strong><small>+355 68 319 5901</small></span></a>
        <a class="contact-link" href="https://wa.me/355692595638" target="_blank" rel="noopener"><span>◉</span><span><strong>WhatsApp alternativ</strong><small>+355 69 259 5638</small></span></a>
        <a class="contact-link" href="mailto:albaniaumra@gmail.com"><span>✉</span><span><strong>Email</strong><small>albaniaumra@gmail.com</small></span></a>
      </div>
      <p style="margin-top:20px;font-size:10px"><a href="https://www.instagram.com/umratravel/" target="_blank" rel="noopener">Instagram @umratravel ↗</a> <span style="color:#a28a4d;padding:0 7px">·</span> <a href="https://www.facebook.com/umratravelalbania/" target="_blank" rel="noopener">Facebook UMRA Travel ↗</a></p>
    </div>
    <div class="form-card"><h2>Na dërgo pyetjen</h2><p>Plotëso formularin dhe mesazhi do të hapet gati në WhatsApp.</p>
      <?php if ($error): ?><div class="feedback" role="alert"><?= h($error) ?></div><?php endif; ?>
      <?php if ($sentLink): ?><div class="success-panel" role="status"><strong>Mesazhi u përgatit.</strong><br>Hape WhatsApp-in dhe shtyp “Dërgo” për t’ia përcjellë ekipit.<p><a href="<?= h($sentLink) ?>" target="_blank" rel="noopener">Hap WhatsApp-in ↗</a></p></div><?php else: ?>
      <form method="post" action="kontakt.php" class="form-grid">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <div class="form-field"><label for="name">Emri *</label><input id="name" name="name" autocomplete="name" required value="<?= h($_POST['name'] ?? '') ?>" placeholder="Emri dhe mbiemri"></div>
        <div class="form-field"><label for="phone">Telefoni *</label><input id="phone" name="phone" autocomplete="tel" required value="<?= h($_POST['phone'] ?? '') ?>" placeholder="+355 ..."></div>
        <div class="form-field"><label for="email">Email (opsional)</label><input id="email" name="email" type="email" autocomplete="email" value="<?= h($_POST['email'] ?? '') ?>" placeholder="emri@email.com"></div>
        <div class="form-field"><label for="topic">Tema</label><select id="topic" name="topic"><option>Pyetje e përgjithshme</option><option>Paketat e grupit</option><option>Umre e personalizuar</option><option>Umre në Ramazan</option><option>Përgatitja dhe dokumentet</option></select></div>
        <div class="form-field full"><label for="message">Pyetja jote *</label><textarea id="message" name="message" required placeholder="Si mund të të ndihmojmë?"><?= h($_POST['message'] ?? '') ?></textarea></div>
        <div class="form-field full"><button class="button button-dark form-submit" type="submit">Vazhdo në WhatsApp <span>↗</span></button><p class="form-note">Mesazhi nuk dërgohet automatikisht. Ai përgatitet dhe hapet në WhatsApp; dërgoje pasi të kontrollosh përmbajtjen.</p></div>
      </form><?php endif; ?>
    </div>
  </div></section>
</main>
<?php site_footer(); ?>
