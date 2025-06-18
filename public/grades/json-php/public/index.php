<?php
include '../config/auth.php';
include '../config/db.php';
include '../includes/header.php';

// Pagination
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;
// Search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchParam = "%{$search}%";

$countSql = "SELECT COUNT(*) FROM Profile";
$sql = "SELECT * FROM Profile";
$where = "";
$order = " ORDER BY profile_id DESC";
$params = [];

if (!empty($search)) {
    $where = " WHERE first_name LIKE :search
                OR last_name LIKE :search
                OR email LIKE :search
                OR headline LIKE :search
                OR summary LIKE :search";
    $params[':search'] = $searchParam;
}

$countStmt = $pdo->prepare($countSql . $where);
$countStmt->execute($params);
$totalProfiles = $countStmt->fetchColumn();
$totalPages = ceil($totalProfiles / $limit);

$stmt = $pdo->prepare($sql . $where . $order . " LIMIT :limit OFFSET :offset");

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_STR);
}

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$alert = null;

if (!empty($_SESSION['alert'])) {
    $alert = [
        'type' => $_SESSION['alert']['type'],
        'message' => $_SESSION['alert']['message'],
    ];
    unset($_SESSION['alert']);
}
?>

    <?php if ($userLogged): ?>
        <h2 class="text-center mb-5"><?php echo $userLoggedName ?>'s Resume Registry</h2>
    <?php else: ?>
        <h2 class="text-center mb-5">All Resume Registry</h2>
    <?php endif; ?>

    <?php if (!empty($alert)): ?>
        <div class="alert alert-<?php echo $alert['type'] ?>" role="alert" id="alert">
            <?php echo $alert['message'] ?>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-end mb-3">
        <?php if ($userLogged): ?>
            <a href="add.php" class="btn btn-success">Add New Entry</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-primary">Please log in</a>
        <?php endif; ?>
    </div>

    <?php if (count($rows) > 0): ?>
        <form method="get" class="mb-3">
            <div class="row">
                <div class="col-12 text-center">
                    <div class="input-group">
                        <input type="text" class="form-control" id="search" name="search" placeholder="Search profiles" value="<?php echo htmlspecialchars($search) ?>">
                        <button class="btn btn-secondary me-2" type="submit">Search</button>
                        <?php if (!empty($search)): ?>
                            <a href="index.php" class="btn btn-secondary">Reset</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>

    <?php if (count($rows) > 0): ?>
        <table class="table table-striped table-bordered">
            <thead class="table-secondary">
                <tr>
                    <th class="text-center" style="width: 230px;">Name</th>
                    <th class="text-center">Headline</th>
                    <?php if ($userLogged): ?>
                        <th class="text-center" style="width: 178px;">Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td class="align-middle"><?php echo $row['first_name'] ?></td>
                    <td class="align-middle"><?php echo $row['headline'] ?></td>
                    <?php if ($userLogged): ?>
                        <td class="align-middle text-start">
                            <a href="view.php?profile_id=<?php echo $row['profile_id'] ?>" class="btn btn-info btn-sm">View</a>
                            <?php if ($row['user_id'] == $userLoggedId): ?>
                                <a href="edit.php?profile_id=<?php echo $row['profile_id'] ?>" class="btn btn-dark btn-sm">Edit</a>
                                <a href="delete.php?profile_id=<?php echo $row['profile_id'] ?>" class="btn btn-danger btn-sm">Delete</a>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?search=<?php echo urlencode($search) ?>&page=<?php echo $page - 1 ?>">Previous</a></li>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <li class="page-item"><a class="page-link" href="?search=<?php echo urlencode($search) ?>&page=<?php echo $page + 1 ?>">Next</a></li>
                <?php endif; ?>
            </ul>
        </nav>

    <?php else: ?>
      <div class="alert alert-warning text-center">No records found.</div>
    <?php endif; ?>

    <script src="../public/assets/js/index.js"></script>

<?php include '../includes/footer.php'; ?>