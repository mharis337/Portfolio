document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("modal");
    const closeButton = document.querySelector(".close-button");
    const modalMessage = document.getElementById("modal-message");
  
    function showModal(message) {
      modalMessage.textContent = message;
      modal.style.display = "flex";
    }
  
    if (closeButton) {
      closeButton.addEventListener("click", () => {
        modal.style.display = "none";
      });
    }
  
    window.addEventListener("click", (event) => {
      if (event.target === modal) {
        modal.style.display = "none";
      }
  
      const adminLoginModal = document.getElementById('adminLoginModal');
      if (event.target === adminLoginModal) {
        adminLoginModal.style.display = 'none';
      }
    });
  
    const checkWaitTimeBtn = document.querySelector(".check-wait-time");
    const checkWaitTimeModal = document.getElementById("checkWaitTime");
    const formContent = document.getElementById("enterQueue");
  
    if (checkWaitTimeBtn) {
      checkWaitTimeBtn.addEventListener("click", (event) => {
        event.preventDefault();
        checkWaitTimeModal.style.display = "block";
        if (formContent) {
          formContent.style.display = "none";
        }
      });
    }
  
    const checkWaitTimeForm = document.getElementById("checkWaitTimeForm");
    if (checkWaitTimeForm) {
      checkWaitTimeForm.onsubmit = function (event) {
        event.preventDefault();
  
        const name = document.getElementById("checkName").value;
        const code = document.getElementById("checkCode").value;
  
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "/public/db_api.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");
  
        xhr.onreadystatechange = function () {
          if (xhr.readyState === 4) {
            if (xhr.status === 200) {
              const data = JSON.parse(xhr.responseText);
              if (data.success) {
                showModal(`Hello ${name}, your estimated wait time is: ${data.waitTime} minutes.`);
                checkWaitTimeModal.style.display = "none";
              } else {
                showModal(`User not found or invalid code.`);
              }
            } else {
              showModal("An error occurred. Please try again.");
            }
          }
        };
  
        const requestData = JSON.stringify({
          action: "check_wait_time",
          name,
          code
        });
        xhr.send(requestData);
      };
    }
  
    const enterQueueBtn = document.getElementById("queue-button");
    const severitySlider = document.getElementById("severity");
    const severityValue = document.getElementById("severity-value");
  
    if (enterQueueBtn) {
      enterQueueBtn.addEventListener("click", (event) => {
        event.preventDefault();
        formContent.style.display = "block";
        if (checkWaitTimeModal) {
          checkWaitTimeModal.style.display = "none";
        }
      });
    }
  
    if (severitySlider && severityValue) {
      severityValue.textContent = severitySlider.value;
      severitySlider.addEventListener("input", () => {
        severityValue.textContent = severitySlider.value;
      });
    }
  
    const queueForm = document.getElementById("queueForm");
    if (queueForm) {
      queueForm.onsubmit = function (event) {
        event.preventDefault();
  
        const name = document.getElementById("name").value;
        const injury = document.getElementById("injury").value;
        const severity = document.getElementById("severity").value;
  
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "/public/db_api.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");
  
        xhr.onreadystatechange = function () {
          if (xhr.readyState === 4) {
            if (xhr.status === 200) {
              const data = JSON.parse(xhr.responseText);
              if (data.success) {
                showModal(`You have been added to the queue. Your code is: ${data.code}. Estimated wait time: ${data.waitTime} minutes.`);
                formContent.style.display = "none";
              } else {
                showModal(`There was an error: ${data.error}`);
              }
            } else {
              showModal("An error occurred. Please try again.");
            }
          }
        };
  
        const requestData = JSON.stringify({
          action: "add_to_queue",
          name,
          injury,
          severity
        });
        xhr.send(requestData);
      };
    }
  
    const fetchQueueData = () => {
      const xhr = new XMLHttpRequest();
      xhr.open("GET", "/public/db_api.php?action=get_queue_data", true);
  
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
          if (xhr.status === 200) {
            const data = JSON.parse(xhr.responseText);
            document.getElementById("totalPatients").textContent = `Total: ${data.totalPatients}`;
            document.getElementById("inTreatment").textContent = `In Treatment: ${data.inTreatment}`;
            document.getElementById("waiting").textContent = `Waiting: ${data.waiting}`;
            document.getElementById("estimatedWait").textContent = `${data.estimatedWait} hrs`;
          }
        }
      };
  
      xhr.send();
    };
  
    fetchQueueData();
    setInterval(fetchQueueData, 10000);
  
    const adminLoginButton = document.querySelector('.admin-login-button');
    const adminLoginModal = document.getElementById('adminLoginModal');
    const adminCloseButton = adminLoginModal ? adminLoginModal.querySelector('.admin-close-button') : null;
    const adminLoginForm = document.getElementById('adminLoginForm');
    const loginError = document.getElementById('loginError');
  
    if (adminLoginButton) {
      adminLoginButton.addEventListener('click', (event) => {
        event.preventDefault();
        adminLoginModal.style.display = 'block';
      });
    }
  
    if (adminCloseButton) {
      adminCloseButton.addEventListener('click', () => {
        adminLoginModal.style.display = 'none';
      });
    }
  
    if (adminLoginForm) {
      adminLoginForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
  
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/public/db_api.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
  
        xhr.onreadystatechange = function () {
          if (xhr.readyState === 4) {
            if (xhr.status === 200) {
              const response = JSON.parse(xhr.responseText);
              if (response.success) {
                window.location.href = response.redirect;
              } else {
                loginError.textContent = response.message;
              }
            } else {
              loginError.textContent = 'An error occurred. Please try again.';
            }
          }
        };
  
        const requestData = JSON.stringify({
          action: 'admin_login',
          username,
          password
        });
  
        xhr.send(requestData);
      });
    }
  });