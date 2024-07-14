<?php
session_start();

function initializeGame() {
    if (!isset($_SESSION['scores'])) {
        $_SESSION['scores'] = [
            'X' => 0,
            'O' => 0,
            'draws' => 0
        ];
    }
    $_SESSION['board'] = array_fill(0, 9, null);
    $_SESSION['turn'] = 'X';
    $_SESSION['winner'] = null;
    $_SESSION['isDraw'] = false;
}

function makeMove($index) {
    if ($_SESSION['winner'] || $_SESSION['board'][$index] !== null) {
        return;
    }

    $_SESSION['board'][$index] = $_SESSION['turn'];

    if (checkWinner()) {
        $_SESSION['winner'] = $_SESSION['turn'];
        $_SESSION['scores'][$_SESSION['turn']] += 1;
    } else if (!in_array(null, $_SESSION['board'])) {
        $_SESSION['isDraw'] = true;
        $_SESSION['scores']['draws'] += 1;
    } else {
        if ($_SESSION['turn'] === 'X') {
            $_SESSION['turn'] = 'O';
        } else {
            $_SESSION['turn'] = 'X';
        }
    }
}

function checkWinner() {
    $winningCombinations = [
        [0, 1, 2], [3, 4, 5], [6, 7, 8],
        [0, 3, 6], [1, 4, 7], [2, 5, 8],
        [0, 4, 8], [2, 4, 6]
    ];

    foreach ($winningCombinations as $combination) {
        if ($_SESSION['board'][$combination[0]] !== null &&
            $_SESSION['board'][$combination[0]] === $_SESSION['board'][$combination[1]] &&
            $_SESSION['board'][$combination[1]] === $_SESSION['board'][$combination[2]]) {
                
            return true;
        }
    }
    return false;
}

function getGameState() {
    return [
        'board' => $_SESSION['board'],
        'turn' => $_SESSION['turn'],
        'winner' => $_SESSION['winner'],
        'isDraw' => $_SESSION['isDraw'],
        'scores' => $_SESSION['scores']
    ];
}
?>
