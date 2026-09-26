document.addEventListener('DOMContentLoaded', function () {
    // Prevent back navigation
    history.pushState(null, null, location.href);
    window.onpopstate = function () {
        history.go(1);
    };

    // Timer logic
    let timeLeft = 10 * 60; // 10 minutes in seconds
    const timerElement = document.getElementById('timer');
    const quizForm = document.getElementById('quizForm');

    const timerInterval = setInterval(() => {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        timerElement.textContent = `Time Left: ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;

        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            alert('Time is up! Submitting quiz...');
            quizForm.submit();
        }
        timeLeft--;
    }, 1000);
});