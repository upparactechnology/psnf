<?php
/** @var array $app */
/** @var array $folder */
/** @var array $subfolders */
/** @var array $resources */
/** @var array $staff */
/** @var array $folderStaffList */
/** @var array $resourceAssignments */
/** @var array $folders */
ob_start();
$folderStaffList = $folderStaffList ?? [];
$formatSize = static function (int $bytes): string {
  if ($bytes >= 1024 * 1024 * 1024) return number_format($bytes / (1024 * 1024 * 1024), 2) . ' GB';
  if ($bytes >= 1024 * 1024) return number_format($bytes / (1024 * 1024), 2) . ' MB';
  if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
  return $bytes . ' B';
};
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h6 class="mb-0 section-title">Folder: <?= htmlspecialchars($folder['name']) ?></h6>
  <a class="btn btn-sm btn-outline-secondary" href="<?= $app['base_url'] ?>/admin/folders">Back to Folders</a>
</div>

<div class="row g-3">
  <div class="col-lg-4"><div class="card-glass p-3">
    <h6>Edit Folder</h6>
    <form class="ajax-form" action="<?= $app['base_url'] ?>/admin/folders/update" method="post">
      <input type="hidden" name="folder_id" value="<?= (int)$folder['id'] ?>">
      <input type="hidden" name="parent_id" value="<?= (int)($folder['parent_id'] ?? 0) ?>">
      <input class="form-control mb-2" name="name" value="<?= htmlspecialchars($folder['name']) ?>" required>
      <button class="btn btn-outline-primary">Update</button>
    </form>
    <form class="ajax-form mt-2" action="<?= $app['base_url'] ?>/admin/folders/delete" method="post" onsubmit="return confirm('Delete this folder?')">
      <input type="hidden" name="folder_id" value="<?= (int)$folder['id'] ?>">
      <button class="btn btn-outline-danger">Delete</button>
    </form>
  </div></div>

  <div class="col-lg-4"><div class="card-glass p-3">
    <h6>Create Subfolder</h6>
    <form class="ajax-form" action="<?= $app['base_url'] ?>/admin/folders/create" method="post">
      <input type="hidden" name="parent_id" value="<?= (int)$folder['id'] ?>">
      <input class="form-control mb-2" name="name" placeholder="Folder name" required>
      <button class="btn btn-primary">Create Subfolder</button>
    </form>
  </div></div>

  <div class="col-lg-4"><div class="card-glass p-3">
    <h6>Upload to This Folder</h6>
    <form class="ajax-form" action="<?= $app['base_url'] ?>/admin/resources/upload" method="post" enctype="multipart/form-data">
      <input type="hidden" name="folder_id" value="<?= (int)$folder['id'] ?>">
      <input class="form-control mb-2" name="files[]" type="file" multiple required>
      <button class="btn btn-success">Upload Files</button>
    </form>
  </div></div>
</div>

<?php if (!empty($subfolders)): ?>
<div class="card-glass p-3 mt-3">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="mb-0 section-title">Subfolders</h6>
    <span class="badge rounded-pill text-bg-secondary"><?= count($subfolders) ?> total</span>
  </div>
  <div class="table-responsive">
    <table class="table table-modern">
      <tr><th>ID</th><th>Name</th><th class="text-end">Actions</th></tr>
      <?php foreach($subfolders as $sf): ?>
      <tr class="clickable-row" data-url="<?= $app['base_url'] ?>/admin/folders/view?id=<?= (int)$sf['id'] ?>" data-search="<?= htmlspecialchars(strtolower($sf['name'].' '.$sf['id'])) ?>">
        <td><?= (int)$sf['id'] ?></td>
        <td><?= htmlspecialchars($sf['name']) ?></td>
        <td class="text-end">
          <div class="dropdown d-inline-block">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false">
              Actions
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
              <li><a class="dropdown-item" href="<?= $app['base_url'] ?>/admin/folders/view?id=<?= (int)$sf['id'] ?>"><i class="bi bi-eye"></i> View</a></li>
              <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editFolderModal<?= (int)$sf['id'] ?>"><i class="bi bi-pencil"></i> Rename</button></li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <form class="ajax-form m-0" action="<?= $app['base_url'] ?>/admin/folders/delete" method="post" onsubmit="return confirm('Delete this folder?')">
                  <input type="hidden" name="folder_id" value="<?= (int)$sf['id'] ?>">
                  <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash"></i> Delete</button>
                </form>
              </li>
            </ul>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>
