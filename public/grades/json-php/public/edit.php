<?php
include '../config/auth.php';
include '../config/db.php';
include '../config/functions.php';
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

// Get all educations
$sqlEducation = "SELECT
                    Education.profile_id,
                    Education.institution_id,
                    Education.education_rank,
                    Education.year,
                    Institution.name AS school
                FROM Education
                INNER JOIN Institution ON (Education.institution_id = Institution.institution_id)
                WHERE Education.profile_id = :profile_id
                ORDER BY Education.education_rank ASC";
$stmt = $pdo->prepare($sqlEducation);
$stmt->execute([
    ':profile_id' => $profileId,
]);
$educations = $stmt->fetchAll(PDO::FETCH_ASSOC);
$countEducationFields = count($educations);
$totalEducationFields = count($educations);

// Get all positions
$sqlPosition = "SELECT
                    *
                FROM Position
                WHERE profile_id = :profile_id
                ORDER BY position_rank ASC";
$stmt = $pdo->prepare($sqlPosition);
$stmt->execute([
    ':profile_id' => $profileId,
]);
$positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
$countPositionFields = count($positions);
$totalPositionFields = count($positions);

$isPost = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $isPost = true;
    $error = null;
    // Validate
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $headline = trim($_POST['headline'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $url = isset($_POST['url']) ? trim($_POST['url']) : '';

    $countEducationFields = intval($_POST['count_education_fields']);
    $totalEducationFields = intval($_POST['total_education_fields']);
    $countPositionFields = intval($_POST['count_position_fields']);
    $totalPositionFields = intval($_POST['total_position_fields']);

    if (empty($firstName) || empty($lastName) || empty($email) || empty($headline) || empty($summary)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (!empty($url) && !preg_match('/^https?:\/\/.+$/', $url)) {
        $error = 'Invalid URL. Must start with http:// or https://';
    } elseif (!empty($url) && !isUrlAccessible($url)) {
        $error = 'The provided image URL is not accessible.';
    // } elseif ($totalEducationFields < 1) {
    //     $error = 'At least one Education is required.';
    // } elseif ($totalPositionFields < 1) {
    //     $error = 'At least one Position is required.';
    } else {
        // Education validate
        $error = validateEducations($_POST, $countEducationFields);
        // Position validate
        $error = validatePositions($_POST, $countPositionFields);

        if (empty($error)) {

            try {
                // Update Profile
                $queryProfile = "UPDATE Profile SET
                            first_name = :first_name,
                            last_name = :last_name,
                            email = :email,
                            headline = :headline,
                            summary = :summary,
                            url = :url
                        WHERE profile_id = :profile_id AND user_id = :user_id";

                $stmtProfile = $pdo->prepare($queryProfile);

                $stmtProfile->execute([
                    ':first_name' => htmlentities($firstName),
                    ':last_name' => htmlentities($lastName),
                    ':email' => htmlentities($email),
                    ':headline' => htmlentities($headline),
                    ':summary' => htmlentities($summary),
                    ':url' => !empty($url) ? $url : null,
                    ':profile_id' => $profileId,
                    ':user_id' => $userLoggedId,
                ]);

                // Clear out the old educations entries
                deleteEducations($pdo, $profileId);
                // Insert Educations
                insertEducations($pdo, $profileId, $_POST);

                // Clear out the old position entries
                deletePositions($pdo, $profileId);
                // Insert Positions
                insertPositions($pdo, $profileId, $_POST);

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
}
?>

    <h2 class="text-center mb-5">Editing Profile for <?php echo $userLoggedName ?></h2>

    <div class="d-flex justify-content-end mb-3">
        <?php if ($profile['user_id'] == $userLoggedId): ?>
            <a href="delete.php?profile_id=<?php echo $profileId ?>" class="btn btn-danger ms-2">Delete</a>
        <?php endif; ?>
        <a href="index.php" class="btn btn-secondary ms-2">Back to list</a>
    </div>

    <div class="row my-5 border p-3 rounded">

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center mb-3"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>?profile_id=<?php echo $profileId ?>">
            <div class="form-group mb-3">
                <label for="firstname" class="form-label">First Name</label>
                <input type="text" class="form-control" id="firstname" name="first_name" value="<?php echo htmlentities($firstName) ?>" autocomplete="false">
            </div>
            <div class="form-group mb-3">
                <label for="lastname" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="lastname" name="last_name" value="<?php echo htmlentities($lastName) ?>" autocomplete="false">
            </div>
            <div class="form-group mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="text" class="form-control" id="email" name="email" value="<?php echo htmlentities($email) ?>" autocomplete="false">
            </div>
            <div class="form-group mb-3">
                <label for="headline" class="form-label">Headline</label>
                <input type="text" class="form-control" id="headline" name="headline" value="<?php echo htmlentities($headline) ?>" autocomplete="false">
            </div>
            <div class="form-group mb-3">
                <label for="summary" class="form-label">Summary</label>
                <textarea class="form-control" id="summary" name="summary" rows="5"><?php echo htmlspecialchars($summary); ?></textarea>
            </div>
            <!--
            <div class="form-group mb-5">
                <label for="url" class="form-label">Photo URL (optional)</label>
                <input type="text" class="form-control" id="url" name="url" value="<?php echo isset($url) ? htmlspecialchars($url) : ''; ?>">
            </div>
            -->

            <hr />

            <h5 class="mt-4 mb-3">
                Education <button type="button" id="addEdu" class="btn btn-outline-dark px-3 ms-2">+</button>
            </h5>

            <div id="education_fields">
                <?php if ($isPost == false && $totalEducationFields > 0) : ?>
                    <?php $count = 1; ?>
                    <?php foreach ($educations as $data) : ?>
                        <div id="education<?php echo $count; ?>" class="education">
                            <div class="row">
                                <div class="col-md-2 form-group mb-3">
                                    <label for="edu_year<?php echo $count; ?>" class="form-label">Year</label>
                                    <input type="text" class="form-control" id="edu_year<?php echo $count; ?>" name="edu_year<?php echo $count; ?>" value="<?php echo $data['year'] ?>" size="4" maxlength="4">
                                </div>
                                <div class="col-md-2 form-group mb-3 align-self-end">
                                    <button type="button" class="btn btn-outline-dark px-3 removeEdu" value="<?php echo $count; ?>">-</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 form-group mb-3">
                                    <label for="edu_school<?php echo $count; ?>" class="form-label">School</label>
                                    <input type="text" name="edu_school<?php echo $count; ?>" class="form-control ui-autocomplete-input school" value="<?php echo $data['school'] ?>" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <?php $count++; ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($isPost == true && $countEducationFields > 0) : ?>
                    <?php for ($i = 1; $i <= $countEducationFields; $i++) : ?>
                        <?php if (isset($_POST['edu_year' . $i]) || isset($_POST['desc' . $i])) : ?>
                            <div id="education<?php echo $i; ?>" class="education">
                                <div class="row">
                                    <div class="col-md-2 form-group mb-3">
                                        <label for="edu_year<?php echo $i; ?>" class="form-label">Year</label>
                                        <input type="text" class="form-control" name="edu_year<?php echo $i; ?>" value="<?php echo $_POST['edu_year' . $i] ?>" size="4" maxlength="4">
                                    </div>
                                    <div class="col-md-2 form-group mb-3 align-self-end">
                                        <button type="button" class="btn btn-outline-dark px-3 removeEdu" value="<?php echo $i; ?>">-</button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 form-group mb-3">
                                        <label for="edu_school<?php echo $i; ?>" class="form-label">School</label>
                                        <input type="text" name="edu_school<?php echo $i; ?>" class="form-control ui-autocomplete-input school" value="<?php echo $_POST['edu_school' . $i] ?>" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
            <input type="hidden" name="count_education_fields" id="count_education_fields" value="<?php echo ($countEducationFields > 0) ? $countEducationFields : 0 ?>" />
            <input type="hidden" name="total_education_fields" id="total_education_fields" value="<?php echo ($totalEducationFields > 0) ? $totalEducationFields : 0 ?>" />

            <hr />

            <h5 class="mt-4 mb-3">
                Positions <button type="button" id="addPos" class="btn btn-outline-dark px-3 ms-2">+</button>
            </h5>

            <div id="position_fields">
                <?php if ($isPost == false && $totalPositionFields) : ?>
                    <?php $count = 1; ?>
                    <?php foreach ($positions as $data) : ?>
                        <div id="position<?php echo $count; ?>" class="position">
                            <div class="row">
                                <div class="col-md-2 form-group mb-3">
                                    <label for="year<?php echo $count; ?>" class="form-label">Year</label>
                                    <input type="text" class="form-control" id="year<?php echo $count; ?>" name="year<?php echo $count; ?>" value="<?php echo $data['year'] ?>" size="4" maxlength="4">
                                </div>
                                <div class="col-md-2 form-group mb-3 align-self-end">
                                    <button type="button" class="btn btn-outline-dark px-3 removePos" value="<?php echo $count; ?>">-</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 form-group mb-3">
                                    <textarea class="form-control" name="desc<?php echo $count; ?>" rows="5"><?php echo $data['description'] ?></textarea>
                                </div>
                            </div>
                        </div>
                        <?php $count++; ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($isPost == true && $countPositionFields > 0) : ?>
                    <?php for ($i = 1; $i <= $countPositionFields; $i++) : ?>
                        <?php if (isset($_POST['year' . $i]) || isset($_POST['desc' . $i])) : ?>
                            <div id="position<?php echo $i; ?>" class="position">
                                <div class="row">
                                    <div class="col-md-2 form-group mb-3">
                                        <label for="year<?php echo $i; ?>" class="form-label">Year</label>
                                        <input type="text" class="form-control" name="year<?php echo $i; ?>" value="<?php echo $_POST['year' . $i] ?>" size="4" maxlength="4">
                                    </div>
                                    <div class="col-md-2 form-group mb-3 align-self-end">
                                        <button type="button" class="btn btn-outline-dark px-3 removePos" value="<?php echo $i; ?>">-</button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 form-group mb-3">
                                        <textarea class="form-control" name="desc<?php echo $i; ?>" rows="5"><?php echo $_POST['desc' . $i] ?></textarea>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
            <input type="hidden" name="count_position_fields" id="count_position_fields" value="<?php echo ($countPositionFields > 0) ? $countPositionFields : 0 ?>" />
            <input type="hidden" name="total_position_fields" id="total_position_fields" value="<?php echo ($totalPositionFields > 0) ? $totalPositionFields : 0 ?>" />

            <hr />

            <div class="d-flex mt-3 justify-content-start">
                <button class="btn btn-primary" type="submit" value="Save">Save</button>
                <a href="index.php" class="btn btn-secondary ms-2">Cancel</a>
            </div>
        </form>

    </div>

    <script src="../public/assets/js/profile.js"></script>

<?php include '../includes/footer.php'; ?>