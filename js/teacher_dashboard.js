document.addEventListener('DOMContentLoaded', () => {
    // Sidebar toggle
    window.toggleSidebar = function () {
        const sidebar = document.querySelector('.sidebar');
        const main = document.querySelector('.main');
        if (sidebar && main) {
            sidebar.classList.toggle('active');
            main.classList.toggle('shifted');
        }
    };

    // Load quiz results dynamically
    window.toggleResults = function (quizId) {
        const resultsDiv = document.getElementById('results-' + quizId);

        if (!resultsDiv) return;

        if (resultsDiv.style.display === 'block') {
            resultsDiv.style.display = 'none';
            return;
        }

        resultsDiv.style.display = 'block';
        resultsDiv.innerHTML = '<p>Loading...</p>';

        fetch('php/get_quiz_results.php?quiz_id=' + quizId)
            .then(response => response.text())
            .then(data => {
                resultsDiv.innerHTML = data;
            })
            .catch(err => {
                resultsDiv.innerHTML = '<p>Error loading results.</p>';
                console.error(err);
            });
    };
});
