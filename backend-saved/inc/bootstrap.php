<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $secure,
        'samesite' => 'Lax',
    ]);
    session_start();
}

define('ROOT_PATH', dirname(__DIR__));
define('DATA_PATH', ROOT_PATH . '/data');
define('PACKAGE_FILE', DATA_PATH . '/packages.json');
define('UPLOAD_PATH', ROOT_PATH . '/assets/uploads');
define('AGENCY_PHONE', '+355 68 600 0655');
define('AGENCY_WHATSAPP', '+355 68 319 5901');
define('AGENCY_WHATSAPP_ALT', '+355 69 259 5638');
define('AGENCY_EMAIL', 'albaniaumra@gmail.com');

function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function packages(): array
{
    if (!is_file(PACKAGE_FILE)) return [];
    $data = json_decode((string)file_get_contents(PACKAGE_FILE), true);
    return is_array($data) ? $data : [];
}

function save_packages(array $items): bool
{
    if (!is_dir(DATA_PATH) && !mkdir(DATA_PATH, 0755, true)) return false;
    $json = json_encode(array_values($items), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) return false;
    $temp = PACKAGE_FILE . '.tmp';
    if (file_put_contents($temp, $json, LOCK_EX) === false) return false;
    if (@rename($temp, PACKAGE_FILE)) return true;
    $copied = @copy($temp, PACKAGE_FILE);
    @unlink($temp);
    return $copied;
}

function active_packages(): array
{
    return array_values(array_filter(packages(), function ($p) {
        return !array_key_exists('active', $p) || $p['active'];
    }));
}

function find_package(string $slug): ?array
{
    foreach (packages() as $package) {
        if (($package['slug'] ?? '') === $slug) return $package;
    }
    return null;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

function valid_csrf(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals((string)$_SESSION['csrf_token'], (string)$_POST['csrf_token']);
}

function wa_link(string $message, string $phone = '355686000655'): string
{
    return 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);
}

function package_card(array $package): void
{
    $photo = $package['image'] ?? 'assets/images/umra-package-poster.jpg';
    $price = (string)($package['price'] ?? 'Na kontaktoni');
    $contactPrice = stripos($price, 'kontaktoni') !== false;
    ?>
    <article class="package-card">
      <a class="package-image" href="paketa.php?slug=<?= rawurlencode((string)$package['slug']) ?>" aria-label="Detajet: <?= h($package['title']) ?>">
        <img src="<?= h($photo) ?>" alt="<?= h($package['title']) ?>" loading="lazy">
        <span class="package-tag"><?= h($package['tag'] ?? 'Udhëtim në grup') ?></span>
      </a>
      <div class="package-card-content">
        <p class="eyebrow small-eyebrow"><?= h($package['date'] ?? '') ?></p>
        <h3><a href="paketa.php?slug=<?= rawurlencode((string)$package['slug']) ?>"><?= h($package['title']) ?></a></h3>
        <p class="package-summary"><?= h($package['summary'] ?? '') ?></p>
        <div class="package-card-bottom">
          <div class="price-lockup"><?php if (!$contactPrice): ?><span>Nga</span><?php endif; ?><strong><?= h($price) ?></strong><?php if (!$contactPrice): ?><span>/ person</span><?php endif; ?></div>
          <a class="text-link" href="rezervo.php?paketa=<?= rawurlencode((string)$package['slug']) ?>">Rezervo <span aria-hidden="true">↗</span></a>
        </div>
      </div>
    </article>
    <?php
}

function site_header(string $title, string $active = ''): void
{
    $links = [
        'Paketa' => ['paketa.php', 'paketa'],
        'Rreth Umres' => ['rreth-umres.php', 'umra'],
        'Pyetje të shpeshta' => ['index.php#pyetje', 'faq'],
        'Kontakt' => ['kontakt.php', 'kontakt'],
    ];
    ?>
    <!doctype html>
    <html lang="sq">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="theme-color" content="#073f40">
      <meta name="description" content="UMRA Travel organizon udhëtime shpirtërore në Mekë dhe Medinë. Zbuloni paketat e Umres 2026–2027 dhe kërkoni rezervimin tuaj.">
      <title><?= h($title) ?> | UMRA Travel</title>
      <link rel="icon" type="image/svg+xml" href="assets/images/umra-logo.svg">
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500;1,600&display=swap" rel="stylesheet">
      <link rel="stylesheet" href="assets/css/site.css">
    </head>
    <body>
      <div class="announcement"><span class="announcement-dot"></span> Një udhëtim që nuk matet vetëm me kilometra, por me ndjenja.</div>
      <header class="site-header" id="siteHeader">
        <div class="nav-shell">
          <a class="brand" href="index.php" aria-label="UMRA Travel – faqja kryesore">
            <img src="assets/images/umra-logo.svg" alt="UMRA Travel">
            <span class="brand-copy"><strong>UMRA <i>TRAVEL</i></strong><small>Udhëtim me përkushtim</small></span>
          </a>
          <button class="menu-toggle" id="menuToggle" aria-label="Hap menunë" aria-expanded="false"><span></span><span></span></button>
          <nav class="main-nav" id="mainNav" aria-label="Menuja kryesore">
            <?php foreach ($links as $label => $item): ?>
              <a class="<?= $active === $item[1] ? 'is-active' : '' ?>" href="<?= h($item[0]) ?>"><?= h($label) ?></a>
            <?php endforeach; ?>
            <a class="nav-cta" href="paketa.php">Gjej udhëtimin <span>↗</span></a>
          </nav>
        </div>
      </header>
    <?php
}

function site_footer(): void
{
    ?>
      <footer class="site-footer">
        <div class="footer-main wrap">
          <div class="footer-brand">
            <a class="brand footer-brand-link" href="index.php"><img src="assets/images/umra-logo.svg" alt=""><span class="brand-copy"><strong>UMRA <i>TRAVEL</i></strong><small>Udhëtim me përkushtim</small></span></a>
            <p>Jo vetëm një udhëtim, por një përjetim pranë vendeve të shenjta.</p>
          </div>
          <div><h3>Eksploro</h3><a href="paketa.php">Paketat e Umres</a><a href="rreth-umres.php">Rreth Umres dhe Haxhit</a><a href="kontakt.php">Na kontakto</a></div>
          <div><h3>Kontakti</h3><a href="tel:+355686000655">+355 68 600 0655</a><a href="https://wa.me/355683195901">WhatsApp +355 68 319 5901</a><a href="mailto:albaniaumra@gmail.com">albaniaumra@gmail.com</a></div>
          <div><h3>Na ndiq</h3><a href="https://www.instagram.com/umratravel/" target="_blank" rel="noopener">Instagram ↗</a><a href="https://www.facebook.com/umratravelalbania/" target="_blank" rel="noopener">Facebook ↗</a><a class="admin-link" href="admin.php">Hyrja e administratorit</a></div>
        </div>
        <div class="footer-bottom wrap"><span>© <?= date('Y') ?> UMRA Travel. Të gjitha të drejtat e rezervuara.</span><span>Shqipëri <b>·</b> Mekë <b>·</b> Medinë</span></div>
      </footer>
      <a class="floating-wa" href="https://wa.me/355683195901" aria-label="Na shkruaj në WhatsApp" target="_blank" rel="noopener"><span>✳</span><small>Pyet në WhatsApp</small></a>
      <script src="assets/js/site.js" defer></script>
    </body>
    </html>
    <?php
}
