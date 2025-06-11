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
            $query = "INSERT INTO Profile
                        (user_id, first_name, last_name, email, headline, summary, url)
                    VALUES
                        (:user_id, :first_name, :last_name, :email, :headline, :summary, :url)";
            $stmt = $pdo->prepare($query);

            $stmt->execute([
                ':user_id' => $userLoggedId,
                ':first_name' => htmlentities($firstName),
                ':last_name' => htmlentities($lastName),
                ':email' => htmlentities($email),
                ':headline' => htmlentities($headline),
                ':summary' => htmlentities($summary),
                ':url' => !empty($url) ? $url : null,
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

    <h2 class="text-center mb-5">Adding Profile for <?php echo $userLoggedName ?></h2>

    <div class="d-flex justify-content-end mb-3">
        <a href="index.php" class="btn btn-secondary ms-2">Back to list</a>
    </div>

    <div class="row my-5 border p-3 rounded">

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center mb-3"><?php echo $error; ?></div>
        <?php endif; ?></php>

        <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            <div class="form-group mb-3">
                <label for="firstname" class="form-label">First Name</label>
                <input type="text" class="form-control" id="firstname" name="first_name" value="<?php echo (!empty($firstName)) ? $firstName : '' ?>">
            </div>
            <div class="form-group mb-3">
                <label for="lastname" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="lastname" name="last_name" value="<?php echo (!empty($lastName)) ? $lastName : '' ?>">
            </div>
            <div class="form-group mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="text" class="form-control" id="email" name="email" value="<?php echo (!empty($email)) ? $email : '' ?>">
            </div>
            <div class="form-group mb-3">
                <label for="headline" class="form-label">Headline</label>
                <input type="text" class="form-control" id="headline" name="headline" value="<?php echo (!empty($headline)) ? $headline : '' ?>">
            </div>
            <div class="form-group mb-3">
                <label for="summary" class="form-label">Summary</label>
                <textarea class="form-control" id="summary" name="summary" rows="5"><?php echo (!empty($summary)) ? $summary : '' ?></textarea>
            </div>
            <div class="form-group mb-5">
                <label for="url" class="form-label">Photo URL (optional)</label>
                <input type="text" class="form-control" id="url" name="url">
            </div>

            <div class="d-flex mt-3 justify-content-start">
                <button class="btn btn-primary" type="submit" value="Add">Add</button>
                <a href="index.php" class="btn btn-secondary ms-2">Cancel</a>
            </div>
        </form>

    </div>

    <script src="/assets/js/profile.js"></script>

<?php include '../includes/footer.php'; ?>