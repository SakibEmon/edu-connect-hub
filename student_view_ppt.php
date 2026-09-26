<?php
session_start();
if (!isset($_SESSION['name']) || $_SESSION['role'] != "student") {
    header("Location: login.html");
    exit();
}

include 'php/db_connect.php'; // ডাটাবেস কানেকশন ইনক্লুড করুন

if (!isset($_GET['file'])) {
    echo "File not specified.";
    exit();
}

$file_path = urldecode($_GET['file']);
$file_ext = pathinfo($file_path, PATHINFO_EXTENSION);

// অতিরিক্ত নিরাপত্তা: ফাইলটি ডাটাবেসে বিদ্যমান কিনা চেক করুন
$sql_check = "SELECT id FROM teacher_ppt WHERE file_path = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("s", $file_path);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
if ($result_check->num_rows === 0) {
    echo "Invalid file access.";
    exit();
}
$stmt_check->close();

// ফাইল অস্তিত্ব চেক (অপশনাল, কিন্তু রেকমেন্ডেড)
if (!file_exists($file_path) || strpos($file_path, 'Uploads/ppt/') !== 0) {
    echo "File not found or access denied.";
    exit();
}

$subject = isset($_GET['subject']) ? urldecode($_GET['subject']) : '';
$class = isset($_GET['class']) ? urldecode($_GET['class']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Slideshow Viewer</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/view_ppt.css"> <!-- আপনার CSS ফাইলগুলো অ্যাডজাস্ট করুন -->
</head>
<body>
<div id="pdf-viewer-container">
    <h2>Slide Show View</h2>

    <?php if ($file_ext === "pdf") { ?>
        <div id="controls">
            <button onclick="prevPage()">Previous</button>
            <span>Page: <span id="page_num">1</span> / <span id="page_count">--</span></span>
            <button onclick="nextPage()">Next</button>
            <button onclick="toggleFullScreen()">Fullscreen</button>
        </div>

        <canvas id="pdf-canvas"></canvas>
    <?php } else { ?>
        <p>Only PDF files can be viewed as slideshow. Please ask your teacher to upload as PDF.</p>
    <?php } ?>

    <?php if ($file_ext === "pdf") { ?>
        <div id="preview-container">
            <h3>Slide Previews</h3>
            <div id="thumbnails"></div>
        </div>
    <?php } ?>

    <a href="student_course_view.php?subject=<?php echo urlencode($subject); ?>&class=<?php echo urlencode($class); ?>">
        <button id="back-btn">Back to Course View</button>
    </a>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
<script>
    const url = '<?php echo $file_path; ?>';
    let pdfDoc = null,
        pageNum = 1,
        pageRendering = false,
        pageNumPending = null;
    const canvas = document.getElementById('pdf-canvas');
    const ctx = canvas.getContext('2d');
    const thumbnailsContainer = document.getElementById('thumbnails');

    function renderPage(num) {
        pageRendering = true;
        pdfDoc.getPage(num).then(function(page) {
            const viewport = page.getViewport({scale: 1.5});
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            const renderContext = {
                canvasContext: ctx,
                viewport: viewport
            };
            const renderTask = page.render(renderContext);

            renderTask.promise.then(function() {
                pageRendering = false;
                document.getElementById('page_num').textContent = pageNum;
                if (pageNumPending !== null) {
                    renderPage(pageNumPending);
                    pageNumPending = null;
                }
            });
        });
    }

    function queueRenderPage(num) {
        if (pageRendering) {
            pageNumPending = num;
        } else {
            renderPage(num);
        }
    }

    function prevPage() {
        if (pageNum <= 1) return;
        pageNum--;
        queueRenderPage(pageNum);
    }

    function nextPage() {
        if (pageNum >= pdfDoc.numPages) return;
        pageNum++;
        queueRenderPage(pageNum);
    }

    function renderThumbnail(pageNum) {
        pdfDoc.getPage(pageNum).then(function(page) {
            const viewport = page.getViewport({scale: 0.2});
            const thumbnailCanvas = document.createElement('canvas');
            const thumbnailCtx = thumbnailCanvas.getContext('2d');

            thumbnailCanvas.height = viewport.height;
            thumbnailCanvas.width = viewport.width;

            const renderContext = {
                canvasContext: thumbnailCtx,
                viewport: viewport
            };

            page.render(renderContext).promise.then(function() {
                const img = document.createElement('img');
                img.src = thumbnailCanvas.toDataURL();
                img.classList.add('slide-preview');
                img.setAttribute('data-page', pageNum);
                img.onclick = function() {
                    pageNum = parseInt(this.getAttribute('data-page'));
                    queueRenderPage(pageNum);
                };
                thumbnailsContainer.appendChild(img);
            });
        });
    }

    function toggleFullScreen() {
        if (!document.fullscreenElement) {
            canvas.requestFullscreen().catch(err => {
                alert(`Error attempting to enable fullscreen mode: ${err.message}`);
            });
        } else {
            document.exitFullscreen();
        }
    }

    // Keyboard control for slide navigation
    document.addEventListener("keydown", function(event) {
        switch (event.key) {
            case "ArrowRight": // Next slide
            case "MediaTrackNext":
                nextPage();
                break;
            case "ArrowLeft": // Previous slide
            case "MediaTrackPrevious":
                prevPage();
                break;
        }
    });

    pdfjsLib.getDocument(url).promise.then(function(pdfDoc_) {
        pdfDoc = pdfDoc_;
        document.getElementById('page_count').textContent = pdfDoc.numPages;
        renderPage(pageNum);
        for (let i = 1; i <= pdfDoc.numPages; i++) {
            renderThumbnail(i);
        }
    });
</script>
</body>
</html>

<?php
$conn->close();
?>