function toast(msg,type='info'){
  const c={success:'bg-success',error:'bg-danger',warning:'bg-warning text-dark',info:'bg-primary'};
  const id='t'+Date.now();
  const wrap=document.createElement('div');
  wrap.id=id;
  wrap.className=`toast align-items-center text-white ${c[type]||c.info} border-0 position-fixed bottom-0 end-0 m-3`;
  wrap.innerHTML=`<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
  wrap.querySelector('.toast-body').textContent=msg;
  document.body.appendChild(wrap);
  const t=new bootstrap.Toast(wrap,{delay:2600});
  wrap.addEventListener('hidden.bs.toast',()=>wrap.remove());
  t.show();
}

(function(){
  const body = document.body;
  if (!body) return;
  const enabled = body.getAttribute('data-smartboard-enabled') === '1';
  const minW = parseInt(body.getAttribute('data-smartboard-min-width') || '1600', 10);
  const minH = parseInt(body.getAttribute('data-smartboard-min-height') || '900', 10);

  function setCookie(val){
    document.cookie = `psnf_smartboard=${val}; path=/; max-age=86400`;
  }

  function update(){
    if (!enabled) { setCookie(0); return; }
    const w = Math.max(window.innerWidth || 0, (window.screen && window.screen.width) || 0);
    const h = Math.max(window.innerHeight || 0, (window.screen && window.screen.height) || 0);
    const isSmartboard = w >= minW && h >= minH;
    setCookie(isSmartboard ? 1 : 0);
  }

  update();
  let timer;
  window.addEventListener('resize', function(){
    clearTimeout(timer);
    timer = setTimeout(update, 200);
  });
})();

function setTheme(next){
  document.documentElement.setAttribute('data-theme', next);
  try { localStorage.setItem('psnf_theme', next); } catch (e) {}
}
function toggleTheme(){
  const cur = document.documentElement.getAttribute('data-theme') || 'light';
  const next = cur === 'dark' ? 'light' : 'dark';
  setTheme(next);
}
document.addEventListener('click', function(e){
  const target = e.target && e.target.closest ? e.target.closest('#themeToggle') : null;
  if (!target) return;
  e.preventDefault();
  toggleTheme();
});

$(document).on('click','#sidebarToggle',function(){
  $('.admin-shell').toggleClass('sidebar-collapsed');
});

function openCmdk(){ $('#cmdPalette').removeClass('d-none'); $('#cmdkInput').trigger('focus'); }
function closeCmdk(){ $('#cmdPalette').addClass('d-none'); $('#cmdkInput').val(''); $('.cmdk-item').show(); }
$(document).on('click','#cmdkToggle',openCmdk);
$(document).on('click','#cmdPalette',function(e){ if(e.target.id==='cmdPalette') closeCmdk(); });
$(document).on('keydown', function(e) {
  if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
    e.preventDefault();
    openCmdk();
    return;
  }
  
  const key = e.key;
  if (key === 'Backspace' || key === 'Escape') {
    const active = document.activeElement;
    if (active && (
      active.tagName === 'INPUT' || 
      active.tagName === 'TEXTAREA' || 
      active.tagName === 'SELECT' || 
      active.isContentEditable
    )) {
      return;
    }

    if (document.querySelector('.modal.show')) {
      return;
    }

    if (!$('#cmdPalette').hasClass('d-none')) {
      if (key === 'Escape') {
        closeCmdk();
      }
      return;
    }

    if (document.querySelector('.dropdown-menu.show')) {
      return;
    }

    e.preventDefault();

    const backBtn = Array.from(document.querySelectorAll('a, button')).find(el => {
      const txt = el.textContent.trim().toLowerCase();
      return txt === 'back' || txt.includes('back to') || txt === 'cancel';
    });

    if (backBtn) {
      if (backBtn.tagName === 'A' && backBtn.href) {
        window.location.href = backBtn.href;
      } else {
        backBtn.click();
      }
    } else {
      window.history.back();
    }
  }
});
$(document).on('input','#cmdkInput',function(){ const q=this.value.toLowerCase().trim(); $('.cmdk-item').each(function(){ $(this).toggle($(this).text().toLowerCase().includes(q)); }); });

$(document).on('dblclick', '.clickable-row', function(e) {
  if (e.target.closest('button, a, input, select, textarea, form, .dropdown, .modal')) {
    return;
  }
  const url = this.getAttribute('data-url');
  if (url) {
    window.location.href = url;
  }
});

document.addEventListener('submit', function(e){
  const form = e.target && e.target.closest ? e.target.closest('.ajax-form') : null;
  if (!form) return;
  e.preventDefault();
  const action = form.getAttribute('action') || '';
  const method = (form.getAttribute('method') || 'POST').toUpperCase();
  const fd = new FormData(form);
  fetch(action, {method, body: fd, credentials: 'same-origin'})
    .then(async res => {
      let data = null;
      try { data = await res.json(); } catch (err) { data = null; }
      if (res.ok && (!data || data.ok !== false)) {
        toast((data && data.message) ? data.message : 'Success','success');
        setTimeout(()=>location.reload(),450);
        return;
      }
      const msg = (data && data.message) ? data.message : 'Server error';
      toast(msg,'error');
    })
    .catch(()=>toast('Server error','error'));
});

function applyFilterGroup(group, queryOverride){
  const searchSelector = '.js-local-search[data-filter-group="' + group + '"]';
  const filterSelector = '.js-filter[data-filter-group="' + group + '"]';
  const rowSelector = 'tr[data-filter-group="' + group + '"]';
  const query = (queryOverride !== undefined ? queryOverride : ($(searchSelector).val() || '')).toString().toLowerCase().trim();
  const filters = {};
  $(filterSelector).each(function(){
    const key = $(this).attr('data-filter-key');
    const val = ($(this).val() || '').toString().toLowerCase().trim();
    if (val && val !== 'all') filters[key] = val;
  });

  const hasActiveFilter = query || Object.keys(filters).length > 0;
  const $table = $('table').has(rowSelector);

  if (hasActiveFilter) {
    if ($table.data('pagination')) {
      $table.data('pagination').destroy();
    }
    $(rowSelector).each(function(){
      const txt = (this.getAttribute('data-search') || '').toLowerCase();
      let ok = !query || txt.includes(query);
      if (ok) {
        for (const key in filters) {
          const rowVal = (this.getAttribute('data-' + key) || '').toLowerCase();
          if (rowVal !== filters[key]) { ok = false; break; }
        }
      }
      this.style.display = ok ? '' : 'none';
    });
  } else {
    $(rowSelector).show();
    if ($table.data('pagination')) {
      $table.data('pagination').restore();
    }
  }
}

$(document).on('input','#globalSearch',function(){
  const q=$(this).val().toString().toLowerCase().trim();
  const $tables = $('table');
  if (q) {
    $tables.each(function() {
      if ($(this).data('pagination')) $(this).data('pagination').destroy();
    });
    $('[data-search]').each(function(){ const txt=($(this).attr('data-search')||'').toLowerCase(); $(this).toggle(txt.includes(q)); });
  } else {
    $('[data-search]').show();
    $tables.each(function() {
      if ($(this).data('pagination')) $(this).data('pagination').restore();
    });
  }
});

$(document).on('input','.js-local-search',function(){
  const group = this.getAttribute('data-filter-group');
  if (group) { applyFilterGroup(group, this.value.toString().toLowerCase().trim()); return; }
  const q=$(this).val().toString().toLowerCase().trim();
  $('[data-search]').each(function(){ const txt=($(this).attr('data-search')||'').toLowerCase(); $(this).toggle(txt.includes(q)); });
});

$(document).on('change','.js-filter',function(){
  const group = this.getAttribute('data-filter-group');
  if (group) applyFilterGroup(group);
});

// Global basic protections & keys interception
$(document).on('contextmenu', e => {
  e.preventDefault();
});

window.addEventListener('keydown', e => {
  const isProtected = document.body && document.body.getAttribute('data-screenshot-protection') === '1';
  const key = e.key ? e.key.toLowerCase() : '';
  const code = e.code ? e.code.toLowerCase() : '';
  
  // Standard blocking: Ctrl+S, Ctrl+P, Ctrl+U, F12, Ctrl+Shift+I/J
  if ((e.ctrlKey && ['s', 'p', 'u'].includes(key)) || e.key === 'F12' || (e.ctrlKey && e.shiftKey && ['i', 'j'].includes(key))) {
    e.preventDefault();
    if (isProtected) {
      toast('Shortcut disabled for security reasons.', 'warning');
    }
  }
  
  if (isProtected) {
    const isWindows = navigator.userAgent.toLowerCase().includes('win');
    const isMac = navigator.userAgent.toLowerCase().includes('mac');

    // Detect PrintScreen (PrtScn) key
    const isPrintScreen = e.key === 'PrintScreen' || e.keyCode === 44 || key === 'printscreen' || code === 'printscreen';
    
    // Detect Insert key (shares key with PrintScreen on many laptops next to F12)
    const isInsert = e.key === 'Insert' || e.keyCode === 45 || key === 'insert' || code === 'insert';
    
    // Detect Win key on Windows immediately (covers Win+Shift+S, Win+PrtScn, etc. before OS intercepts)
    const isWinKey = isWindows && (e.metaKey || e.key === 'Meta' || e.key === 'win' || code === 'osleft' || code === 'osright');
    
    // Detect Ctrl key (Control button or held modifier)
    const isCtrlKey = e.ctrlKey || e.key === 'Control' || key === 'control' || code === 'controlleft' || code === 'controlright';
    
    // Detect Cmd + Shift on macOS (covers Cmd+Shift+3/4/5 before the third key is pressed)
    const isMacCmdShift = isMac && e.metaKey && e.shiftKey;
    
    // Backup explicit combos
    const isWinShiftS = e.metaKey && e.shiftKey && (key === 's' || code === 'keys');
    const isMacScreenshot = e.metaKey && e.shiftKey && ['3', '4', '5'].includes(key);
    
    if (isPrintScreen || isInsert || isWinKey || isCtrlKey || isMacCmdShift || isWinShiftS || isMacScreenshot) {
      if (isInsert || isWinShiftS || isMacScreenshot) {
        e.preventDefault();
        toast('Screenshots are strictly disabled on this portal.', 'error');
      }
      showSecurityShield();
      wipeClipboard();
      
      // Setup a series of delayed clipboard wipes to capture OS-level actions
      for (const delay of [50, 100, 200, 500]) {
        setTimeout(wipeClipboard, delay);
      }
    }
  }
}, { capture: true });

// Clipboard wipe helper
function wipeClipboard() {
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText("⛔ [CONFIDENTIALITY PROTOCOL] Screen capture restricted. This portal contains secure bank records protected under policy.")
      .catch(() => {});
  }
}

// Security Shield visibility helpers
let shieldTimeout = null;
function showSecurityShield() {
  const shield = document.getElementById('securityShield');
  if (shield) {
    if (shieldTimeout) clearTimeout(shieldTimeout);
    shieldTimeout = setTimeout(() => {
      if (document.hasFocus()) {
        return;
      }
      shield.classList.remove('d-none');
      document.body.classList.add('shield-active');
      // Force browser reflow to render and paint changes synchronously
      void document.body.offsetHeight;
      void shield.offsetHeight;
    }, 200);
  }
}

function hideSecurityShield() {
  if (shieldTimeout) clearTimeout(shieldTimeout);
  const shield = document.getElementById('securityShield');
  if (shield) {
    shield.classList.add('d-none');
    document.body.classList.remove('shield-active');
  }
}

// Handle blur, visibility change and copy/paste blocking
(function() {
  const isProtected = document.body.getAttribute('data-screenshot-protection') === '1';
  
  if (isProtected) {
    // Advanced Shield Events
    window.addEventListener('blur', showSecurityShield, { capture: true });
    window.addEventListener('focus', () => {
      hideSecurityShield();
      wipeClipboard();
    }, { capture: true });
    window.addEventListener('keyup', e => {
      const key = e.key ? e.key.toLowerCase() : '';
      const code = e.code ? e.code.toLowerCase() : '';
      const isPrintScreen = e.key === 'PrintScreen' || e.keyCode === 44 || key === 'printscreen' || code === 'printscreen';
      if (isPrintScreen) {
        showSecurityShield();
        wipeClipboard();
      }
    }, { capture: true });
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        showSecurityShield();
      } else {
        wipeClipboard();
      }
    }, { capture: true });
    
    // Allow clicking anywhere on the shield card to dismiss (if window is focused)
    $(document).on('click', '#securityShield', function() {
      if (document.hasFocus()) {
        hideSecurityShield();
      }
    });

    // Block copy & cut events
    document.addEventListener('copy', e => {
      e.preventDefault();
      wipeClipboard();
      toast('Copying content is disabled.', 'warning');
    });
    document.addEventListener('cut', e => {
      e.preventDefault();
      wipeClipboard();
      toast('Cutting content is disabled.', 'warning');
    });
    
    // DOM Integrity Protection (MutationObserver)
    const observer = new MutationObserver((mutations) => {
      const shield = document.getElementById('securityShield');
      if (!shield) {
        // If they deleted the element, reload immediately to restore security
        window.location.reload();
        return;
      }
      
      // Check if they tried to hide the shield by hacking attributes
      const isHidden = shield.classList.contains('d-none');
      const hasBadStyle = shield.style.display === 'none' || shield.style.opacity === '0' || shield.style.visibility === 'hidden';
      
      if (!document.hasFocus() && (isHidden || hasBadStyle)) {
        // Re-force visibility when blurred
        shield.classList.remove('d-none');
        shield.style.setProperty('display', 'flex', 'important');
        shield.style.setProperty('opacity', '1', 'important');
        shield.style.setProperty('visibility', 'visible', 'important');
      }
    });
    
    observer.observe(document.body, { childList: true, subtree: true, attributes: true });
  } else {
    // Standard basic blur for non-protected users (admins)
    window.addEventListener('blur', () => {
      document.body.style.filter = 'blur(4px)';
    });
    window.addEventListener('focus', () => {
      document.body.style.filter = 'none';
    });
  }
})();
(function(){
  function cssVar(name){return getComputedStyle(document.documentElement).getPropertyValue(name).trim();}
  function makeChart(id, config){const el=document.getElementById(id);if(!el||typeof Chart==='undefined')return null;return new Chart(el,config);}

  function bootDashboard(){
    const data=window.psnfDashboard; if(!data) return;
    const fileTypeEntries=Object.entries(data.fileTypes||{});
    const typeLabels=fileTypeEntries.map(x=>x[0]);
    const typeCounts=fileTypeEntries.map(x=>Number(x[1].count||0));
    const typeBytes=fileTypeEntries.map(x=>Number(x[1].bytes||0));
    const colors=['#3b82f6','#14b8a6','#f59e0b','#8b5cf6','#ef4444','#64748b'];

    makeChart('fileTypeChart',{
      type:'pie',
      data:{labels:typeLabels,datasets:[{data:typeCounts,backgroundColor:colors,borderWidth:0}]},
      options:{plugins:{legend:{position:'bottom',labels:{color:cssVar('--muted')}},tooltip:{enabled:true}},animation:{duration:700}}
    });

    makeChart('fileTypeTrendChart',{
      type:'bar',
      data:{labels:typeLabels,datasets:[{label:'Storage (bytes)',data:typeBytes,backgroundColor:'#4f8dff',borderRadius:8}]},
      options:{plugins:{legend:{display:false}},scales:{x:{ticks:{color:cssVar('--muted')},grid:{display:false}},y:{ticks:{color:cssVar('--muted')},grid:{color:'rgba(148,163,184,.2)'}}},animation:{duration:650}}
    });

    const trendDays=Object.keys((data.trends||{}).activity_days||{});
    const trendVals=Object.values((data.trends||{}).activity_days||{});
    makeChart('loginTrendChart',{
      type:'line',
      data:{labels:trendDays,datasets:[{label:'Login Activity',data:trendVals,borderColor:'#10b981',backgroundColor:'rgba(16,185,129,.2)',fill:true,tension:.35}]},
      options:{plugins:{legend:{display:false}},scales:{x:{ticks:{color:cssVar('--muted')}},y:{ticks:{color:cssVar('--muted')}}},animation:{duration:700}}
    });

    const storage=data.storageByFolder||[];
    makeChart('storageByFolderChart',{
      type:'bar',
      data:{labels:storage.map(x=>x.folder),datasets:[{label:'Storage',data:storage.map(x=>Number(x.bytes||0)),backgroundColor:'#6366f1',borderRadius:8}]},
      options:{plugins:{legend:{display:false}},scales:{x:{ticks:{color:cssVar('--muted')},grid:{display:false}},y:{ticks:{color:cssVar('--muted')},grid:{color:'rgba(148,163,184,.2)'}}}}
    });

    $('#cmdkInput').on('input',function(){
      const q=this.value.toLowerCase().trim();
      const pool=data.searchIndex||[];
      if(!q){return;}
      const results=pool.filter(r=>`${r.type} ${r.title} ${r.meta}`.toLowerCase().includes(q)).slice(0,8);
      const map={folder:'??',file:'??',user:'??',permission:'??',action:'?'};
      $('.cmdk-list').html(results.map(r=>`<a href="${window.location.origin}${r.url}" class="cmdk-item"><span>${map[r.type]||'�'} ${r.title}</span><small class="text-muted d-block">${r.meta}</small></a>`).join('') || '<div class="p-3 text-muted">No results</div>');
    });

    $('.tree-toggle').on('click',function(){ $(this).closest('.tree-node').toggleClass('collapsed'); });

    function selectedRows(){ return $('#permissionMatrix tbody .perm-row-check:checked').length; }
    function refreshBulk(){const n=selectedRows();$('#bulkCount').text(n);$('#bulkActionToolbar').toggleClass('d-none',n===0);}
    $(document).on('change','#permSelectAll',function(){ $('#permissionMatrix tbody .perm-row-check').prop('checked',this.checked); refreshBulk(); });
    $(document).on('change','.perm-row-check',refreshBulk);

    $(document).on('input','#permissionSearch',function(){
      const q=this.value.toLowerCase().trim();
      $('#permissionMatrix tbody tr').each(function(){const t=($(this).attr('data-search')||'').toLowerCase();$(this).toggle(t.includes(q));});
    });

    $('.js-bulk-action').on('click',function(){
      const action=$(this).data('action');
      if(!selectedRows()){toast('Select at least one row','warning');return;}
      if(!confirm(`Confirm bulk action: ${action}?`)) return;
      toast(`${action} started. You can undo for a few seconds.`, 'info');
      setTimeout(()=>toast('Bulk action complete','success'),800);
    });

    $('.file-preview-trigger').on('click',function(){
      const data=$(this).data('preview')||{};
      $('#previewDrawerContent').html(`<strong>${data.file_name||'File'}</strong><div class="mt-2">Title: ${data.title||''}</div><div>Folder: ${data.folder_name||'Unfiled'}</div><div>Size: ${(data.size||0).toLocaleString()} bytes</div><div class="mt-2 text-muted">Preview placeholder with metadata, permissions and activity tabs.</div>`);
      $('#filePreviewDrawer').addClass('open').attr('aria-hidden','false');
    });
    $('#closePreviewDrawer').on('click',()=>$('#filePreviewDrawer').removeClass('open').attr('aria-hidden','true'));

    $(document).on('click','.preview-tabs button',function(){ $('.preview-tabs button').removeClass('active'); $(this).addClass('active'); });

    $(document).on('click','.audit-detail-btn',function(){
      const d=$(this).data('event')||{};
      $('#auditDetailBody').html(`<div><strong>Action:</strong> ${d.event||''}</div><div><strong>Severity:</strong> ${d.severity||''}</div><div><strong>IP:</strong> ${d.ip||''}</div><div><strong>Time:</strong> ${d.created_at||''}</div><div class="mt-2"><strong>Meta:</strong> ${d.meta||''}</div>`);
      new bootstrap.Modal(document.getElementById('auditDetailModal')).show();
    });

    $('#exportAuditBtn,#exportMatrixBtn').on('click',()=>toast('Export queued','success'));
    $('.js-cleanup').on('click',()=>toast('Cleanup suggestions generated','info'));
    $('#bulkGrantBtn').on('click',()=>toast('Bulk grant mode enabled','info'));
    $('#bulkRevokeBtn').on('click',()=>toast('Bulk revoke mode enabled','warning'));
  }

  function initPagination(tableEl, itemsPerPage = 10) {
    const $table = $(tableEl);
    const $tbody = $table.find('tbody');
    const $rows = $tbody.find('tr:not(.text-muted)');
    
    if ($rows.length <= itemsPerPage) {
      const $prevPager = $table.next('.table-pagination');
      if ($prevPager.length) $prevPager.remove();
      $table.removeData('pagination');
      $rows.show();
      return;
    }
    
    let $pager = $table.next('.table-pagination');
    if (!$pager.length) {
      $pager = $('<div class="table-pagination d-flex justify-content-between align-items-center mt-3"></div>');
      $table.after($pager);
    }
    
    let currentPage = 1;
    const totalPages = Math.ceil($rows.length / itemsPerPage);
    
    function showPage(page) {
      currentPage = page;
      const start = (page - 1) * itemsPerPage;
      const end = start + itemsPerPage;
      
      $rows.each(function(index) {
        if (index >= start && index < end) {
          $(this).show();
        } else {
          $(this).hide();
        }
      });
      
      renderControls();
    }
    
    function renderControls() {
      let html = `<small class="text-muted">Showing ${Math.min($rows.length, (currentPage - 1) * itemsPerPage + 1)} to ${Math.min($rows.length, currentPage * itemsPerPage)} of ${$rows.length}</small>`;
      html += '<nav><ul class="pagination pagination-sm m-0">';
      html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a></li>`;
      for (let i = 1; i <= totalPages; i++) {
        html += `<li class="page-item ${currentPage === i ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
      }
      html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${currentPage + 1}">Next</a></li>`;
      html += '</ul></nav>';
      $pager.html(html);
    }
    
    $pager.off('click', 'a').on('click', 'a', function(e) {
      e.preventDefault();
      const page = parseInt($(this).data('page'));
      if (page >= 1 && page <= totalPages) {
        showPage(page);
      }
    });
    
    $table.data('pagination', {
      showPage,
      destroy: function() {
        $rows.show();
        $pager.hide();
      },
      restore: function() {
        $pager.show();
        showPage(currentPage);
      }
    });
    
    showPage(1);
  }

  window.addEventListener('load', function() {
    bootDashboard();
    $('.js-paginate').each(function() {
      initPagination(this);
    });
  });
})();
