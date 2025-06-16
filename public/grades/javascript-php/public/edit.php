<?php
include '../config/auth.php';
include '../config/db.php';
include '../config/functions.php';
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
    // Validate
    $firstName = trim($_POST['first_name'] ?? null);
    $lastName = trim($_POST['last_name'] ?? null);
    $email = trim($_POST['email'] ?? null);
    $headline = trim($_POST['headline'] ?? null);
    $summary = trim($_POST['summary'] ?? null);
    $url = trim($_POST['url']);

    if (empty($firstName) || empty($lastName) || empty($email) || empty($headline) || empty($summary)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (!empty($url) && !preg_match('/^https?:\/\/.+$/', $url)) {
        $error = 'Invalid URL. Must start with http:// or https://';
    } elseif (!empty($url) && !isUrlAccessible($url)) {
        $error = 'The provided image URL is not accessible.';
    } else {
        try {
            $query = "UPDATE Profile SET
                        first_name = :first_name,
                        last_name = :last_name,
                        email = :email,
                        headline = :headline,
                        summary = :summary,
                        url = :url
                    WHERE profile_id = :profile_id AND user_id = :user_id";

            $stmt = $pdo->prepare($query);

            $stmt->execute([
                ':first_name' => htmlentities($firstName),
                ':last_name' => htmlentities($lastName),
                ':email' => htmlentities($email),
                ':headline' => htmlentities($headline),
                ':summary' => htmlentities($summary),
                ':url' => !empty($url) ? $url : null,
                ':profile_id' => $profileId,
                ':user_id' => $userLoggedId,
            ]);

            $_SESSION['alert'] = [
                'type' => 'success',
                'message' => 'Profile added',
            ];
            header("Location: index.php");
            exit;

        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

    <h2 class="text-center mb-5">Editing Profile for <?php echo $userLoggedName ?></h2>

    <div class="d-flex justify-content-end mb-3">
        <a href="index.php" class="btn btn-secondary ms-2">Back to list</a>
    </div>

    <div class="row my-5 border p-3 rounded">

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center mb-3"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>?profile_id=<?php echo $profileId ?>">
            <div class="form-group mb-3">
                <label for="firstname" class="form-label">First Name</label>
                <input type="text" class="form-control" id="firstname" name="first_name" value="<?php echo $profile['first_name'] ?>">
            </div>
            <div class="form-group mb-3">
                <label for="lastname" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="lastname" name="last_name" value="<?php echo $profile['last_name'] ?>">
            </div>
            <div class="form-group mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="text" class="form-control" id="email" name="email" value="<?php echo $profile['email'] ?>">
            </div>
            <div class="form-group mb-3">
                <label for="headline" class="form-label">Headline</label>
                <input type="text" class="form-control" id="headline" name="headline" value="<?php echo $profile['headline'] ?>">
            </div>
            <div class="form-group mb-3">
                <label for="summary" class="form-label">Summary</label>
                <textarea class="form-control" id="summary" name="summary" rows="5"><?php echo htmlspecialchars($profile['summary']); ?></textarea>
            </div>
            <div class="form-group mb-5">
                <label for="url" class="form-label">Photo URL (optional)</label>
                <input type="text" class="form-control" id="url" name="url" value="<?php echo isset($profile['url']) ? htmlspecialchars($profile['url']) : ''; ?>">
            </div>

            <div class="d-flex mt-3 justify-content-start">
                <button class="btn btn-primary" type="submit">Save</button>
                <a href="index.php" class="btn btn-secondary ms-2">Cancel</a>
            </div>
        </form>

    </div>

    <script src="../public/assets/js/profile.js"></script>

<?php include '../includes/footer.php'; ?>