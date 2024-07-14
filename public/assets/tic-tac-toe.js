const end = document.querySelector(".end_screen");
const result = document.querySelector(".results");
const board = document.querySelector(".tic-tac-toe");

document.querySelectorAll(".cell").forEach((cell, index) => {
  cell.addEventListener("click", () => {
    makeMove(index);
  });
});

document.querySelector(".restart").addEventListener("click", () => {
  restartGame();
});

function makeMove(index) {
  const xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.readyState == XMLHttpRequest.DONE) {
      let data = JSON.parse(xmlhttp.responseText);
      updateBoard(data);
      updateScores(data.scores);
    }
  };
  xmlhttp.open("POST", "/public/api.php", true);
  xmlhttp.setRequestHeader("Content-Type", "application/json");
  xmlhttp.send(JSON.stringify({ action: "makeMove", index: index }));
}

function restartGame() {
    setTimeout(() => {

  end.classList.add("hidden");
  board.classList.remove("hidden");

  const xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.readyState == XMLHttpRequest.DONE) {
      let data = JSON.parse(xmlhttp.responseText);
      updateBoard(data);
      updateScores(data.scores);
    }
  };
  xmlhttp.open("POST", "/public/api.php", true);
  xmlhttp.setRequestHeader("Content-Type", "application/json");
  xmlhttp.send(JSON.stringify({ action: "restart" }));
}, 500);

}

function updateBoard(data) {
  const cells = document.querySelectorAll(".cell");
  cells.forEach((cell, index) => {
    cell.classList.remove("x", "o");
    if (data.board[index] === "X") {
      cell.classList.add("x");
    } else if (data.board[index] === "O") {
      cell.classList.add("o");
    }
  });
  document.querySelector(".title").textContent = `It is ${data.turn}'s Turn`;
  if (data.winner || data.isDraw) {
    displayEndMessage(data);
  }
}

function updateScores(scores) {
  const scoresContainer = document.querySelector(".scores");
  scoresContainer.innerHTML = 
      '<p>Player X Wins: ' + (scores["X"] !== undefined ? scores["X"] : 0) + '</p>' +
      '<p>Player O Wins: ' + (scores["O"] !== undefined ? scores["O"] : 0) + '</p>' +
      '<p>Draws: ' + (scores["draws"] !== undefined ? scores["draws"] : 0) + '</p>';
}


function displayEndMessage(data) {
  setTimeout(() => {
    end.classList.remove("hidden");
    board.classList.add("hidden");
    if (!data.isDraw) {
      result.textContent = "The winner is " + data.winner + "!";
    } else {
      result.textContent = "It's a Draw!";
    }
  }, 1000);
}
