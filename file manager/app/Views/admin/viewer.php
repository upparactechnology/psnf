<?php ob_start(); 
$url=$app['base_url'].'/admin/resources/stream?id='.(int)$resource['id']; 
$ext = strtolower(pathinfo((string)$resource['file_name'], PATHINFO_EXTENSION));
?>
<script>
  (function() {
    const sanitize = (proto) => {
      if (!proto) return;
      for (const key in proto) {
        if (Object.prototype.hasOwnProperty.call(proto, key)) {
          try {
            Object.defineProperty(proto, key, { enumerable: false });
          } catch (e) {}
        }
      }
    };
    sanitize(Object.prototype);
    sanitize(Array.prototype);
    sanitize(RegExp.prototype);
  })();
</script>
<div class="viewer-overlay">PSNF • <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></div>
<div class="container-fluid py-2 no-select">
  <a class="btn btn-sm btn-secondary mb-2" href="javascript:history.back()">Back</a>

  <div class="viewer-toolbar mb-2">
    <button class="btn btn-sm btn-outline-secondary" id="zoomOutBtn" type="button">-</button>
    <span id="zoomLabel" class="px-2">100%</span>
    <button class="btn btn-sm btn-outline-secondary" id="zoomInBtn" type="button">+</button>
    <button class="btn btn-sm btn-outline-secondary" id="zoomResetBtn" type="button">Reset</button>
  </div>

  <?php if (str_contains($resourceType,'pdf')): ?>
    <div id="pdfContainer" class="card-glass p-3 viewer-stage" style="min-height:85vh"></div>
    <script type="module">
      import * as pdfjsLib from 'https://cdn.jsdelivr.net/npm/pdfjs-dist@4.5.136/legacy/build/pdf.min.mjs';
      pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@4.5.136/legacy/build/pdf.worker.min.mjs';

      const pdf = await pdfjsLib.getDocument('<?= $url ?>').promise;
      const host = document.getElementById('pdfContainer');
      const zoomLabel = document.getElementById('zoomLabel');
      let zoom = 1;
      const pages = [];

      for(let i=1;i<=pdf.numPages;i++){
        const page = await pdf.getPage(i);
        pages.push(page);
      }

      async function renderAll(){
        host.innerHTML='';
        const maxW = host.clientWidth - 24;
        for(const page of pages){
          const base = page.getViewport({scale:1});
          const autoScale = Math.max(0.7, maxW / base.width);
          const viewport = page.getViewport({scale:autoScale * zoom});
          const canvas = document.createElement('canvas');
          canvas.className='pdf-page d-block';
          canvas.width = viewport.width;
          canvas.height = viewport.height;
          host.appendChild(canvas);
          await page.render({canvasContext:canvas.getContext('2d'),viewport}).promise;
        }
        zoomLabel.textContent = Math.round(zoom * 100) + '%';
      }

      document.getElementById('zoomInBtn').addEventListener('click',()=>{zoom=Math.min(3,zoom+0.1);renderAll();});
      document.getElementById('zoomOutBtn').addEventListener('click',()=>{zoom=Math.max(0.5,zoom-0.1);renderAll();});
      document.getElementById('zoomResetBtn').addEventListener('click',()=>{zoom=1;renderAll();});
      window.addEventListener('resize',()=>renderAll());
      await renderAll();
    </script>

  <?php elseif (str_contains($resourceType,'video')): ?>
    <div class="card-glass p-2 viewer-stage">
      <video id="v" class="video-js vjs-default-skin secure-video" controls preload="auto" controlsList="nodownload noplaybackrate" disablePictureInPicture width="100%" height="760">
        <source src="<?= $url ?>" type="<?= htmlspecialchars($resourceType) ?>">
      </video>
    </div>
    <script>
      const p = videojs('v', {controlBar:{pictureInPictureToggle:false,playbackRateMenuButton:false,fullscreenToggle:true}});
      let zoom = 1;
      const el = document.querySelector('#v_html5_api');
      const lbl = document.getElementById('zoomLabel');
      function applyZoom(){ if(!el) return; el.style.transform='scale('+zoom+')'; el.style.transformOrigin='center top'; lbl.textContent=Math.round(zoom*100)+'%'; }
      document.getElementById('zoomInBtn').addEventListener('click',()=>{zoom=Math.min(2.5,zoom+0.1);applyZoom();});
      document.getElementById('zoomOutBtn').addEventListener('click',()=>{zoom=Math.max(0.6,zoom-0.1);applyZoom();});
      document.getElementById('zoomResetBtn').addEventListener('click',()=>{zoom=1;applyZoom();});
      applyZoom();
    </script>

  <?php else: ?>
    <?php if ($ext === 'pptx'): ?>
      <!-- PPTX Viewer -->
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/meshesha/PPTXjs@master/css/pptxjs.css">
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/meshesha/PPTXjs@master/css/nv.d3.min.css">
      
      <style>
        #pptxContainer audio, #pptxContainer video {
          display: none !important;
        }
      </style>
      <div id="pptxContainer" class="card-glass p-3 viewer-stage" style="min-height:85vh; background: #fff; overflow: auto; border: 1px solid var(--line); border-radius: 12px; transition: transform 0.2s ease;"></div>

      <script>
        (function() {
          function loadScript(src) {
            return new Promise((resolve, reject) => {
              const s = document.createElement('script');
              s.src = src;
              s.onload = resolve;
              s.onerror = reject;
              document.body.appendChild(s);
            });
          }

          window.addEventListener('DOMContentLoaded', async () => {
            // Sanitize prototype to capture properties added during page load
            const sanitize = (proto) => {
              if (!proto) return;
              for (const key in proto) {
                if (Object.prototype.hasOwnProperty.call(proto, key)) {
                  try {
                    Object.defineProperty(proto, key, { enumerable: false });
                  } catch (e) {}
                }
              }
            };
            sanitize(Object.prototype);
            sanitize(Array.prototype);
            sanitize(RegExp.prototype);

            while (typeof jQuery === 'undefined') {
              await new Promise(r => setTimeout(r, 50));
            }
            try {
              await loadScript("https://cdn.jsdelivr.net/gh/meshesha/PPTXjs@master/js/jszip.min.js");
              await loadScript("https://cdn.jsdelivr.net/gh/meshesha/PPTXjs@master/js/filereader.js");
              await loadScript("https://cdn.jsdelivr.net/gh/meshesha/PPTXjs@master/js/d3.min.js");
              await loadScript("https://cdn.jsdelivr.net/gh/meshesha/PPTXjs@master/js/nv.d3.min.js");
              await loadScript("https://cdn.jsdelivr.net/gh/meshesha/PPTXjs@master/js/pptxjs.js");

              const containerWidth = document.getElementById('pptxContainer').clientWidth - 40;
              const scaleVal = containerWidth < 960 ? Math.floor((containerWidth / 960) * 100) + "%" : "100%";

              jQuery("#pptxContainer").pptxToHtml({
                pptxFileUrl: "<?= $url ?>",
                fileInputId: "",
                slideMode: false,
                keyBoardShortCut: false,
                slidesScale: scaleVal,
                mediaProcess: false
              });

              // Slide zoom handling
              let zoom = 1;
              const el = document.getElementById('pptxContainer');
              const lbl = document.getElementById('zoomLabel');
              function applyZoom(){ el.style.transform='scale('+zoom+')'; el.style.transformOrigin='center top'; lbl.textContent=Math.round(zoom*100)+'%'; }
              document.getElementById('zoomInBtn').addEventListener('click',()=>{zoom=Math.min(3,zoom+0.1);applyZoom();});
              document.getElementById('zoomOutBtn').addEventListener('click',()=>{zoom=Math.max(0.4,zoom-0.1);applyZoom();});
              document.getElementById('zoomResetBtn').addEventListener('click',()=>{zoom=1;applyZoom();});
            } catch (err) {
              console.error("Failed to load PowerPoint viewer:", err);
            }
          });
        })();
      </script>
    <?php elseif (in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'])): ?>
      <div class="card-glass p-3 d-flex justify-content-center viewer-stage">
        <img id="secureImage" src="<?= $url ?>" class="img-fluid rounded viewer-image" style="max-height:88vh;object-fit:contain">
      </div>
      <script>
        let zoom = 1;
        const img = document.getElementById('secureImage');
        const lbl = document.getElementById('zoomLabel');
        function applyZoom(){ img.style.transform='scale('+zoom+')'; img.style.transformOrigin='center top'; lbl.textContent=Math.round(zoom*100)+'%'; }
        document.getElementById('zoomInBtn').addEventListener('click',()=>{zoom=Math.min(3,zoom+0.1);applyZoom();});
        document.getElementById('zoomOutBtn').addEventListener('click',()=>{zoom=Math.max(0.4,zoom-0.1);applyZoom();});
        document.getElementById('zoomResetBtn').addEventListener('click',()=>{zoom=1;applyZoom();});
        applyZoom();
      </script>
    <?php else: ?>
      <div class="card-glass p-5 text-center viewer-stage mx-auto shadow-sm" style="max-width: 600px; min-height: 40vh; display: flex; flex-direction: column; justify-content: center; align-items: center; border-radius: 16px; border: 1px solid var(--line);">
        <div class="mb-4">
          <?php if (in_array($ext, ['ppt', 'pptx'])): ?>
            <i class="bi bi-file-earmark-slides text-danger" style="font-size: 5rem;"></i>
          <?php elseif (in_array($ext, ['doc', 'docx'])): ?>
            <i class="bi bi-file-earmark-word text-primary" style="font-size: 5rem;"></i>
          <?php elseif (in_array($ext, ['xls', 'xlsx'])): ?>
            <i class="bi bi-file-earmark-excel text-success" style="font-size: 5rem;"></i>
          <?php elseif ($ext === 'zip' || $ext === 'rar'): ?>
            <i class="bi bi-file-earmark-zip text-warning" style="font-size: 5rem;"></i>
          <?php else: ?>
            <i class="bi bi-file-earmark-arrow-down text-secondary" style="font-size: 5rem;"></i>
          <?php endif; ?>
        </div>
        <h4 class="mb-2 font-weight-bold"><?= htmlspecialchars($resource['title']) ?></h4>
        <p class="text-muted mb-4"><?= htmlspecialchars($resource['file_name']) ?> (<?= strtoupper($ext) ?>)</p>
        <a href="<?= $url ?>&download=1" class="btn btn-primary btn-lg px-5 shadow-sm">
          <i class="bi bi-cloud-arrow-down me-2"></i> Download File
        </a>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?php $content=ob_get_clean(); $title='Secure Viewer'; require __DIR__ . '/../layouts/main.php'; ?>