<?php endif; ?>

<div class="card-glass p-3 mt-3">
  <h6 class="section-title">Files in Folder</h6>
  <div class="d-none justify-content-start align-items-center gap-2 mb-2 p-2 rounded bg-light border" id="bulkActionsToolbar">
    <span class="text-muted small fw-semibold ms-2"><span id="selectedCount">0</span> selected</span>
    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#bulkMoveModal"><i class="bi bi-folder-symlink"></i> Move Selected</button>
    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#bulkCopyModal"><i class="bi bi-copy"></i> Copy Selected</button>
  </div>
  <div class="table-responsive">
    <table class="table table-modern js-paginate" id="filesTable">
      <thead>
        <tr>
          <th style="width: 40px;"><input type="checkbox" id="selectAllCheckbox" class="form-check-input"></th>
          <th style="width: 40px;"></th>
          <th>Title</th>
          <th>Type</th>
          <th>Size</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody id="filesTableBody">
      <?php foreach($resources as $r): ?>
      <?php 
        $assigned = $resourceAssignments[(int)$r['id']] ?? []; 
        $uniqueAccessCount = count(array_unique(array_merge($folderStaffList, array_keys($assigned))));
      ?>
      <tr class="clickable-row draggable-row" draggable="true" data-id="<?= (int)$r['id'] ?>" data-url="<?= $app['base_url'] ?>/admin/resources/view?id=<?= (int)$r['id'] ?>" data-search="<?= htmlspecialchars(strtolower($r['title'].' '.$r['file_name'].' '.$r['mime_type'])) ?>">
        <td onclick="event.stopPropagation();"><input type="checkbox" class="row-checkbox form-check-input" value="<?= (int)$r['id'] ?>"></td>
        <td class="align-middle text-center drag-handle-cell" style="cursor: grab;"><i class="bi bi-grid-3x2-gap-fill text-muted"></i></td>
        <td><?= htmlspecialchars($r['title']) ?></td>
        <td><span class="chip"><?= htmlspecialchars($r['mime_type']) ?></span></td>
        <td><?= $formatSize((int)$r['file_size']) ?></td>
        <td class="text-end">
          <div class="dropdown d-inline-block">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false">
              Actions
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
              <li><a class="dropdown-item" href="<?= $app['base_url'] ?>/admin/resources/view?id=<?= (int)$r['id'] ?>"><i class="bi bi-eye"></i> View</a></li>
              <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#assignFileModal<?= (int)$r['id'] ?>"><i class="bi bi-person-fill-gear"></i> Assign (<?= $uniqueAccessCount ?>)</button></li>
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
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($resources)): ?><tr class="no-files-tr"><td colspan="5" class="text-muted text-center">No files in this folder.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if (!empty($subfolders)): ?>
  <?php foreach($subfolders as $sf): ?>
    <!-- Edit Folder Modal -->
    <div class="modal fade" id="editFolderModal<?= (int)$sf['id'] ?>" tabindex="-1" aria-labelledby="editFolderModalLabel<?= (int)$sf['id'] ?>" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-glass">
          <div class="modal-header border-bottom-0">
            <h5 class="modal-title" id="editFolderModalLabel<?= (int)$sf['id'] ?>">Rename Folder: <?= htmlspecialchars($sf['name']) ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form class="ajax-form" action="<?= $app['base_url'] ?>/admin/folders/update" method="post">
            <div class="modal-body text-start">
              <input type="hidden" name="folder_id" value="<?= (int)$sf['id'] ?>">
              <input type="hidden" name="parent_id" value="<?= (int)($sf['parent_id'] ?? 0) ?>">
              <div class="mb-3">
                <label class="form-label">Folder Name</label>
                <input class="form-control" name="name" value="<?= htmlspecialchars($sf['name']) ?>" required>
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
  <?php endforeach; ?>
