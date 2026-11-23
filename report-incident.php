<?php
require_once __DIR__ . '/includes/auth.php';

$errors = [];
$successMessage = '';
$uploadedImagePath = null;
$uploadedAbsolutePath = null;

$formData = [
    'reporter_name' => '',
    'reporter_contact' => '',
    'incident_type' => '',
    'description' => '',
    'location' => '',
];

$incidentTypes = [
    'flood' => 'Flood',
    'fire' => 'Fire',
    'landslide' => 'Landslide',
    'road_obstruction' => 'Road obstruction',
    'other' => 'Other',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    foreach ($formData as $field => $defaultValue) {
        $formData[$field] = trim((string) ($_POST[$field] ?? $defaultValue));
    }

    if ($formData['reporter_name'] === '' || strlen($formData['reporter_name']) > 100) {
        $errors[] = 'Enter your name (maximum 100 characters).';
    }

    if ($formData['reporter_contact'] !== '' && strlen($formData['reporter_contact']) > 20) {
        $errors[] = 'Contact number must be 20 characters or fewer.';
    }

    if (!array_key_exists($formData['incident_type'], $incidentTypes)) {
        $errors[] = 'Select a valid incident type.';
    }

    if ($formData['description'] === '' || strlen($formData['description']) > 10000) {
        $errors[] = 'Provide an incident description (maximum 10,000 characters).';
    }

    if ($formData['location'] === '' || strlen($formData['location']) > 255) {
        $errors[] = 'Provide the incident location (maximum 255 characters).';
    }

    $allowedImages = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = '';

    if (isset($_FILES['incident_image']) && $_FILES['incident_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['incident_image']['error'] !== UPLOAD_ERR_OK || $_FILES['incident_image']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'The image must be 5 MB or smaller and upload successfully.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = (string) $finfo->file($_FILES['incident_image']['tmp_name']);
            if (!isset($allowedImages[$mime])) {
                $errors[] = 'Upload a JPEG, PNG, or WebP image only.';
            }
        }
    }

    if (empty($errors)) {
        try {
            $pdo = db();

            if (isset($_FILES['incident_image']) && $_FILES['incident_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDirectory = __DIR__ . '/storage/incident_uploads';
                if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0750, true) && !is_dir($uploadDirectory)) {
                    throw new RuntimeException('Upload directory could not be created.');
                }
                $fileName = bin2hex(random_bytes(16)) . '.' . $allowedImages[$mime];
                $uploadedAbsolutePath = $uploadDirectory . '/' . $fileName;

                if (!move_uploaded_file($_FILES['incident_image']['tmp_name'], $uploadedAbsolutePath)) {
                    throw new RuntimeException('Image could not be saved.');
                }
                $uploadedImagePath = 'storage/incident_uploads/' . $fileName;
            }

            $insertIncident = $pdo->prepare(
                "INSERT INTO incidents
                    (reported_by, reporter_name, reporter_contact, incident_type, description, location, image_path, status)
                 VALUES
                    (NULL, :reporter_name, :reporter_contact, :incident_type, :description, :location, :image_path, 'reported')"
            );

            $insertIncident->execute([
                'reporter_name' => $formData['reporter_name'],
                'reporter_contact' => $formData['reporter_contact'] !== '' ? $formData['reporter_contact'] : null,
                'incident_type' => $formData['incident_type'],
                'description' => $formData['description'],
                'location' => $formData['location'],
                'image_path' => $uploadedImagePath,
            ]);

            $newId = (int) $pdo->lastInsertId();
            $refCode = formatRef('INC', $newId);

            $successMessage = "Your incident report has been submitted. Reference Number: {$refCode}. Keep this reference number for follow-up.";

            $formData = [
                'reporter_name' => '',
                'reporter_contact' => '',
                'incident_type' => '',
                'description' => '',
                'location' => '',
            ];
        } catch (Throwable $exception) {
            if ($uploadedAbsolutePath !== null && is_file($uploadedAbsolutePath)) {
                unlink($uploadedAbsolutePath);
            }
            error_log($exception->getMessage());
            $errors[] = 'The report could not be submitted. Please try again later or contact the barangay directly.';
        }
    }
}

$activePage = 'report-incident.php';
$pageTitle = 'Report an Incident';
require __DIR__ . '/includes/public-page-start.php';
?>

<div class="section-head">
    <div>
        <div class="kicker">Community reporting</div>
        <h2>Submit an Incident Report</h2>
    </div>
    <p>
        This form is open to the public. You do not need an account.
        Do not use this form instead of contacting emergency services during immediate danger.
    </p>
</div>

<div class="panel" style="max-width: 780px;">
    <?php if ($successMessage !== ''): ?>
        <div class="notice success"><?= h($successMessage) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="notice error">
            <strong>Please correct the following:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= h($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="report-incident.php" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">

        <div class="form-grid">
            <div class="field">
                <label for="reporter_name">Your name *</label>
                <input id="reporter_name" name="reporter_name" maxlength="100" value="<?= h($formData['reporter_name']) ?>" required>
            </div>

            <div class="field">
                <label for="reporter_contact">Contact number (optional)</label>
                <input id="reporter_contact" name="reporter_contact" maxlength="20" value="<?= h($formData['reporter_contact']) ?>">
            </div>

            <div class="field">
                <label for="incident_type">Incident type *</label>
                <select id="incident_type" name="incident_type" required>
                    <option value="">Select type</option>
                    <?php foreach ($incidentTypes as $value => $label): ?>
                        <option value="<?= h($value) ?>" <?= $formData['incident_type'] === $value ? 'selected' : '' ?>>
                            <?= h($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="location">Incident location *</label>
                <input id="location" name="location" maxlength="255" value="<?= h($formData['location']) ?>" placeholder="Street, purok, or landmark" required>
            </div>

            <div class="field full">
                <label for="description">What happened? *</label>
                <textarea id="description" name="description" required><?= h($formData['description']) ?></textarea>
            </div>

            <div class="field full">
                <label for="incident_image">Incident image (optional, JPEG/PNG/WebP, maximum 5 MB)</label>
                <input id="incident_image" name="incident_image" type="file" accept="image/jpeg,image/png,image/webp">
            </div>
        </div>

        <button class="button" type="submit">Submit report</button>
    </form>
</div>

<?php require __DIR__ . '/includes/public-page-end.php'; ?>