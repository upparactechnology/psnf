<?php
$layout    = 'app';
$pageTitle = 'Student Transport Assignments';
$breadcrumbs = [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Transport Workspace', 'url' => '/transport/overview'], ['label' => 'Student Assignments']];
ob_start();
?>

<?php
$addressMap = [];
foreach($unassignedStudents as $stu) {
    $addressMap[$stu['id']] = $stu['address'] ?? '';
}
?>

<div x-data="assignModalData()" x-init="initMap()" class="space-y-6 max-w-6xl mx-auto">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Student Transport Assignments</h1>
            <p class="text-xs text-slate-500 mt-0.5">Map students to routes, assigned vehicles, drivers, pickup stops & pickup times</p>
        </div>
        <button @click="assignModal = true" class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-sm">+ Assign Student to Route</button>
    </div>

    <!-- Student Assignments Table -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/40 overflow-hidden">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 dark:bg-slate-800 text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider text-2xs">
                <tr>
                    <th class="p-4">Student</th>
                    <th class="p-4">Assigned Driver & Bus</th>
                    <th class="p-4">Pickup Point</th>
                    <th class="p-4 text-center">Pickup Time</th>
                    <th class="p-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                <?php if (empty($assignments)): ?>
                <tr>
                    <td colspan="5" class="p-8 text-center text-slate-400">No students are currently assigned to any transport route.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($assignments as $a): ?>
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                        <td class="p-4">
                            <div class="font-bold text-slate-900 dark:text-white"><?= e($a['first_name'] . ' ' . $a['last_name']) ?></div>
                            <div class="text-xs text-slate-500 mt-0.5"><?= e($a['admission_number']) ?></div>
                        </td>
                        <td class="p-4">
                            <div class="font-medium text-slate-700 dark:text-slate-300"><?= e($a['driver_name']) ?></div>
                            <div class="text-xs font-mono text-indigo-500 mt-0.5"><?= e($a['driver_phone']) ?></div>
                        </td>
                        <td class="p-4 text-slate-600 dark:text-slate-400"><?= e($a['pickup_point'] ?? 'N/A') ?></td>
                        <td class="p-4 text-center">
                            <span class="inline-flex items-center px-2 py-1 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono text-xs">
                                <?= e(date('h:i A', strtotime($a['pickup_time']))) ?>
                            </span>
                        </td>
                        <td class="p-4 text-center flex items-center justify-center gap-2">
                            <button type="button" @click="openEditModal(<?= $a['id'] ?>, '<?= addslashes(e($a['pickup_point'])) ?>', '<?= addslashes(e($a['pickup_time'])) ?>', <?= (float)($a['pickup_lat'] ?? 0) ?>, <?= (float)($a['pickup_lng'] ?? 0) ?>)" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-lg font-medium text-xs transition-colors">Edit</button>
                            <form action="<?= url("transport/assignments/{$a['id']}/remove") ?>" method="POST" class="inline-block" onsubmit="return confirm('Remove student from transport route?')">
                                <?= \Core\View::csrf() ?>
                                <button type="submit" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg font-medium text-xs transition-colors">Remove</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Assign Modal -->
    <div x-show="assignModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" x-cloak>
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm" @click="assignModal = false"></div>
        <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 z-10" @click.stop>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Assign Student to Transport Route</h3>
            
            <form x-ref="assignForm" @submit.prevent="$refs.assignForm.action = '<?= url('transport') ?>/' + $refs.driverSelect.value + '/assign'; $refs.assignForm.submit()" method="POST" class="space-y-4 text-xs">
                <?= \Core\View::csrf() ?>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Select Driver</label>
                        <select x-ref="driverSelect" name="driver_id" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                            <?php foreach ($drivers as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Select Student</label>
                        <select name="student_id" x-model="selectedStudent" @change="pickupPoint = addresses[selectedStudent] || ''" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                            <option value="" disabled>-- Select a Student --</option>
                            <?php foreach ($unassignedStudents as $stu): ?>
                                <option value="<?= $stu['id'] ?>"><?= e($stu['first_name'] . ' ' . $stu['last_name'] . ' (' . $stu['admission_number'] . ')') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pickup Point</label>
                        <input type="text" name="pickup_point" x-model="pickupPoint" placeholder="e.g. 5th Cross, Gandhi Nagar" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pickup Time</label>
                        <input type="text" name="pickup_time" value="08:00 AM" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-mono">
                    </div>
                </div>
                
                <!-- Interactive Map for Pin Dropping -->
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Exact Pickup Location (Click to drop pin)</label>
                    <div id="assign-map" class="w-full h-48 rounded-xl border border-slate-200 dark:border-slate-700 z-10" style="position: relative;"></div>
                    <input type="hidden" name="pickup_lat" id="pickup_lat" required>
                    <input type="hidden" name="pickup_lng" id="pickup_lng" required>
                    <p class="text-[10px] text-slate-500 mt-1">Please click on the map to mark the exact GPS coordinates for the driver.</p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="assignModal = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-500">Save Assignment</button>
                </div>
            </form>
        </div>
    </div>

    </div>

    <!-- Edit Modal -->
    <div x-show="editModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" x-cloak>
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm" @click="editModal = false"></div>
        <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 z-10" @click.stop>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit Assignment</h3>
            
            <form x-ref="editForm" :action="'<?= url('transport/assignments') ?>/' + editId + '/update'" method="POST" class="space-y-4 text-xs">
                <?= \Core\View::csrf() ?>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pickup Point</label>
                        <input type="text" name="pickup_point" x-model="editPickupPoint" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pickup Time</label>
                        <input type="text" name="pickup_time" x-model="editPickupTime" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-slate-900 dark:text-white font-mono">
                    </div>
                </div>
                
                <!-- Interactive Map for Pin Dropping -->
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Exact Pickup Location (Click to drop pin)</label>
                    <div id="edit-map" class="w-full h-48 rounded-xl border border-slate-200 dark:border-slate-700 z-10" style="position: relative;"></div>
                    <input type="hidden" name="pickup_lat" id="edit_pickup_lat" x-model="editLat" required>
                    <input type="hidden" name="pickup_lng" id="edit_pickup_lng" x-model="editLng" required>
                    <p class="text-[10px] text-slate-500 mt-1">Please click on the map to mark the exact GPS coordinates for the driver.</p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-500">Update</button>
                </div>
            </form>
        </div>
    </div>

</div>
<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    let map = null;
    let marker = null;
    let editMap = null;
    let editMarker = null;
    
    function assignModalData() {
        return {
            assignModal: false,
            selectedStudent: "",
            pickupPoint: "",
            addresses: <?= json_encode($addressMap) ?>,
            
            // Edit Modal State
            editModal: false,
            editId: '',
            editPickupPoint: '',
            editPickupTime: '',
            editLat: 0,
            editLng: 0,
            
            openEditModal(id, point, time, lat, lng) {
                this.editId = id;
                this.editPickupPoint = point;
                this.editPickupTime = time;
                this.editLat = lat;
                this.editLng = lng;
                this.editModal = true;
                
                setTimeout(() => {
                    if (!editMap) {
                        editMap = L.map('edit-map').setView([lat || 21.1702, lng || 72.8311], 15);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap'
                        }).addTo(editMap);

                        editMap.on('click', (e) => {
                            this.editLat = e.latlng.lat;
                            this.editLng = e.latlng.lng;
                            document.getElementById('edit_pickup_lat').value = this.editLat;
                            document.getElementById('edit_pickup_lng').value = this.editLng;
                            if (editMarker) editMarker.setLatLng(e.latlng);
                            else editMarker = L.marker(e.latlng).addTo(editMap);
                        });
                    } else {
                        editMap.setView([lat || 21.1702, lng || 72.8311], 15);
                    }
                    
                    if (lat && lng) {
                        if (editMarker) {
                            editMarker.setLatLng([lat, lng]);
                        } else {
                            editMarker = L.marker([lat, lng]).addTo(editMap);
                        }
                    } else if (editMarker) {
                        editMap.removeLayer(editMarker);
                        editMarker = null;
                    }
                    editMap.invalidateSize();
                }, 100);
            },
            
            initMap() {
                if (!map) {
                    // Default to School location (Surat)
                    map = L.map('assign-map').setView([21.1702, 72.8311], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap'
                    }).addTo(map);

                    map.on('click', (e) => {
                        const lat = e.latlng.lat;
                        const lng = e.latlng.lng;
                        
                        document.getElementById('pickup_lat').value = lat;
                        document.getElementById('pickup_lng').value = lng;
                        
                        if (marker) {
                            marker.setLatLng(e.latlng);
                        } else {
                            marker = L.marker(e.latlng).addTo(map);
                        }
                    });
                }
                
                this.$watch('assignModal', value => {
                    if (value && map) {
                        setTimeout(() => map.invalidateSize(), 100);
                    }
                });
            }
        };
    }
</script>

<?php
$content = ob_get_clean();
?>
