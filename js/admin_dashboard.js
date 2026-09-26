let selectedClass = null;

function selectClass(classNum) {
    selectedClass = classNum;
    localStorage.setItem('selectedClass', classNum);

    document.getElementById('selected-class-text').textContent = 'Class ' + classNum;
    document.getElementById('class-selection').classList.add('hidden');
    document.getElementById('dashboard-options').classList.remove('hidden');
    document.getElementById('class-stats').classList.remove('hidden');

    fetchClassStats(classNum);
    fetchClassQuizzes(classNum); // ✅ Fetch quizzes
}

function resetClass() {
    selectedClass = null;
    localStorage.removeItem('selectedClass');

    document.getElementById('dashboard-options').classList.add('hidden');
    document.getElementById('class-selection').classList.remove('hidden');
    document.getElementById('class-stats').classList.add('hidden');
    document.getElementById('quiz-container').innerHTML = 'Loading...';
}

function goToQuiz() {
    if (selectedClass) {
        window.location.href = `add_quiz.php?class=${selectedClass}`;
    }
}

function goToStudent() {
    if (selectedClass) {
        window.location.href = `student_management.php?class=${selectedClass}`;
    }
}

function fetchClassStats(classNum) {
    fetch(`php/get_class_stats.php?class=${classNum}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('teacher-count').textContent = data.teachers;
            document.getElementById('student-count').textContent = data.students;
        })
        .catch(error => {
            console.error("Error fetching class stats:", error);
            document.getElementById('teacher-count').textContent = "Error";
            document.getElementById('student-count').textContent = "Error";
        });
}

// ✅ নতুন ফাংশন: কুইজ লোড করা
function fetchClassQuizzes(classNum) {
    fetch(`php/get_quizzes.php?class=${classNum}`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('quiz-container');
            container.innerHTML = '';

            if (data.length === 0) {
                container.innerHTML = '<p>No quizzes available for this class.</p>';
                return;
            }

            data.forEach(quiz => {
                const item = document.createElement('div');
                item.classList.add('quiz-item');
                item.innerHTML = `
                    <strong>Subject:</strong> ${quiz.subject} <br>
                    <strong>Chapter:</strong> ${quiz.chapter} <br>
                    <strong>Topic:</strong> ${quiz.topic}
                    <hr>
                `;
                container.appendChild(item);
            });
        })
        .catch(error => {
            console.error('Error loading quizzes:', error);
            document.getElementById('quiz-container').innerHTML = 'Error loading quizzes.';
        });
}

window.onload = () => {
    const savedClass = localStorage.getItem('selectedClass');
    if (savedClass) {
        selectClass(savedClass);
    }
};
