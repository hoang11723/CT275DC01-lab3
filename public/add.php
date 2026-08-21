<?php
require_once __DIR__ . '/../src/bootstrap.php';

use CT275\Labs\Contact;

$errors = [];
$contact = new Contact($PDO);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contactData = [
        'name'  => $_POST['name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'notes' => $_POST['notes'] ?? '',
    ];

    // Xử lý Upload Avatar
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
    $subtitle = 'Add a new contact here.';
    include_once __DIR__ . '/../src/partials/heading.php';
    ?>

    <div class="row">
      <div class="col-md-8 offset-md-2">
        <form action="/add.php" method="POST" enctype="multipart/form-data">
          
          <div class="mb-3">
            <label for="avatar" class="form-label">Avatar</label>
            <input type="file" name="avatar" class="form-control" id="avatar" accept="image/*">
            <div class="mt-2">
              <img id="avatar-preview" src="#" alt="Preview Avatar" class="img-thumbnail d-none" style="max-height: 120px;">
            </div>
          </div>

          <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" id="name" placeholder="Enter Name" value="<?= html_escape($_POST['name'] ?? '') ?>">
            <?php if (isset($errors['name'])): ?>
              <div class="invalid-feedback"><?= $errors['name'] ?></div>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" id="phone" placeholder="Enter Phone" value="<?= html_escape($_POST['phone'] ?? '') ?>">
            <?php if (isset($errors['phone'])): ?>
              <div class="invalid-feedback"><?= $errors['phone'] ?></div>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea name="notes" class="form-control <?= isset($errors['notes']) ? 'is-invalid' : '' ?>" id="notes" placeholder="Enter Notes (The note must not exceed 255 characters.)" rows="3"><?= html_escape($_POST['notes'] ?? '') ?></textarea>
            <?php if (isset($errors['notes'])): ?>
              <div class="invalid-feedback"><?= $errors['notes'] ?></div>
            <?php endif; ?>
          </div>

          <button type="submit" class="btn btn-primary">Add Contact</button>
          <a href="/" class="btn btn-secondary">Cancel</a>
        </form>
      </div>
    </div>
  </div>

  <?php include_once __DIR__ . '/../src/partials/footer.php' ?>

  <!-- JS preview ảnh trước khi upload -->
  <script>
    document.getElementById('avatar').addEventListener('change', function(e) {
      const file = e.target.files[0];
      const preview = document.getElementById('avatar-preview');
      if (file) {
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('d-none');
      } else {
        preview.classList.add('d-none');
      }
    });
  </script>
</body>
</html>