<?php ob_start(); $url=$app['base_url'].'/staff/resource/stream?id='.(int)$resource['id']; ?>
<div class="viewer-overlay">PSNF • <?= htmlspecialchars($_SESSION['staff_name']) ?></div>
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
  <?php endif; ?>
</div>
<?php $content=ob_get_clean(); $title='Secure Viewer'; require __DIR__ . '/../layouts/main.php'; ?>
