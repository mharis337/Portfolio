<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: index.html');
    exit();
}

require '../public/db/db.php';

try {
    $stmt = $conn->prepare('SELECT * FROM queue');
    $stmt->execute();
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching patients: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
    <link rel="stylesheet" href="/styles/ER.css">
    <link rel="stylesheet" type="text/css" href="/styles/styleProjectPage.css" />
</head>
<header>
    <div class="navbar">
      <ul>
        <li><a href="/">Home</a></li>
        <li><a href="/src/project.html">Projects</a></li>
        <li><a href="/src/resume.html">Resume</a></li>
      </ul>
    </div>
  </header>
<body>
    <nav class="nav">
        <p class="logo"><a href="/src/ER.html">City Hospital</a></p>
        <div class="buttons">
            <div><a href="/src/ER.html" class="logout">Logout</a></div>
        </div>
    </nav>

    <div class="hero">
        <div class="hero-container-admin">
            <p class="title-admin">Welcome to the Admin Dashboard</p>
            <p class="description-admin">Here you can manage the patients.</p>
        </div>
    </div>

    <div class="container">
        <div class="patient-list">
            <p class="title">Patient Queue</p>
            <table class="description">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Injury</th>
                        <th>Severity</th>
                        <th>Wait Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($patients as $patient): ?>
                    <tr id="patient-<?= $patient['id'] ?>">
                        <td style="padding-left: 30px;"><?= $patient['id'] ?></td>
                        <td style="padding-left: 30px;"><?= $patient['name'] ?></td>
                        <td style="padding-left: 30px;"><?= $patient['injury'] ?></td>
                        <td style="padding-left: 30px;"><?= $patient['severity'] ?></td>
                        <td class="patient-wait-time" style="padding-left: 30px;"><?= $patient['wait_time'] ?></td>
                        <td class="admin-buttons">
                            <a href="#" class="admit-button" data-id="<?= $patient['id'] ?>">Admit</a>
                            <a href="#" class="remove-button" data-id="<?= $patient['id'] ?>">Remove</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="/public/assets/ER.js"></script>
    <script src="/public/assets/admin_script.js"></script>
</body>
</html>
