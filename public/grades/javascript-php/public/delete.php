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

$stmt = $pdo->prepare("SELECT * FROM Profile WHERE profile_id = :profile_id AND user_id = :user_id");
$stmt->execute([
    ':profile_id' => $profileId,
    ':user_id' => $userLoggedId
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("DELETE FROM Profile WHERE profile_id = :profile_id AND user_id = :user_id");
    $stmt->execute([
        ':profile_id' => $profileId,
        ':user_id' => $userLoggedId
    ]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['alert'] = [
            'type' => 'success',
            'message' => 'Profile deleted successfully',
        ];
    } else {
        $_SESSION['alert'] = [
            'type' => 'danger',
            'message' => 'You are not authorized to delete this profile.',
        ];
    }

    header("Location: index.php");
    exit;
}
?>

    <h2 class="text-center mb-5">Deleteing Profile</h2>

    <div class="d-flex justify-content-end mb-3">
        <a href="index.php" class="btn btn-secondary ms-2">Back to list</a>
    </div>

    <div class="row my-5 border p-3 rounded">

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center mb-3"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>?profile_id=<?php echo $profileId ?>">
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
                <div class="form-group mb-5">
                    <label for="photo" class="form-label"><strong>Photo:</strong></label>
                    <div><img src="<?php echo htmlspecialchars($profile['url']) ?>" class="img-fluid" alt="Photo"></div>
                </div>
            <?php endif; ?>

            <div class="d-flex mt-3 justify-content-start">
                <button class="btn btn-danger" type="submit">Delete</button>
                <a href="index.php" class="btn btn-secondary ms-2">Cancel</a>
            </div>
        </form>

    </div>

<?php include '../includes/footer.php'; ?>