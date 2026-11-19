<?php
require __DIR__ . '/inc/bootstrap.php';
if (!is_dir(DATA_PATH)) mkdir(DATA_PATH, 0755, true);
$authFile = DATA_PATH . '/admin-auth.json';
$authData = is_file($authFile) ? json_decode((string)file_get_contents($authFile), true) : null;
$isSetup = !is_array($authData) || empty($authData['password_hash']);
$loggedIn = !empty($_SESSION['umra_admin']);
$error = '';
$notice = '';
$knownImages = [
    'assets/images/umra-package-poster.jpg',
    'assets/images/included-poster.jpg',
    'assets/images/ramadan-poster.jpg',
    'assets/images/custom-umra-poster.jpg',
];

function make_slug(string $value): string
{
    $ascii = function_exists('iconv') ? iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) : $value;
    $ascii = strtolower((string)$ascii);
    $slug = trim((string)preg_replace('/[^a-z0-9]+/', '-', $ascii), '-');
    return $slug !== '' ? $slug : 'paketa-' . substr(bin2hex(random_bytes(4)), 0, 8);
}

function store_package_photo(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || ($file['size'] ?? 0) > 5 * 1024 * 1024) return null;
    if (!is_dir(UPLOAD_PATH) && !mkdir(UPLOAD_PATH, 0755, true)) return null;
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
    if ($finfo) finfo_close($finfo);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime])) return null;
    $dimensions = @getimagesize($file['tmp_name']);
    if (!$dimensions || $dimensions[0] > 6000 || $dimensions[1] > 6000) return null;
    $filename = 'package-' . bin2hex(random_bytes(12)) . '.' . $extensions[$mime];
    $destination = UPLOAD_PATH . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) return null;
    return 'assets/uploads/' . $filename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!valid_csrf()) {
        $error = 'Sesioni skadoi. Rifresko faqen dhe provo përsëri.';
    } elseif (($_POST['action'] ?? '') === 'setup' && $isSetup) {
        $password = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');
        if (strlen($password) < 12) $error = 'Fjalëkalimi duhet të ketë të paktën 12 karaktere.';
        elseif ($password !== $confirm) $error = 'Fjalëkalimet nuk përputhen.';
        else {
            $saved = file_put_contents($authFile, json_encode(['password_hash' => password_hash($password, PASSWORD_DEFAULT)], JSON_PRETTY_PRINT), LOCK_EX);
            if ($saved === false) $error = 'Nuk mund të ruhej konfigurimi. Kontrollo lejet e shkrimit te dosja data/.';
            else { @chmod($authFile, 0600); $isSetup = false; $authData = json_decode((string)file_get_contents($authFile), true); $notice = 'Fjalëkalimi u krijua. Tani mund të hysh në panel.'; }
        }
    } elseif (($_POST['action'] ?? '') === 'login' && !$isSetup) {
        $attempts = (int)($_SESSION['admin_attempts'] ?? 0);
        if ($attempts >= 8) $error = 'Shumë tentativa hyrjeje. Rifresko sesionin ose provo pas pak.';
        elseif (password_verify((string)($_POST['password'] ?? ''), (string)($authData['password_hash'] ?? ''))) {
            session_regenerate_id(true); $_SESSION['umra_admin'] = true; unset($_SESSION['admin_attempts']); $loggedIn = true;
        } else { $_SESSION['admin_attempts'] = $attempts + 1; $error = 'Fjalëkalimi nuk është i saktë.'; }
    } elseif (($_POST['action'] ?? '') === 'logout' && $loggedIn) {
        unset($_SESSION['umra_admin']); session_regenerate_id(true); $loggedIn = false;
    } elseif (($_POST['action'] ?? '') === 'save_package' && $loggedIn) {
        $items = packages();
        $originalSlug = trim((string)($_POST['original_slug'] ?? ''));
        $index = null;
        foreach ($items as $i => $item) if (($item['slug'] ?? '') === $originalSlug && $originalSlug !== '') { $index = $i; break; }
        $title = trim((string)($_POST['title'] ?? ''));
        $date = trim((string)($_POST['date'] ?? ''));
        $price = trim((string)($_POST['price'] ?? ''));
        $summary = trim((string)($_POST['summary'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $tag = trim((string)($_POST['tag'] ?? ''));
        $featureText = trim((string)($_POST['features'] ?? ''));
        $postedSlug = trim((string)($_POST['slug'] ?? ''));
        $slug = make_slug($postedSlug !== '' ? $postedSlug : $title);
        if ($title === '' || $date === '' || $summary === '' || $description === '') {
            $error = 'Plotëso emrin, datën, përshkrimin e shkurtër dhe hollësitë.';
        } else {
            foreach ($items as $i => $existing) {
                if (($existing['slug'] ?? '') === $slug && $i !== $index) $slug .= '-' . substr(bin2hex(random_bytes(2)), 0, 4);
            }
            $selectedImage = (string)($_POST['image'] ?? 'assets/images/umra-package-poster.jpg');
            if (!in_array($selectedImage, $knownImages, true) && !preg_match('#^assets/uploads/[a-zA-Z0-9_-]+\.(jpg|png|webp)$#', $selectedImage)) $selectedImage = 'assets/images/umra-package-poster.jpg';
            $uploaded = store_package_photo($_FILES['photo'] ?? []);
            if (isset($_FILES['photo']) && ($_FILES['photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE && !$uploaded) {
                $error = 'Fotografia nuk u ngarkua. Përdor JPG, PNG ose WebP deri në 5 MB.';
            } else {
                $image = $uploaded ?: $selectedImage;
                $features = array_values(array_filter(array_map('trim', preg_split('/\R/', $featureText)), function ($line) { return $line !== ''; }));
                $record = ['slug' => $slug, 'title' => $title, 'tag' => $tag ?: 'Udhëtim në grup', 'date' => $date, 'price' => $price ?: 'Na kontaktoni', 'summary' => $summary, 'description' => $description, 'features' => $features, 'image' => $image, 'active' => isset($_POST['active'])];
                if ($index === null) $items[] = $record; else $items[$index] = $record;
                if (save_packages($items)) { $notice = $index === null ? 'Paketa u shtua.' : 'Ndryshimet u ruajtën.'; $_GET['edit'] = $slug; }
                else $error = 'Ndryshimet nuk u ruajtën. Kontrollo lejet e shkrimit te dosja data/.';
            }
        }
    }
}

$items = packages();
$editSlug = trim((string)($_GET['edit'] ?? ''));
$editing = null;
foreach ($items as $item) if (($item['slug'] ?? '') === $editSlug) { $editing = $item; break; }
?>
<!doctype html><html lang="sq"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Administrimi | UMRA Travel</title><link rel="stylesheet" href="assets/css/site.css"></head>
<body class="admin-body">
  <header class="admin-top"><a class="brand" href="index.php"><img src="assets/images/umra-logo.svg" alt=""><span class="brand-copy"><strong>UMRA <i>TRAVEL</i></strong><small>Panel administrimi</small></span></a><?php if ($loggedIn): ?><form method="post"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="logout"><button class="button button-outline button-small" type="submit">Dil nga paneli</button></form><?php else: ?><a class="exit" href="index.php">Kthehu te faqja ↗</a><?php endif; ?></header>
  <?php if (!$loggedIn): ?>
    <main class="admin-login"><div class="form-card">
      <p class="eyebrow section-kicker"><?= $isSetup ? 'Konfigurimi fillestar' : 'Hyrje e sigurt' ?></p>
      <h2><?= $isSetup ? 'Krijo fjalëkalimin e administratorit' : 'Paneli i paketave' ?></h2>
      <p><?= $isSetup ? 'Ky hap shfaqet vetëm herën e parë. Fjalëkalimi ruhet i hashuar.' : 'Hyr për të shtuar ose përditësuar paketat e udhëtimit.' ?></p>
      <?php if ($error): ?><div class="error-box" role="alert"><?= h($error) ?></div><?php endif; ?><?php if ($notice): ?><div class="success-box"><?= h($notice) ?></div><?php endif; ?>
      <form method="post" class="form-grid"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="<?= $isSetup ? 'setup' : 'login' ?>">
        <div class="form-field full"><label for="password">Fjalëkalimi</label><input id="password" name="password" type="password" required minlength="<?= $isSetup ? 12 : 1 ?>" autocomplete="<?= $isSetup ? 'new-password' : 'current-password' ?>" placeholder="<?= $isSetup ? 'Të paktën 12 karaktere' : 'Fjalëkalimi' ?>"></div>
        <?php if ($isSetup): ?><div class="form-field full"><label for="confirm_password">Përsërit fjalëkalimin</label><input id="confirm_password" name="confirm_password" type="password" required minlength="12" autocomplete="new-password"></div><?php endif; ?>
        <div class="form-field full"><button class="button button-dark form-submit" type="submit"><?= $isSetup ? 'Krijo llogarinë' : 'Hyr në panel' ?> <span>↗</span></button></div>
      </form>
      <?php if ($isSetup): ?><p class="form-help">Për aktivizim, hostimi duhet të lejojë shkrimin në dosjen <code>data/</code>. Mbaje këtë fjalëkalim vetëm për ekipin e agjencisë.</p><?php endif; ?>
    </div></main>
  <?php else: ?>
    <main class="admin-wrap">
      <div class="admin-heading"><div><p class="eyebrow section-kicker">UMRA Travel · Përmbajtja e faqes</p><h1>Menaxho paketat</h1><p>Shto udhëtime të reja, ndrysho tekstet dhe fotot, ose hiqi përkohësisht nga faqja.</p></div><a class="button button-dark button-small" href="admin.php?new=1">+ Shto paketë</a></div>
      <?php if ($error): ?><div class="error-box" role="alert"><?= h($error) ?></div><?php endif; ?><?php if ($notice): ?><div class="success-box" role="status"><?= h($notice) ?></div><?php endif; ?>
      <div class="admin-grid">
        <section class="admin-panel"><h2>Paketat e ruajtura</h2><div style="overflow-x:auto"><table class="admin-table"><thead><tr><th>Paketa</th><th>Data / çmimi</th><th>Statusi</th><th>Veprime</th></tr></thead><tbody>
          <?php foreach ($items as $item): ?><tr><td><strong><?= h($item['title'] ?? '') ?></strong><small><?= h($item['tag'] ?? '') ?></small></td><td><?= h($item['date'] ?? '') ?><br><small><?= h($item['price'] ?? '') ?></small></td><td><span class="admin-tag <?= empty($item['active']) ? 'off' : '' ?>"><?= empty($item['active']) ? 'E fshehur' : 'Publikuar' ?></span></td><td class="row-actions"><a href="admin.php?edit=<?= rawurlencode((string)$item['slug']) ?>">Ndrysho</a></td></tr><?php endforeach; ?>
        </tbody></table></div></section>
        <section class="admin-panel"><h2><?= $editing ? 'Ndrysho paketën' : 'Shto paketë të re' ?></h2>
          <?php if (!$items): ?><p class="form-help">Nuk ka ende paketa.</p><?php endif; ?>
          <form method="post" action="admin.php?<?= $editing ? 'edit=' . rawurlencode((string)$editing['slug']) : 'new=1' ?>" enctype="multipart/form-data" class="admin-form"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="save_package"><input type="hidden" name="original_slug" value="<?= h($editing['slug'] ?? '') ?>">
            <div class="form-grid">
              <div class="form-field full"><label for="title">Emri i paketës *</label><input id="title" name="title" required value="<?= h($editing['title'] ?? '') ?>"></div>
              <div class="form-field"><label for="slug">Adresa e shkurtër</label><input id="slug" name="slug" value="<?= h($editing['slug'] ?? '') ?>" placeholder="p.sh. umre-dhjetor-2026"></div>
              <div class="form-field"><label for="tag">Etiketa</label><input id="tag" name="tag" value="<?= h($editing['tag'] ?? '') ?>" placeholder="Grup · Vjeshtë 2026"></div>
              <div class="form-field"><label for="date">Datat *</label><input id="date" name="date" required value="<?= h($editing['date'] ?? '') ?>" placeholder="3 – 11 Nëntor 2026"></div>
              <div class="form-field"><label for="price">Çmimi</label><input id="price" name="price" value="<?= h($editing['price'] ?? '') ?>" placeholder="960 €"></div>
              <div class="form-field full"><label for="summary">Përshkrimi i shkurtër *</label><textarea id="summary" name="summary" required><?= h($editing['summary'] ?? '') ?></textarea></div>
              <div class="form-field full"><label for="description">Hollësitë e paketës *</label><textarea id="description" name="description" required><?= h($editing['description'] ?? '') ?></textarea></div>
              <div class="form-field full"><label for="features">Çfarë përfshihet (një pikë për rresht)</label><textarea id="features" name="features" placeholder="Vizë 1-vjeçare&#10;Hotel në Mekë dhe Medinë"><?= h(implode("\n", is_array($editing['features'] ?? null) ? $editing['features'] : [])) ?></textarea></div>
              <div class="form-field full"><label for="image">Fotoja e paketës</label><select id="image" name="image"><?php foreach ($knownImages as $image): ?><option value="<?= h($image) ?>" <?= ($editing['image'] ?? 'assets/images/umra-package-poster.jpg') === $image ? 'selected' : '' ?>><?= h(basename($image)) ?></option><?php endforeach; ?><?php if (!empty($editing['image']) && strpos($editing['image'], 'assets/uploads/') === 0): ?><option value="<?= h($editing['image']) ?>" selected>Fotoja aktuale e ngarkuar</option><?php endif; ?></select></div>
              <?php if (!empty($editing['image'])): ?><div class="form-field full"><div class="current-image"><img src="<?= h($editing['image']) ?>" alt="Fotoja aktuale"><span>Fotoja aktuale</span></div></div><?php endif; ?>
              <div class="form-field full"><label for="photo">Ngarko foto të re (JPG, PNG ose WebP, max. 5 MB)</label><input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp"></div>
              <div class="form-field full"><label class="admin-check"><input type="checkbox" name="active" value="1" <?= !isset($editing['active']) || !empty($editing['active']) ? 'checked' : '' ?>> Shfaqe këtë paketë në website</label></div>
              <div class="form-field full"><button type="submit" class="button button-dark form-submit">Ruaj paketën <span>↗</span></button></div>
            </div>
          </form>
        </section>
      </div>
      <p class="form-help" style="margin:16px 3px">Përditësimet bëhen menjëherë pasi të ruhen. Formularët e vizitorëve hapin WhatsApp dhe nuk ruajnë të dhëna personale në këtë panel.</p>
    </main>
  <?php endif; ?>
</body></html>
