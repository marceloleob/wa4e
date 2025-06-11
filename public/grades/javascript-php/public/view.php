<?php
include '../config/auth.php';
include '../config/db.php';
include '../includes/header.php';

if ($userLogged === false) {
    $_SESSION['alert'] = [
        'type' => 'danger',
        'message' => 'You need to be logged in',
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

$stmt = $pdo->prepare("SELECT * FROM Profile WHERE profile_id = :profile_id");
$stmt->execute([':profile_id' => $profileId]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if ($profile === false) {
    $_SESSION['alert'] = [
        'type' => 'danger',
        'message' => 'This profile does not belong to the logged in user',
    ];
    header("Location: index.php");
    exit;
}
?>

    <h2 class="text-center mb-5">Profile information</h2>

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
            <div><?php echo htmlspecialchars($profile['first_name']) ?></div>
        </div>
        <div class="form-group mb-3">
            <label for="lastname" class="form-label"><strong>Last Name:</strong></label>
            <div><?php echo htmlspecialchars($profile['last_name']) ?></div>
        </div>
        <div class="form-group mb-3">
            <label for="email" class="form-label"><strong>Email:</strong></label>
            <div><?php echo htmlspecialchars($profile['email']) ?></div>
        </div>
        <div class="form-group mb-3">
            <label for="headline" class="form-label"><strong>Headline:</strong></label>
            <div><?php echo htmlspecialchars($profile['headline']) ?></div>
        </div>
        <div class="form-group mb-3">
            <label for="summary" class="form-label"><strong>Summary:</strong></label>
            <div><?php echo nl2br(htmlspecialchars($profile['summary'])) ?></div>
        </div>
        <?php if (!empty($profile['url'])): ?>
            <div class="form-group mb-3">
                <label for="photo" class="form-label"><strong>Photo:</strong></label>
                <div><img src="<?php echo htmlspecialchars($profile['url']) ?>" class="img-fluid" alt="Photo"></div>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php include '../includes/footer.php'; ?>