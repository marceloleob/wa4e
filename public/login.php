<?php
include '../config/auth.php';
include '../config/db.php';
include '../includes/header.php';

$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? null;
    $password = $_POST['pass'] ?? null;

    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
    } else {

        // Create hash with salt
        $checkPassword = hash('md5', 'XyZzy12*_' . $password);

        try {
            $stmt = $pdo->prepare("SELECT user_id, name FROM users WHERE email = :email AND password = :pwd");
            $stmt->execute([':email' => $email, ':pwd' => $checkPassword]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user !== false) {
                $_SESSION['name'] = $user['name'];
                $_SESSION['user_id'] = $user['user_id'];

                session_write_close();

                header("Location: index.php");
                exit();

            } else {
                $error = "Incorrect email or password.";
            }

        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<div class="d-flex justify-content-center align-items-center my-5">
    <main class="form-signin m-auto border p-3 rounded" style="width: 450px;">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
        <?php endif; ?></php>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            <h1 class="h3 mb-2 fw-normal">Please sign in</h1>
            <div class="form-text mb-4">For a password hint, view source and find an account and password hint in the HTML comments.</div>

            <div class="form-floating py-2">
                <input type="text" name="email" id="email" class="form-control" id="floatingInput" placeholder="name@example.com">
                <label for="floatingInput">Email address</label>
                <div class="d-none">Hint: The correct email is <code>umsi@umich.edu</code></div>
            </div>
            <div class="form-floating py-2">
                <input type="password" name="pass" id="password" class="form-control" id="floatingPassword" placeholder="Password">
                <label for="floatingPassword">Password</label>
                <div class="d-none">Hint: The correct password is <code>php123</code></div>
            </div>

            <div class="d-flex mt-3 justify-content-end">
                <input type="submit" value="Log In" class="btn btn-primary">
                <a href="index.php" class="btn btn-secondary ms-2">Cancel</a>
            </div>
        </form>
    </main>
</div>

<script src="/assets/js/login.js"></script>

<?php include '../includes/footer.php'; ?>