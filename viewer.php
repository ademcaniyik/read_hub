<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (empty($_GET['file']) || empty($_GET['category'])) {
    header('Location: index.php');
    exit;
}

$category = $_GET['category'];
$file = $_GET['file'];
$filePath = CATEGORIES_PATH . '/' . $category . '/' . $file;

if (!file_exists($filePath)) {
    header('Location: index.php');
    exit;
}

// Get last page read
$progress = getPDFProgress($category . '/' . $file);
$currentPage = $progress['page'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reading <?php echo htmlspecialchars($file); ?> - ReadHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        #pdf-container {
            width: 100%;
            height: calc(100vh - 160px);
            overflow: auto;
            background: #f8f9fa;
            position: relative;
        }

        #pdf-viewer {
            max-width: 100%;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: block;
            margin: 0 auto;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            padding: 10px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-bottom: none;
        }

        @media (max-width: 768px) {
            .toolbar .btn {
                padding: 0.375rem 0.5rem;
                font-size: 0.875rem;
            }

            .page-info {
                font-size: 0.875rem;
            }

            #pdf-container {
                height: calc(100vh - 140px);
            }
        }

        .loading-indicator {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: none;
        }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">

                <div id="pdf-container">
                    <canvas id="pdf-viewer"></canvas>
                </div>
                <div class="loading-indicator">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="toolbar">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <button id="prev" class="btn btn-secondary">
                        <i class="bi bi-chevron-left"></i>
                        <span class="d-none d-sm-inline">Previous</span>
                    </button>
                    <button id="next" class="btn btn-secondary">
                        <span class="d-none d-sm-inline">Next</span>
                        <i class="bi bi-chevron-right"></i>
                    </button>
                    <span class="mx-2 page-info">
                        Page: <span id="page_num"></span> / <span id="page_count"></span>
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button id="zoomOut" class="btn btn-secondary">
                        <i class="bi bi-zoom-out"></i>
                    </button>
                    <button id="zoomIn" class="btn btn-secondary">
                        <i class="bi bi-zoom-in"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.7.107/pdf.min.js"></script>
    <script>
        // PDF.js initialization
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.7.107/pdf.worker.min.js';

        // Enable caching
        const cMapUrl = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.7.107/cmaps/';
        const cMapPacked = true;

        let pdfDoc = null,
            pageNum = <?php echo $currentPage; ?>,
            pageRendering = false,
            pageNumPending = null,
            scale = 1.5,
            canvas = document.getElementById('pdf-viewer'),
            ctx = canvas.getContext('2d');

        function renderPage(num) {
            pageRendering = true;
            pdfDoc.getPage(num).then(function(page) {
                let viewport = page.getViewport({
                    scale: scale
                });
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                let renderContext = {
                    canvasContext: ctx,
                    viewport: viewport
                };

                let renderTask = page.render(renderContext);

                renderTask.promise.then(function() {
                    pageRendering = false;
                    if (pageNumPending !== null) {
                        renderPage(pageNumPending);
                        pageNumPending = null;
                    }
                    // Update progress on server
                    fetch('save-progress.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'category=<?php echo urlencode($category); ?>&file=<?php echo urlencode($file); ?>&page=' + num
                    });
                });
            });

            document.getElementById('page_num').textContent = num;
        }

        function queueRenderPage(num) {
            if (pageRendering) {
                pageNumPending = num;
            } else {
                renderPage(num);
            }
        }

        function onPrevPage() {
            if (pageNum <= 1) {
                return;
            }
            pageNum--;
            queueRenderPage(pageNum);
        }

        function onNextPage() {
            if (pageNum >= pdfDoc.numPages) {
                return;
            }
            pageNum++;
            queueRenderPage(pageNum);
        }

        function onZoomIn() {
            scale *= 1.2;
            queueRenderPage(pageNum);
        }

        function onZoomOut() {
            scale /= 1.2;
            queueRenderPage(pageNum);
        }
        const loadingIndicator = document.querySelector('.loading-indicator');

        function showLoading() {
            loadingIndicator.style.display = 'block';
        }

        function hideLoading() {
            loadingIndicator.style.display = 'none';
        }

        // Adjust scale based on screen size
        function getInitialScale() {
            if (window.innerWidth <= 768) {
                return 1.0;
            }
            return 1.5;
        }

        // Initialize scale
        scale = getInitialScale(); // Get PDF URL through pdf.php handler
        const pdfUrl = window.location.origin +
            '/read_hub/pdf.php?category=' +
            encodeURIComponent('<?php echo $category; ?>') + '&file=' +
            encodeURIComponent('<?php echo $file; ?>');

        console.log('Loading PDF from:', pdfUrl); // Debug log

        // Load the PDF
        showLoading();
        pdfjsLib.getDocument({
            url: pdfUrl,
            cMapUrl: cMapUrl,
            cMapPacked: cMapPacked,
            verbosity: pdfjsLib.VerbosityLevel.INFOS // Enable detailed logging
        }).promise.then(function(pdf) {
            pdfDoc = pdf;
            document.getElementById('page_count').textContent = pdf.numPages;
            renderPage(pageNum);
            hideLoading();
        }).catch(function(error) {
            console.error('Error loading PDF:', error);
            hideLoading();
            console.error('Detailed error:', error);
            document.querySelector('.container-fluid').innerHTML += `
                <div class="alert alert-danger mt-3">
                    <h4 class="alert-heading">Error Loading PDF</h4>
                    <p>There was an error loading the PDF file. Details:</p>
                    <hr>
                    <p class="mb-0"><code>${error.message || 'Unknown error'}</code></p>
                    <div class="mt-3">
                        <button onclick="location.reload()" class="btn btn-outline-danger">Try Again</button>
                        <a href="index.php" class="btn btn-outline-primary">Go Back</a>
                    </div>
                </div>
            `;
        });

        // Button events
        document.getElementById('prev').addEventListener('click', onPrevPage);
        document.getElementById('next').addEventListener('click', onNextPage);
        document.getElementById('zoomIn').addEventListener('click', onZoomIn);
        document.getElementById('zoomOut').addEventListener('click', onZoomOut);
    </script>
</body>

</html>