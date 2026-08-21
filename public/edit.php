<?php
require_once __DIR__ . '/../src/bootstrap.php';

use CT275\Labs\Contact;

$contact = new Contact($PDO);

// Lấy id từ URL hoặc form submit
$id = isset($_REQUEST['id']) ? filter_var($_REQUEST['id'], FILTER_VALIDATE_INT) : false;

// Nếu không có id hoặc không tìm thấy contact, chuyển hướng về trang chủ
if (!$id || !($contact->find($id))) {
    redirect('/');
}

$errors = [];

// Xử lý khi người dùng submit form (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contactData = [
        'name'  => $_POST['name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'notes' => $_POST['notes'] ?? '',
    ];

    $errors = $contact->validate($contactData);

    if (empty($errors)) {
        $contact->fill($contactData);
        if ($contact->save()) {
            redirect('/');
        }
    }
}

include_once __DIR__ . '/../src/partials/header.php';
?>

<body>
  <?php include_once __DIR__ . '/../src/partials/navbar.php' ?>

  <div class="container">
    <?php
    $subtitle = 'Edit contact details.';
    include_once __DIR__ . '/../src/partials/heading.php';
    ?>

    <div class="row">
      <div class="col-md-8 offset-md-2">
        <form action="/edit.php?id=<?= $contact->id ?>" method="POST">
          <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" id="name" value="<?= html_escape($contact->name) ?>">
            <?php if (isset($errors['name'])): ?>
              <div class="invalid-feedback"><?= $errors['name'] ?></div>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" id="phone" value="<?= html_escape($contact->phone) ?>">
            <?php if (isset($errors['phone'])): ?>
              <div class="invalid-feedback"><?= $errors['phone'] ?></div>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea name="notes" class="form-control <?= isset($errors['notes']) ? 'is-invalid' : '' ?>" id="notes" rows="3"><?= html_escape($contact->notes) ?></textarea>
            <?php if (isset($errors['notes'])): ?>
              <div class="invalid-feedback"><?= $errors['notes'] ?></div>
            <?php endif; ?>
          </div>

          <button type="submit" class="btn btn-primary">Update Contact</button>
          <a href="/" class="btn btn-secondary">Cancel</a>
        </form>
      </div>
    </div>
  </div>

  <?php include_once __DIR__ . '/../src/partials/footer.php' ?>
</body>
</html>