<?php endif; ?>

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
                <?php
                $hasFolderAccess = in_array((int)$s['id'], $folderStaffList, true);
                $isDirectlyAssigned = isset($assigned[(int)$s['id']]);
                $isChecked = $hasFolderAccess || $isDirectlyAssigned;
                ?>
                <div class="form-check text-start d-flex align-items-center justify-content-between p-2 rounded" style="border: 1px solid var(--line); background: <?= $hasFolderAccess ? 'rgba(37, 99, 235, 0.05)' : 'transparent' ?>;">
                  <div class="d-flex align-items-center">
                    <input class="form-check-input ms-0 me-2" type="checkbox" name="staff_ids[]" value="<?= (int)$s['id'] ?>" id="file_<?= (int)$r['id'] ?>_staff_<?= (int)$s['id'] ?>" <?= $isChecked ? 'checked' : '' ?> <?= $hasFolderAccess ? 'disabled' : '' ?>>
                    <?php if ($hasFolderAccess): ?>
                      <input type="hidden" name="staff_ids[]" value="<?= (int)$s['id'] ?>">
                    <?php endif; ?>
                    <label class="form-check-label text-start mb-0" for="file_<?= (int)$r['id'] ?>_staff_<?= (int)$s['id'] ?>">
                      <strong><?= htmlspecialchars($s['name']) ?></strong> <br><span class="text-muted small"><?= htmlspecialchars($s['email']) ?></span>
                    </label>
                  </div>
                  <?php if ($hasFolderAccess): ?>
                    <span class="badge bg-primary text-white ms-2" style="font-size:0.75rem;">Inherited (Folder)</span>
                  <?php elseif ($isDirectlyAssigned): ?>
                    <span class="badge bg-success text-white ms-2" style="font-size:0.75rem;">Direct File</span>
                  <?php endif; ?>
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

<style>
.draggable-row.dragging {
  opacity: 0.45;
  background-color: rgba(37, 99, 235, 0.08) !important;
  border: 1px dashed var(--primary);
}
.drag-handle-cell {
  cursor: grab;
  user-select: none;
}
.drag-handle-cell:active {
  cursor: grabbing;
}
</style>

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
function selectBulkMoveFolder(folderId, folderName) {
  document.getElementById('bulkMoveFolderInput').value = folderId;
  document.getElementById('bulkMoveFolderSelectBtn').innerText = folderName;
}
function selectBulkCopyFolder(folderId, folderName) {
  document.getElementById('bulkCopyFolderInput').value = folderId;
  document.getElementById('bulkCopyFolderSelectBtn').innerText = folderName;
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

  const tbody = document.getElementById('filesTableBody');
  if (!tbody) return;

  let dragEl = null;

  tbody.addEventListener('dragstart', (e) => {
    const tr = e.target.closest('.draggable-row');
    if (!tr) return;
    dragEl = tr;
    tr.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', tr.dataset.id);
  });

  tbody.addEventListener('dragend', (e) => {
    if (dragEl) {
      dragEl.classList.remove('dragging');
      dragEl = null;
    }
    saveOrder();
  });

  tbody.addEventListener('dragover', (e) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    const tr = e.target.closest('.draggable-row');
    if (!tr || tr === dragEl) return;

    const rect = tr.getBoundingClientRect();
    const next = (e.clientY - rect.top) / (rect.bottom - rect.top) > 0.5;
    tbody.insertBefore(dragEl, next ? tr.nextSibling : tr);
  });

  function saveOrder() {
    const ids = [];
    tbody.querySelectorAll('.draggable-row').forEach(row => {
      ids.push(row.dataset.id);
    });

    $.post('<?= $app['base_url'] ?>/admin/resources/reorder', { ids: ids }, function(res) {
      if (res && res.ok) {
        toast(res.message || 'Order updated successfully', 'success');
      } else {
        toast(res.message || 'Failed to update order', 'error');
      }
    }, 'json').fail(function() {
      toast('Failed to update order due to network error.', 'error');
    });
  }
});
</script>

<?php
$adminContent=ob_get_clean();
$title='View Folder';
ob_start();
require __DIR__ . '/../layouts/admin_shell.php';
$content=ob_get_clean();
require __DIR__ . '/../layouts/main.php';
?>
