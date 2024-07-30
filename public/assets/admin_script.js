document.addEventListener('DOMContentLoaded', () => {
    function admitPatient(patientId) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/public/db_api.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    const patientRow = document.getElementById('patient-' + patientId);
                    patientRow.querySelector('.patient-wait-time').textContent = '0';
                }
            }
        };
        const requestData = JSON.stringify({
            action: 'admit_patient',
            id: patientId
        });
        xhr.send(requestData);
    }
    function removePatient(patientId) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/public/db_api.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    const patientRow = document.getElementById('patient-' + patientId);
                    patientRow.remove();
                }
            }
        };
        const requestData = JSON.stringify({
            action: 'remove_patient',
            id: patientId
        });
        xhr.send(requestData);
    }
    document.querySelectorAll('.admit-button').forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            const patientId = this.getAttribute('data-id');
            admitPatient(patientId);
        });
    });
    document.querySelectorAll('.remove-button').forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            const patientId = this.getAttribute('data-id');
            removePatient(patientId);
        });
    });
});
