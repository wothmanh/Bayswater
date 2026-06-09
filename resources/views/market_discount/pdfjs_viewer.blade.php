<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PDF Viewer</title>
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <style>
        :root { --toolbar-h: 48px; }
        html, body { height: 100%; margin: 0; }
        body { background: #ffffff; }
        .toolbar {
            height: var(--toolbar-h);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            background: #1f2937; /* gray-800 */
            color: #fff;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
        }
        .btn {
            background: #374151; /* gray-700 */
            border: 0;
            color: #fff;
            padding: 0.35rem 0.6rem;
            border-radius: 0.25rem;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .page-info { margin-left: 0.25rem; }
        .spacer { flex: 1; }
        .viewer {
            position: absolute;
            top: var(--toolbar-h);
            left: 0; right: 0; bottom: 0;
            overflow: auto;
            background: #ffffff;
        }
        canvas { display: block; margin: 0 auto; }
    </style>
</head>
<body>
<div class="toolbar">
    <button class="btn" id="prev">Prev</button>
    <button class="btn" id="next">Next</button>
    <span class="page-info"><span id="page_num">1</span> / <span id="page_count">?</span></span>
    <button class="btn" id="zoom_out">Zoom -</button>
    <button class="btn" id="zoom_in">Zoom +</button>
    <span class="spacer"></span>
    <a class="btn" id="download" href="{{ $fileUrl }}" download>Download</a>
    <a class="btn" id="print" href="{{ $fileUrl }}" target="_blank" rel="noopener">Print</a>
</div>
<div class="viewer">
    <canvas id="the-canvas"></canvas>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    const url = @json($fileUrl);
    const canvas = document.getElementById('the-canvas');
    const ctx = canvas.getContext('2d');
    let pdfDoc = null;
    let pageNum = 1;
    let zoom = 1; // additional zoom factor applied on top of fit-to-width
    let rendering = false;

    // Configure worker
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    function baseScaleForViewport(unscaledViewport) {
        const containerWidth = document.querySelector('.viewer').clientWidth;
        return containerWidth / unscaledViewport.width;
    }

    function renderPage(num) {
        rendering = true;
        pdfDoc.getPage(num).then(function(page) {
            const unscaledViewport = page.getViewport({ scale: 1 });
            const base = baseScaleForViewport(unscaledViewport);
            const finalScale = base * zoom;
            const viewport = page.getViewport({ scale: finalScale });
            canvas.width = viewport.width;
            canvas.height = viewport.height;

            const renderTask = page.render({ canvasContext: ctx, viewport });
            renderTask.promise.then(function () {
                rendering = false;
                document.getElementById('page_num').textContent = num;
            });
        });
    }

    function queueRenderPage(num) {
        if (rendering) return;
        renderPage(num);
    }

    function onPrevPage() {
        if (pageNum <= 1) return;
        pageNum--;
        queueRenderPage(pageNum);
    }
    function onNextPage() {
        if (pageNum >= pdfDoc.numPages) return;
        pageNum++;
        queueRenderPage(pageNum);
    }
    function onZoomIn() { zoom *= 1.1; renderPage(pageNum); }
    function onZoomOut() { zoom /= 1.1; renderPage(pageNum); }

    window.addEventListener('resize', () => renderPage(pageNum));

    document.getElementById('prev').addEventListener('click', onPrevPage);
    document.getElementById('next').addEventListener('click', onNextPage);
    document.getElementById('zoom_in').addEventListener('click', onZoomIn);
    document.getElementById('zoom_out').addEventListener('click', onZoomOut);

    pdfjsLib.getDocument({ url, withCredentials: false }).promise.then(function(pdf) {
        pdfDoc = pdf;
        document.getElementById('page_count').textContent = pdf.numPages;
        renderPage(pageNum);
    }).catch(function(err) {
        console.error('Error loading PDF:', err);
        const viewer = document.querySelector('.viewer');
        viewer.innerHTML = '<div style="padding:1rem;font-family:sans-serif;color:#b91c1c">Failed to load PDF.</div>';
    });
</script>
</body>
</html>