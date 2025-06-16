<?php
include '../config/auth.php';
include '../config/db.php';
include '../includes/header.php';

if ($userLogged === false) {
    $_SESSION['alert'] = [
        'type' => 'danger',
        'message' => 'Access denied: You need to be logged in',
    ];
    header("Location: index.php");
    exit;
}

if (empty($_GET['profile_id'])) {
    $_SESSION['alert'] = [
        'type' => 'danger',
        'message' => 'No profile selected',
    ];
    header("Location: index.php");
    exit;
}

$profileId = filter_input(INPUT_GET, 'profile_id', FILTER_VALIDATE_INT);

if ($profileId === false) {
    $_SESSION['alert'] = [
        'type' => 'danger',
        'message' => 'Invalid profile ID',
    ];
    header("Location: index.php");
    exit;
}

// Get Profile
$stmt = $pdo->prepare("SELECT * FROM Profile WHERE profile_id = :profile_id AND user_id = :user_id");
$stmt->execute([
    ':profile_id' => $profileId,
    ':user_id' => $userLoggedId,
]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if ($profile === false) {
    $_SESSION['alert'] = [
        'type' => 'danger',
        'message' => 'This profile does not belong to the logged in user',
    ];
    header("Location: index.php");
    exit;
}

$firstName = $profile['first_name'] ?? '';
$lastName = $profile['last_name'] ?? '';
$email = $profile['email'] ?? '';
$headline = $profile['headline'] ?? '';
$summary = $profile['summary'] ?? '';
$url = $profile['url'] ?? '';

// Get all positions
$stmt = $pdo->prepare("SELECT * FROM Position WHERE profile_id = :profile_id");
$stmt->execute([
    ':profile_id' => $profileId,
]);
$positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalPositionFields = count($positions);
?>

    <h2 class="text-center mb-5">Profile Information</h2>

    <div class="d-flex justify-content-end mb-3">
        <?php if ($profile['user_id'] == $userLoggedId): ?>
            <a href="edit.php?profile_id=<?php echo $profile['profile_id'] ?>" class="btn btn-dark ms-2">Edit</a>
            <a href="delete.php?profile_id=<?php echo $profile['profile_id'] ?>" class="btn btn-danger ms-2">Delete</a>
        <?php endif; ?>
        <a href="index.php" class="btn btn-secondary ms-2">Back to list</a>
    </div>

    <div class="row my-5 border p-3 rounded">
    <div class="col-md-12">

        <div class="form-group mb-3">
            <label for="firstname" class="form-label"><strong>First Name:</strong></label>
            <div><?php echo $firstName ?></div>
        </div>
        <div class="form-group mb-3">
            <label for="lastname" class="form-label"><strong>Last Name:</strong></label>
            <div><?php echo $lastName ?></div>
        </div>
        <div class="form-group mb-3">
            <label for="email" class="form-label"><strong>Email:</strong></label>
            <div><?php echo $email ?></div>
        </div>
        <div class="form-group mb-3">
            <label for="headline" class="form-label"><strong>Headline:</strong></label>
            <div><?php echo $headline ?></div>
        </div>
        <div class="form-group mb-3">
            <label for="summary" class="form-label"><strong>Summary:</strong></label>
            <div><?php echo nl2br(htmlspecialchars($summary)) ?></div>
        </div>
        <?php if (!empty($url)): ?>
            <div class="form-group mb-3">
                <label for="photo" class="form-label"><strong>Photo:</strong></label>
                <div><img src="<?php echo htmlspecialchars($url) ?>" class="img-fluid" alt="Photo"></div>
            </div>
        <?php endif; ?>

        <?php if ($totalPositionFields) : ?>
            <hr />

            <h5 class="mt-4 mb-3">
                Positions
            </h5>

            <div id="position_fields">

                <?php foreach ($positions as $data) : ?>
                    <div class="form-group mb-3">
                        <label for="year" class="form-label"><strong>Year:</strong> <?php echo $data['year'] ?></label>
                        <div><?php echo nl2br(htmlspecialchars($data['description'])) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php include '../includes/footer.php'; ?>