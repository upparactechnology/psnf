<?php
/** @var array $app */
/** @var array $resources */
/** @var array $folders */
ob_start();
$formatSize = static function (int $bytes): string {
  if ($bytes >= 1024 * 1024 * 1024) return number_format($bytes / (1024 * 1024 * 1024), 2) . ' GB';
  if ($bytes >= 1024 * 1024) return number_format($bytes / (1024 * 1024), 2) . ' MB';
  if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
  return $bytes . ' B';
};
?>

<div class="panel card-glass p-3 mb-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">All Files</h5>
    <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#uploadFilesModal"><i class="bi bi-cloud-upload"></i> Upload Files</button>
  </div>
  <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
    <div class="d-flex gap-2 ms-auto">
      <input class="form-control form-control-sm js-local-search" data-filter-group="files" style="width: 320px;" placeholder="Search files">
          <select class="form-select form-select-sm js-filter" data-filter-group="files" data-filter-key="folder" style="max-width:180px">
            <option value="all">All folders</option>
            <option value="0">Unfiled</option>
            <?php foreach ($folders as $f): ?>
              <option value="<?= (int)$f['id'] ?>"><?= htmlspecialchars($f['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <select class="form-select form-select-sm js-filter" data-filter-group="files" data-filter-key="type" style="max-width:160px">
            <option value="all">All types</option>
            <option value="pdf">PDF</option>
            <option value="jpg">JPG</option>
            <option value="jpeg">JPEG</option>
            <option value="png">PNG</option>
            <option value="mp4">MP4</option>
            <option value="webm">WEBM</option>
            <option value="mov">MOV</option>
          </select>
        </div>
      </div>
      <div class="d-none justify-content-start align-items-center gap-2 mb-2 p-2 rounded bg-light border" id="bulkActionsToolbar">
        <span class="text-muted small fw-semibold ms-2"><span id="selectedCount">0</span> selected</span>
        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#bulkMoveModal"><i class="bi bi-folder-symlink"></i> Move Selected</button>
        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#bulkCopyModal"><i class="bi bi-copy"></i> Copy Selected</button>
      </div>
      <div class="table-responsive table-wrap">
        <table class="table table-modern js-paginate" id="filesTable">
          <thead><tr><th style="width: 40px;"><input type="checkbox" id="selectAllCheckbox" class="form-check-input"></th><th>Title</th><th>Folder</th><th>Type</th><th>Size</th><th class="text-end">Actions</th></tr></thead>
          <tbody id="filesTableBody">
          <?php foreach($resources as $r): ?>
            <?php $ext = strtolower(pathinfo((string)$r['file_name'], PATHINFO_EXTENSION)); ?>
            <?php $assigned = $resourceAssignments[(int)$r['id']] ?? []; ?>
            <tr class="clickable-row" data-url="<?= $app['base_url'] ?>/admin/resources/view?id=<?= (int)$r['id'] ?>" data-filter-group="files" data-folder="<?= (int)($r['folder_id'] ?? 0) ?>" data-type="<?= htmlspecialchars($ext) ?>" data-search="<?= htmlspecialchars(strtolower($r['title'].' '.$r['file_name'].' '.$r['folder_name'].' '.$r['mime_type'])) ?>">
              <td onclick="event.stopPropagation();"><input type="checkbox" class="row-checkbox form-check-input" value="<?= (int)$r['id'] ?>"></td>
              <td><?= htmlspecialchars($r['title']) ?><div class="text-muted small"><?= htmlspecialchars($r['file_name']) ?></div></td>
              <td><?= htmlspecialchars((string)$r['folder_name']) ?></td>
              <td><span class="chip"><?= htmlspecialchars($r['mime_type']) ?></span></td>
              <td><?= $formatSize((int)$r['file_size']) ?></td>
              <td class="text-end">
                <div class="dropdown d-inline-block">
                  <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false">
                    Actions
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><a class="dropdown-item" href="<?= $app['base_url'] ?>/admin/resources/view?id=<?= (int)$r['id'] ?>"><i class="bi bi-eye"></i> View</a></li>
                    <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#assignFileModal<?= (int)$r['id'] ?>"><i class="bi bi-person-fill-gear"></i> Assign (<?= count($assigned) ?>)</button></li>
                    <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editFileModal<?= (int)$r['id'] ?>"><i class="bi bi-pencil"></i> Edit Details</button></li>
                    <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#moveFileModal<?= (int)$r['id'] ?>"><i class="bi bi-folder-symlink"></i> Move</button></li>
                    <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#copyFileModal<?= (int)$r['id'] ?>"><i class="bi bi-copy"></i> Copy</button></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                      <form class="ajax-form m-0" action="<?= $app['base_url'] ?>/admin/resources/delete" method="post" onsubmit="return confirm('Delete this file?')">
                        <input type="hidden" name="resource_id" value="<?= (int)$r['id'] ?>">
                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash"></i> Delete</button>
                      </form>
                    </li>
                  </ul>
                </div>

                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

<!-- Upload Files Modal -->
<div class="modal fade" id="uploadFilesModal" tabindex="-1" aria-labelledby="uploadFilesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content card-glass">
      <div class="modal-header border-bottom-0">
        <h5 class="modal-title" id="uploadFilesModalLabel">Upload Files</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form class="ajax-form" action="<?= $app['base_url'] ?>/admin/resources/upload" method="post" enctype="multipart/form-data">
        <div class="modal-body text-start">
          <div class="mb-3">
            <label class="form-label">Folder</label>
            <div class="dropdown">
              <button class="form-select text-start dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="uploadFolderSelectBtn">
                Unfiled
              </button>
              <ul class="dropdown-menu w-100 custom-select-dropdown">
                <li><a class="dropdown-item" href="#" onclick="selectUploadFolder(0, 'Unfiled'); return false;">Unfiled</a></li>
                <?php foreach ($folders as $f): ?>
                  <li><a class="dropdown-item" href="#" onclick="selectUploadFolder(<?= (int)$f['id'] ?>, '<?= htmlspecialchars(addslashes($f['name'])) ?>'); return false;"><?= htmlspecialchars($f['name']) ?></a></li>
                <?php endforeach; ?>
              </ul>
              <input type="hidden" name="folder_id" id="uploadFolderInput" value="0">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Files</label>
            <div class="file-upload-wrapper border border-dashed rounded-3 p-4 text-center bg-light position-relative" style="border-width: 2px !important; border-color: var(--line) !important; border-style: dashed !important; transition: border-color 0.2s;">
              <i class="bi bi-cloud-arrow-up-fill text-primary" style="font-size: 2.2rem; display: block; margin-bottom: 8px;"></i>
              <span class="d-block text-muted small mb-2">Drag and drop files here or click to browse</span>
              <input class="form-control position-absolute top-0 start-0 w-100 h-100 opacity-0" name="files[]" type="file" multiple required style="cursor: pointer;" onchange="updateFileList(this)">
              <div id="file-list-preview" class="mt-2 text-start small text-primary fw-semibold"></div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-top-0">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-sm btn-primary">Upload Files</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php foreach($resources as $r): ?>
  <!-- Edit File Modal -->
  <div class="modal fade" id="editFileModal<?= (int)$r['id'] ?>" tabindex="-1" aria-labelledby="editFileModalLabel<?= (int)$r['id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content card-glass">
        <div class="modal-header border-bottom-0">
          <h5 class="modal-title" id="editFileModalLabel<?= (int)$r['id'] ?>">Edit File: <?= htmlspecialchars($r['title']) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form class="ajax-form" action="<?= $app['base_url'] ?>/admin/resources/update" method="post">
          <div class="modal-body text-start">
            <input type="hidden" name="resource_id" value="<?= (int)$r['id'] ?>">
            <div class="mb-3">
              <label class="form-label">Title</label>
              <input class="form-control" name="title" value="<?= htmlspecialchars($r['title']) ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Folder</label>
              <?php 
              $currentFolderName = 'Unfiled';
              foreach ($folders as $f) {
                if ((int)$f['id'] === (int)($r['folder_id'] ?? 0)) {
                  $currentFolderName = $f['name'];
                  break;
                }
              }
              ?>
              <div class="dropdown">
                <button class="form-select text-start dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="folderSelectBtn<?= (int)$r['id'] ?>">
                  <?= htmlspecialchars($currentFolderName) ?>
                </button>
                <ul class="dropdown-menu w-100 custom-select-dropdown">
                  <li><a class="dropdown-item" href="#" onclick="selectFolder(<?= (int)$r['id'] ?>, 0, 'Unfiled'); return false;">Unfiled</a></li>
                  <?php foreach ($folders as $f): ?>
                    <li><a class="dropdown-item" href="#" onclick="selectFolder(<?= (int)$r['id'] ?>, <?= (int)$f['id'] ?>, '<?= htmlspecialchars(addslashes($f['name'])) ?>'); return false;"><?= htmlspecialchars($f['name']) ?></a></li>
                  <?php endforeach; ?>
                </ul>
                <input type="hidden" name="folder_id" id="folderInput<?= (int)$r['id'] ?>" value="<?= (int)($r['folder_id'] ?? 0) ?>">
              </div>
            </div>
          </div>
          <div class="modal-footer border-top-0">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-sm btn-primary">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php $assigned = $resourceAssignments[(int)$r['id']] ?? []; ?>
  <!-- Modal -->
  <div class="modal fade" id="assignFileModal<?= (int)$r['id'] ?>" tabindex="-1" aria-labelledby="assignFileModalLabel<?= (int)$r['id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content card-glass">
        <div class="modal-header border-bottom-0">
          <h5 class="modal-title" id="assignFileModalLabel<?= (int)$r['id'] ?>">Assign File: <?= htmlspecialchars($r['title']) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form class="ajax-form" action="<?= $app['base_url'] ?>/admin/assignments/save" method="post">
          <div class="modal-body text-start">
            <input type="hidden" name="resource_id" value="<?= (int)$r['id'] ?>">
            <p class="text-muted small mb-3">Select staff members allowed to access this file:</p>
            <div class="d-flex flex-column gap-2" style="max-height: 280px; overflow-y: auto;">
              <?php foreach($staff as $s): ?>
                <div class="form-check text-start">
                  <input class="form-check-input" type="checkbox" name="staff_ids[]" value="<?= (int)$s['id'] ?>" id="file_<?= (int)$r['id'] ?>_staff_<?= (int)$s['id'] ?>" <?= isset($assigned[(int)$s['id']]) ? 'checked' : '' ?>>
                  <label class="form-check-label text-start" for="file_<?= (int)$r['id'] ?>_staff_<?= (int)$s['id'] ?>">
                    <strong><?= htmlspecialchars($s['name']) ?></strong> <span class="text-muted small">(<?= htmlspecialchars($s['email']) ?>)</span>
                  </label>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="modal-footer border-top-0">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-sm btn-primary">Save Assignments</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Move File Modal -->
  <div class="modal fade" id="moveFileModal<?= (int)$r['id'] ?>" tabindex="-1" aria-labelledby="moveFileModalLabel<?= (int)$r['id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content card-glass">
        <div class="modal-header border-bottom-0">
          <h5 class="modal-title" id="moveFileModalLabel<?= (int)$r['id'] ?>">Move File: <?= htmlspecialchars($r['title']) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form class="ajax-form" action="<?= $app['base_url'] ?>/admin/resources/move" method="post">
          <div class="modal-body text-start">
            <input type="hidden" name="resource_id" value="<?= (int)$r['id'] ?>">
            <div class="mb-3">
              <label class="form-label">Destination Folder</label>
              <?php 
              $currentFolderName = 'Unfiled';
              foreach ($folders as $f) {
                if ((int)$f['id'] === (int)($r['folder_id'] ?? 0)) {
                  $currentFolderName = $f['name'];
                  break;
                }
              }
              ?>
              <div class="dropdown">
                <button class="form-select text-start dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="moveFolderSelectBtn<?= (int)$r['id'] ?>">
                  <?= htmlspecialchars($currentFolderName) ?>
                </button>
                <ul class="dropdown-menu w-100 custom-select-dropdown" style="max-height: 200px; overflow-y: auto;">
                  <li><a class="dropdown-item" href="#" onclick="selectMoveFolder(<?= (int)$r['id'] ?>, 0, 'Unfiled'); return false;">Unfiled</a></li>
                  <?php foreach ($folders as $f): ?>
                    <li><a class="dropdown-item" href="#" onclick="selectMoveFolder(<?= (int)$r['id'] ?>, <?= (int)$f['id'] ?>, '<?= htmlspecialchars(addslashes($f['name'])) ?>'); return false;"><?= htmlspecialchars($f['name']) ?></a></li>
                  <?php endforeach; ?>
                </ul>
                <input type="hidden" name="folder_id" id="moveFolderInput<?= (int)$r['id'] ?>" value="<?= (int)($r['folder_id'] ?? 0) ?>">
              </div>
            </div>
          </div>
          <div class="modal-footer border-top-0">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-sm btn-primary">Move File</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Copy File Modal -->
  <div class="modal fade" id="copyFileModal<?= (int)$r['id'] ?>" tabindex="-1" aria-labelledby="copyFileModalLabel<?= (int)$r['id'] ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content card-glass">
        <div class="modal-header border-bottom-0">
          <h5 class="modal-title" id="copyFileModalLabel<?= (int)$r['id'] ?>">Copy File: <?= htmlspecialchars($r['title']) ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form class="ajax-form" action="<?= $app['base_url'] ?>/admin/resources/copy" method="post">
          <div class="modal-body text-start">
            <input type="hidden" name="resource_id" value="<?= (int)$r['id'] ?>">
            <div class="mb-3">
              <label class="form-label">Destination Folder</label>
              <?php 
              $currentFolderName = 'Unfiled';
              foreach ($folders as $f) {
                if ((int)$f['id'] === (int)($r['folder_id'] ?? 0)) {
                  $currentFolderName = $f['name'];
                  break;
                }
              }
              ?>
              <div class="dropdown">
                <button class="form-select text-start dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="copyFolderSelectBtn<?= (int)$r['id'] ?>">
                  <?= htmlspecialchars($currentFolderName) ?>
                </button>
                <ul class="dropdown-menu w-100 custom-select-dropdown" style="max-height: 200px; overflow-y: auto;">
                  <li><a class="dropdown-item" href="#" onclick="selectCopyFolder(<?= (int)$r['id'] ?>, 0, 'Unfiled'); return false;">Unfiled</a></li>
                  <?php foreach ($folders as $f): ?>
                    <li><a class="dropdown-item" href="#" onclick="selectCopyFolder(<?= (int)$r['id'] ?>, <?= (int)$f['id'] ?>, '<?= htmlspecialchars(addslashes($f['name'])) ?>'); return false;"><?= htmlspecialchars($f['name']) ?></a></li>
                  <?php endforeach; ?>
                </ul>
                <input type="hidden" name="folder_id" id="copyFolderInput<?= (int)$r['id'] ?>" value="<?= (int)($r['folder_id'] ?? 0) ?>">
              </div>
            </div>
          </div>
          <div class="modal-footer border-top-0">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-sm btn-primary">Copy File</button>
          </div>
        </form>
      </div>
    </div>
  </div>
<?php endforeach; ?>

  <!-- Bulk Move Modal -->
  <div class="modal fade" id="bulkMoveModal" tabindex="-1" aria-labelledby="bulkMoveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content card-glass">
        <div class="modal-header border-bottom-0">
          <h5 class="modal-title" id="bulkMoveModalLabel">Move Selected Files</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form class="ajax-form" action="<?= $app['base_url'] ?>/admin/resources/move" method="post" id="bulkMoveForm">
          <div class="modal-body text-start">
            <div id="bulkMoveIdsContainer"></div>
            <div class="mb-3">
              <label class="form-label">Destination Folder</label>
              <div class="dropdown">
                <button class="form-select text-start dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="bulkMoveFolderSelectBtn">
                  Unfiled
                </button>
                <ul class="dropdown-menu w-100 custom-select-dropdown" style="max-height: 200px; overflow-y: auto;">
                  <li><a class="dropdown-item" href="#" onclick="selectBulkMoveFolder(0, 'Unfiled'); return false;">Unfiled</a></li>
                  <?php foreach ($folders as $f): ?>
                    <li><a class="dropdown-item" href="#" onclick="selectBulkMoveFolder(<?= (int)$f['id'] ?>, '<?= htmlspecialchars(addslashes($f['name'])) ?>'); return false;"><?= htmlspecialchars($f['name']) ?></a></li>
                  <?php endforeach; ?>
                </ul>
                <input type="hidden" name="folder_id" id="bulkMoveFolderInput" value="0">
              </div>
            </div>
          </div>
          <div class="modal-footer border-top-0">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-sm btn-primary">Move Files</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bulk Copy Modal -->
  <div class="modal fade" id="bulkCopyModal" tabindex="-1" aria-labelledby="bulkCopyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content card-glass">
        <div class="modal-header border-bottom-0">
          <h5 class="modal-title" id="bulkCopyModalLabel">Copy Selected Files</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form class="ajax-form" action="<?= $app['base_url'] ?>/admin/resources/copy" method="post" id="bulkCopyForm">
          <div class="modal-body text-start">
            <div id="bulkCopyIdsContainer"></div>
            <div class="mb-3">
              <label class="form-label">Destination Folder</label>
              <div class="dropdown">
                <button class="form-select text-start dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="bulkCopyFolderSelectBtn">
                  Unfiled
                </button>
                <ul class="dropdown-menu w-100 custom-select-dropdown" style="max-height: 200px; overflow-y: auto;">
                  <li><a class="dropdown-item" href="#" onclick="selectBulkCopyFolder(0, 'Unfiled'); return false;">Unfiled</a></li>
                  <?php foreach ($folders as $f): ?>
                    <li><a class="dropdown-item" href="#" onclick="selectBulkCopyFolder(<?= (int)$f['id'] ?>, '<?= htmlspecialchars(addslashes($f['name'])) ?>'); return false;"><?= htmlspecialchars($f['name']) ?></a></li>
                  <?php endforeach; ?>
                </ul>
                <input type="hidden" name="folder_id" id="bulkCopyFolderInput" value="0">
              </div>
            </div>
          </div>
          <div class="modal-footer border-top-0">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-sm btn-primary">Copy Files</button>
          </div>
        </form>
      </div>
    </div>
  </div>

<script>
function selectFolder(resourceId, folderId, folderName) {
  document.getElementById('folderInput' + resourceId).value = folderId;
  document.getElementById('folderSelectBtn' + resourceId).innerText = folderName;
}
function selectMoveFolder(resourceId, folderId, folderName) {
  document.getElementById('moveFolderInput' + resourceId).value = folderId;
  document.getElementById('moveFolderSelectBtn' + resourceId).innerText = folderName;
}
function selectCopyFolder(resourceId, folderId, folderName) {
  document.getElementById('copyFolderInput' + resourceId).value = folderId;
  document.getElementById('copyFolderSelectBtn' + resourceId).innerText = folderName;
}
function selectUploadFolder(folderId, folderName) {
  document.getElementById('uploadFolderInput').value = folderId;
  document.getElementById('uploadFolderSelectBtn').innerText = folderName;
}
function selectBulkMoveFolder(folderId, folderName) {
  document.getElementById('bulkMoveFolderInput').value = folderId;
  document.getElementById('bulkMoveFolderSelectBtn').innerText = folderName;
}
function selectBulkCopyFolder(folderId, folderName) {
  document.getElementById('bulkCopyFolderInput').value = folderId;
  document.getElementById('bulkCopyFolderSelectBtn').innerText = folderName;
}
function updateFileList(input) {
  const preview = document.getElementById('file-list-preview');
  if (input.files && input.files.length > 0) {
    const names = Array.from(input.files).map(f => f.name).join(', ');
    preview.innerHTML = `<i class="bi bi-file-earmark-check-fill"></i> Selected: ${names}`;
  } else {
    preview.innerHTML = '';
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const selectAll = document.getElementById('selectAllCheckbox');
  const checkboxes = document.querySelectorAll('.row-checkbox');
  const toolbar = document.getElementById('bulkActionsToolbar');
  const selectedCountSpan = document.getElementById('selectedCount');
  
  const moveIdsContainer = document.getElementById('bulkMoveIdsContainer');
  const copyIdsContainer = document.getElementById('bulkCopyIdsContainer');

  function updateBulkToolbar() {
    const checked = Array.from(checkboxes).filter(cb => cb.checked);
    selectedCountSpan.textContent = checked.length;
    
    if (checked.length > 0) {
      toolbar.classList.remove('d-none');
      toolbar.classList.add('d-flex');
    } else {
      toolbar.classList.remove('d-flex');
      toolbar.classList.add('d-none');
    }

    if (moveIdsContainer) {
      moveIdsContainer.innerHTML = checked.map(cb => `<input type="hidden" name="resource_ids[]" value="${cb.value}">`).join('');
    }
    if (copyIdsContainer) {
      copyIdsContainer.innerHTML = checked.map(cb => `<input type="hidden" name="resource_ids[]" value="${cb.value}">`).join('');
    }
  }

  if (selectAll) {
    selectAll.addEventListener('change', () => {
      checkboxes.forEach(cb => {
        const row = cb.closest('tr');
        if (row && row.style.display !== 'none') {
          cb.checked = selectAll.checked;
        }
      });
      updateBulkToolbar();
    });
  }

  checkboxes.forEach(cb => {
    cb.addEventListener('change', updateBulkToolbar);
  });
});
</script>

<?php
$adminContent=ob_get_clean();
$title='File Management';
ob_start();
require __DIR__ . '/../layouts/admin_shell.php';
$content=ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
