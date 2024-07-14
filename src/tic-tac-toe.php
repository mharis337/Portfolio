<?php
session_start();

function displayBoard() {
    $board = isset($_SESSION['board']) ? $_SESSION['board'] : array_fill(0, 9, null);
    echo '<div class="tic-tac-toe">';
    for ($i = 0; $i < 9; $i++) {
        $value = $board[$i];
        $class = $value ? strtolower($value) : '';
        echo '<div class="cell ' . $class . '" id="' . $i . '"></div>';
    }
    echo '</div>';
}

function displayEndScreen() {
    echo '<div class="end_screen hidden">';
    echo '<div class="results"></div>';
    echo '<button class="restart">Restart Game</button>';
    echo '</div>';
}

function displayTitle() {
    $turn = isset($_SESSION['turn']) ? $_SESSION['turn'] : 'X';
    echo '<div class="title">It is ' . $turn . '\'s Turn</div>';
}

function displayScores() {
    $scores = isset($_SESSION['scores']) ? $_SESSION['scores'] : array('X' => 0, 'O' => 0, 'draws' => 0);
    echo '<div class="scores">';
    echo '<p>Player X Wins: ' . $scores['X'] . '</p>';
    echo '<p>Player O Wins: ' . $scores['O'] . '</p>';
    echo '<p>Draws: ' . $scores['draws'] . '</p>';
    echo '</div>';
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tic-Tac-Toe Game</title>
    <link rel="stylesheet" type="text/css" href="/global.css">
    <link rel="stylesheet" type="text/css" href="/styles/styleProjectPage.css" />
    <script src="/public/assets/tic-tac-toe.js" async defer></script>
</head>
<header>
    <div class="navbar">
      <ul>
        <li><a href="/">Home</a></li>
        <li><a href="./project.html">Projects</a></li>
        <li><a href="./resume.html">Resume</a></li>
      </ul>
    </div>
  </header>
<body>
    <div class="display">
    <?php displayTitle(); ?>
    <?php displayBoard(); ?>
    <?php displayEndScreen(); ?>
    <?php displayScores(); ?>

    </div>
</body>
</html>
