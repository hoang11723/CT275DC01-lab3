<?php
require_once __DIR__ . '/../src/bootstrap.php';

use CT275\Labs\Contact;

$contact = new Contact($PDO);

$id = isset($_REQUEST['id']) ? filter_var($_REQUEST['id'], FILTER_VALIDATE_INT) : false;

if (!$id || !($contact->find($id))) {
    redirect('/');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contactData = [
        'name'   => $_POST['name'] ?? '',
        'phone'  => $_POST['phone'] ?? '',
        'notes'  => $_POST['notes'] ?? '',
        'avatar' => $contact->avatar // Giữ đường dẫn avatar cũ làm mặc định
    ];

    // Nếu chọn file mới thì xử lý upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['avatar']['tmp_name'];
        $fileName = time() . '_' . basename($_FILES['avatar']['name']);
        $uploadDir = __DIR__ . '/uploads/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destPath = $uploadDir . $fileName;
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $contactData['avatar'] = '/uploads/' . $fileName;
        }
    }

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

  <div class="container my-4">
    <?php
    $subtitle = 'Edit contact details.';
    include_once __DIR__ . '/../src/partials/heading.php';
    ?>

    <div class="row">
      <div class="col-md-8 offset-md-2">
        <form action="/edit.php?id=<?= $contact->id ?>" method="POST" enctype="multipart/form-data">
          
          <div class="mb-3">
            <label for="avatar" class="form-label">Avatar</label>
            <input type="file" name="avatar" class="form-control" id="avatar" accept="image/*">
            <div class="mt-2">
              <img id="avatar-preview" src="<?= $contact->avatar ? html_escape($contact->avatar) : '#' ?>" alt="Preview Avatar" class="img-thumbnail <?= $contact->avatar ? '' : 'd-none' ?>" style="max-height: 120px;">
            </div>
          </div>

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

  <script>
    document.getElementById('avatar').addEventListener('change', function(e) {
      const file = e.target.files[0];
      const preview = document.getElementById('avatar-preview');
      if (file) {
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('d-none');
      }
    });
  </script>
</body>
</html>