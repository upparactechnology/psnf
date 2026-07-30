<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Controller;
use Core\Application;
use Core\Session;
use App\Models\{TransportRoute, Student, ActivityLog};

class TransportController extends Controller
{
    private function db()
    {
        return Application::$app->db;
    }

    public function overview(): string
    {
        $tenantId = \Core\Database::getTenantId();
        $totalVehicles = (int) ($this->db()->selectOne("SELECT COUNT(*) as cnt FROM transport_vehicles")['cnt'] ?? 2);
        $totalDrivers  = (int) ($this->db()->selectOne("SELECT COUNT(*) as cnt FROM employees e JOIN designations des ON e.designation_id = des.id WHERE des.title = 'Driver'")['cnt'] ?? 0);
        $totalStudents = (int) ($this->db()->selectOne("SELECT COUNT(*) as cnt FROM student_transport")['cnt'] ?? 0);
        $activeDrivers = (int) ($this->db()->selectOne("SELECT COUNT(*) as cnt FROM employees e JOIN designations des ON e.designation_id = des.id WHERE des.title = 'Driver' AND e.route_status != 'inactive'")['cnt'] ?? 0);

        $routes = $this->db()->select("
            SELECT e.id, e.first_name, e.last_name, e.phone, e.route_status, COUNT(st.id) as student_count, CONCAT(e.first_name, ' ', e.last_name) as route_name
            FROM employees e
            JOIN designations des ON e.designation_id = des.id
            LEFT JOIN student_transport st ON st.driver_id = e.id
            WHERE e.tenant_id = ? AND des.title = 'Driver'
            GROUP BY e.id ORDER BY e.id ASC
        ", [$tenantId]);

        $tomorrowStr = date('Y-m-d', strtotime('+1 day'));
        $tomorrowBusStudents = $this->db()->select("
            SELECT a.*, s.first_name, s.last_name, s.class, s.section, CONCAT(e.first_name, ' ', e.last_name) as driver_name
            FROM attendance a
            JOIN students s ON s.id = a.student_id
            JOIN student_transport st ON st.student_id = s.id
            JOIN employees e ON e.id = st.driver_id
            WHERE a.tenant_id = ? AND a.date = ? AND a.use_bus_transport = 1
        ", [$tenantId, $tomorrowStr]);

        return $this->view('transport/overview', compact('totalVehicles', 'activeDrivers', 'totalDrivers', 'totalStudents', 'routes', 'tomorrowBusStudents', 'tomorrowStr'));
    }

    public function routes(): string
    {
        $tenantId = \Core\Database::getTenantId();
        $routes = $this->db()->select("
            SELECT tr.*, COUNT(st.id) as student_count
            FROM transport_routes tr
            LEFT JOIN student_transport st ON st.route_id = tr.id
            WHERE tr.tenant_id = ?
            GROUP BY tr.id ORDER BY tr.route_name ASC
        ", [$tenantId]);

        return $this->view('transport/routes', compact('routes'));
    }

    public function vehicles(): string
    {
        $vehicles = $this->db()->select("SELECT v.*, r.route_name, CONCAT(e.first_name, ' ', e.last_name) as driver_name 
                                        FROM transport_vehicles v 
                                        LEFT JOIN transport_routes r ON v.route_id = r.id 
                                        LEFT JOIN employees e ON v.driver_id = e.id 
                                        ORDER BY v.id ASC");
        return $this->view('transport/vehicles', compact('vehicles'));
    }

    public function drivers(): string
    {
        $drivers = $this->db()->select("SELECT e.*, des.title as designation_title, v.vehicle_number 
                                       FROM employees e 
                                       JOIN designations des ON e.designation_id = des.id
                                       LEFT JOIN transport_vehicles v ON v.driver_id = e.id 
                                       WHERE des.title = 'Driver'
                                       ORDER BY e.id ASC");
        return $this->view('transport/drivers', compact('drivers'));
    }

    public function studentAssignments(): string
    {
        $tenantId = \Core\Database::getTenantId();
        $assignments = $this->db()->select("
            SELECT st.*, s.first_name, s.last_name, s.admission_number, CONCAT(e.first_name, ' ', e.last_name) as driver_name, e.phone as driver_phone
            FROM student_transport st
            JOIN students s ON s.id = st.student_id
            JOIN employees e ON e.id = st.driver_id
            WHERE e.tenant_id = ? AND s.deleted_at IS NULL
            ORDER BY e.first_name ASC, s.first_name ASC
        ", [$tenantId]);

        $unassignedStudents = $this->db()->select("
            SELECT id, first_name, last_name, admission_number, address
            FROM students
            WHERE tenant_id = ? AND deleted_at IS NULL AND id NOT IN (
                SELECT student_id FROM student_transport WHERE driver_id IS NOT NULL
            )
            ORDER BY first_name ASC
        ", [$tenantId]);

        $drivers = $this->db()->select("SELECT e.id, CONCAT(e.first_name, ' ', e.last_name) as name FROM employees e JOIN designations des ON e.designation_id = des.id WHERE e.tenant_id = ? AND des.title = 'Driver' ORDER BY e.first_name ASC", [$tenantId]);

        return $this->view('transport/student_assignments', compact('assignments', 'unassignedStudents', 'drivers'));
    }

    public function settings(): string
    {
        $campusLat = $this->getSetting('campus_lat', '23.0225');
        $campusLng = $this->getSetting('campus_lng', '72.5714');
        return $this->view('transport/settings', compact('campusLat', 'campusLng'));
    }

    public function storeSettings(): string
    {
        $lat = \Core\Application::$app->request->input('campus_lat');
        $lng = \Core\Application::$app->request->input('campus_lng');
        
        if ($lat !== null && $lng !== null) {
            $this->db()->query("INSERT INTO settings (`key`, `value`) VALUES ('campus_lat', ?) ON DUPLICATE KEY UPDATE `value` = ?", [$lat, $lat]);
            $this->db()->query("INSERT INTO settings (`key`, `value`) VALUES ('campus_lng', ?) ON DUPLICATE KEY UPDATE `value` = ?", [$lng, $lng]);
            \Core\Session::flash('success', 'Campus location updated successfully.');
        }
        
        \Core\Application::$app->response->redirect('/transport/settings');
        exit();
    }

    private function getSetting($key, $default = null) {
        $row = $this->db()->selectOne("SELECT value FROM settings WHERE `key` = ?", [$key]);
        return $row ? $row['value'] : $default;
    }

    public function tracking(): string
    {
        $tenantId = \Core\Database::getTenantId();

        $routes = $this->db()->select("
            SELECT e.id, e.first_name, e.last_name, e.phone, e.route_status, COUNT(st.id) as student_count, 
            CONCAT(e.first_name, ' ', e.last_name) as route_name, CONCAT(e.first_name, ' ', e.last_name) as name, 
            'No Bus' as bus_number, CONCAT(e.first_name, ' ', e.last_name) as driver_name, e.phone as driver_phone,
            e.current_latitude as lat, e.current_longitude as lng, e.current_speed as speed, e.route_status as status,
            'No Bus' as bus
            FROM employees e
            JOIN designations des ON e.designation_id = des.id
            LEFT JOIN student_transport st ON st.driver_id = e.id
            WHERE e.tenant_id = ? AND des.title = 'Driver'
            GROUP BY e.id
            ORDER BY e.route_status DESC, e.first_name ASC
        ", [$tenantId]);

        $campusLat = $this->getSetting('campus_lat', '23.0225');
        $campusLng = $this->getSetting('campus_lng', '72.5714');

        return $this->view('transport/tracking', compact('routes', 'campusLat', 'campusLng'));
    }

    public function liveData(): string
    {
        $tenantId = \Core\Database::getTenantId();

        $rows = $this->db()->select("
            SELECT e.id, CONCAT(e.first_name, ' ', e.last_name) as driver, CONCAT(e.first_name, ' ', e.last_name) as name, 'No Bus' as bus,
                   e.phone, e.route_status as status,
                   e.current_latitude  as lat,
                   e.current_longitude as lng,
                   e.current_speed     as speed,
                   e.last_updated_at   as updated_at,
                   COUNT(st.id) as students
            FROM employees e
            JOIN designations des ON e.designation_id = des.id
            LEFT JOIN student_transport st ON st.driver_id = e.id
            WHERE e.tenant_id = ? AND des.title = 'Driver'
            GROUP BY e.id
        ", [$tenantId]);

        $data = array_map(fn($r) => [
            'id'         => (int)$r['id'],
            'name'       => $r['name'],
            'bus'        => $r['bus'],
            'driver'     => $r['driver'],
            'phone'      => $r['phone'],
            'status'     => $r['status'],
            'lat'        => (float)($r['lat']  ?? 13.0827),
            'lng'        => (float)($r['lng']  ?? 80.2707),
            'speed'      => (float)($r['speed'] ?? 0.0),
            'students'   => (int)$r['students'],
            'updated_at' => $r['updated_at'] ? date('h:i A', strtotime($r['updated_at'])) : 'Never',
        ], $rows);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'routes' => $data]);
        exit();
    }

    public function driverRouteData(): string
    {
        header('Content-Type: application/json');
        
        $userId = auth()['id'] ?? 0;
        $driver = $this->db()->selectOne("
            SELECT e.id, e.first_name, e.last_name, 'No Bus' as bus, e.phone, e.route_status as status,
                   e.current_latitude as lat, e.current_longitude as lng, e.current_speed as speed
            FROM employees e
            JOIN designations des ON e.designation_id = des.id
            WHERE e.user_id = ? AND des.title = 'Driver'
            LIMIT 1
        ", [$userId]);

        if (!$driver) {
            echo json_encode(['success' => false, 'message' => 'Driver record not found.']);
            exit();
        }

        // Fetch active trip
        $activeTrip = $this->db()->selectOne("SELECT id FROM driver_trips WHERE driver_id = ? AND status = 'active'", [$driver['id']]);
        $tripId = $activeTrip ? $activeTrip['id'] : 0;

        // Fetch assigned students
        $students = $this->db()->select("
            SELECT st.id as assignment_id, st.pickup_point as address, st.pickup_lat, st.pickup_lng, st.pickup_time,
                   s.id, s.first_name, s.last_name, s.class as class_name, s.section, s.address as home_address,
                   ts.status as trip_status, ts.is_current
            FROM student_transport st
            JOIN students s ON s.id = st.student_id
            LEFT JOIN trip_students ts ON ts.student_id = s.id AND ts.trip_id = ?
            WHERE st.driver_id = ? AND s.deleted_at IS NULL
            ORDER BY st.pickup_time ASC, s.first_name ASC
        ", [$tripId, $driver['id']]);

        $routeData = [
            'id' => (int)$driver['id'],
            'driver' => trim($driver['first_name'] . ' ' . $driver['last_name']),
            'bus' => $driver['bus'],
            'phone' => $driver['phone'],
            'status' => $driver['status'],
            'trip_id' => $tripId,
            'lat' => (float)($driver['lat'] ?? 0),
            'lng' => (float)($driver['lng'] ?? 0),
            'speed' => (float)($driver['speed'] ?? 0),
            'students' => array_map(fn($s) => [
                'id' => (int)$s['id'],
                'first_name' => $s['first_name'],
                'last_name' => $s['last_name'],
                'class_name' => $s['class_name'],
                'section' => $s['section'],
                'address' => $s['address'] ?: $s['home_address'],
                'pickup_lat' => (float)($s['pickup_lat'] ?? 0),
                'pickup_lng' => (float)($s['pickup_lng'] ?? 0),
                'pickup_time' => $s['pickup_time'],
                'status' => $s['trip_status'] ?? 'Waiting',
                'is_current' => (bool)$s['is_current'],
            ], $students)
        ];

        echo json_encode(['success' => true, 'route' => $routeData]);
        exit();
    }

    public function updateLocation(string $id): string
    {
        header('Content-Type: application/json');

        $tenantId = \Core\Database::getTenantId();
        $driver = $this->db()->selectOne("SELECT e.* FROM employees e JOIN designations des ON e.designation_id = des.id WHERE e.id = ? AND e.tenant_id = ? AND des.title = 'Driver'", [(int)$id, $tenantId]);
        if (!$driver) {
            echo json_encode(['success' => false, 'message' => 'Driver not found.']);
            exit();
        }

        $body = json_decode(file_get_contents('php://input'), true);
        $lat  = isset($body['lat']) ? (float)$body['lat'] : null;
        $lng  = isset($body['lng']) ? (float)$body['lng'] : null;
        $speed = isset($body['speed']) ? (float)$body['speed'] : null;

        if ($lat === null || $lng === null || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            echo json_encode(['success' => false, 'message' => 'Invalid coordinates.']);
            exit();
        }

        $updateData = [
            'current_latitude'  => $lat,
            'current_longitude' => $lng,
            'last_updated_at'   => now(),
        ];
        if ($speed !== null) {
            $updateData['current_speed'] = $speed;
        }

        $this->db()->update('employees', $updateData, 'id = ?', [$driver['id']]);

        // Insert into driver_locations history if active trip exists
        $activeTrip = $this->db()->selectOne("SELECT id FROM driver_trips WHERE driver_id = ? AND status = 'active'", [$driver['id']]);
        if ($activeTrip) {
            $this->db()->insert('driver_locations', [
                'trip_id' => $activeTrip['id'],
                'lat' => $lat,
                'lng' => $lng,
                'speed' => $speed ?? 0,
                'created_at' => now()
            ]);
        }

        ActivityLog::log('transport_location_updated', auth_id(), [
            'driver_id' => $driver['id'],
            'lat'      => $lat,
            'lng'      => $lng,
            'speed'    => $speed,
        ]);

        echo json_encode(['success' => true, 'lat' => $lat, 'lng' => $lng, 'speed' => $speed]);
        exit();
    }

    public function create(): string
    {
        $tenantId = \Core\Database::getTenantId();
        file_put_contents(ROOT_PATH . '/session_debug.txt', json_encode([
            'session' => $_SESSION ?? null,
            'tenantId' => $tenantId,
            'schoolId' => \Core\Database::getSchoolId(),
            'branchId' => \Core\Database::getBranchId(),
        ]));
        $drivers = $this->db()->select("
            SELECT u.id, u.name, u.phone, u.email 
            FROM users u
            JOIN user_roles ur ON u.id = ur.user_id
            JOIN roles r ON ur.role_id = r.id
            WHERE u.tenant_id = ? AND r.slug = 'driver' AND u.deleted_at IS NULL
        ", [$tenantId]);
        return $this->view('transport/create', compact('drivers'));
    }

    public function store(): string
    {
        $data = $this->request->getBody();
        unset($data['_csrf']);

        $rules = [
            'route_name'   => 'required|min:3',
            'bus_number'   => 'required',
            'driver_name'  => 'required|min:2',
            'driver_phone' => 'required',
        ];

        $validator = new \Core\Validator($data, $rules);
        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', $data);
            return $this->redirect('/transport/create');
        }

        $data['tenant_id'] = \Core\Database::getTenantId();
        $data['school_id'] = \Core\Database::getSchoolId() ?: 1;
        $data['branch_id'] = \Core\Database::getBranchId() ?: 1;
        $data['status']    = $data['status'] ?? 'inactive';
        $data['current_latitude']  = $data['current_latitude'] ? (float)$data['current_latitude'] : 13.0827; // Chennai fallback default
        $data['current_longitude'] = $data['current_longitude'] ? (float)$data['current_longitude'] : 80.2707;
        $data['last_updated_at']   = now();

        $routeId = TransportRoute::create($data);

        ActivityLog::log('transport_route_created', auth_id(), ['route_id' => $routeId]);
        Session::flash('success', "Transport Route '{$data['route_name']}' created successfully.");
        return $this->redirect('/transport');
    }

    public function edit(string $id): string
    {
        $route = TransportRoute::find((int)$id);
        if (!$route) {
            Session::flash('error', 'Route not found.');
            return $this->redirect('/transport');
        }

        $tenantId = \Core\Database::getTenantId();
        $drivers = $this->db()->select("
            SELECT u.id, u.name, u.phone, u.email 
            FROM users u
            JOIN user_roles ur ON u.id = ur.user_id
            JOIN roles r ON ur.role_id = r.id
            WHERE u.tenant_id = ? AND r.slug = 'driver' AND u.deleted_at IS NULL
        ", [$tenantId]);

        return $this->view('transport/edit', compact('route', 'drivers'));
    }

    public function update(string $id): string
    {
        $route = TransportRoute::find((int)$id);
        if (!$route) {
            Session::flash('error', 'Route not found.');
            return $this->redirect('/transport');
        }

        $data = $this->request->getBody();
        unset($data['_csrf'], $data['_method']);

        $rules = [
            'route_name'   => 'required|min:3',
            'bus_number'   => 'required',
            'driver_name'  => 'required|min:2',
            'driver_phone' => 'required',
        ];

        $validator = new \Core\Validator($data, $rules);
        if ($validator->fails()) {
            if ($this->request->wantsJson()) {
                return $this->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
            }
            Session::flash('errors', $validator->errors());
            return $this->redirect("/transport/{$id}/edit");
        }

        $data['current_latitude']  = $data['current_latitude'] ? (float)$data['current_latitude'] : null;
        $data['current_longitude'] = $data['current_longitude'] ? (float)$data['current_longitude'] : null;
        $data['last_updated_at']   = now();

        TransportRoute::update($route['id'], $data);

        ActivityLog::log('transport_route_updated', auth_id(), ['route_id' => $route['id']]);
        Session::flash('success', 'Route updated successfully.');

        if ($this->request->wantsJson()) {
            return $this->json(['success' => true, 'message' => 'Route updated successfully.']);
        }

        return $this->redirect('/transport');
    }

    public function assignStudent(string $id): string
    {
        $tenantId = \Core\Database::getTenantId();
        $driver = $this->db()->selectOne("SELECT e.*, CONCAT(e.first_name, ' ', e.last_name) as name FROM employees e JOIN designations des ON e.designation_id = des.id WHERE e.id = ? AND e.tenant_id = ? AND des.title = 'Driver'", [(int)$id, $tenantId]);
        if (!$driver) {
            Session::flash('error', 'Driver not found.');
            return $this->redirect('/transport/student-assignments');
        }

        $studentId   = (int)$this->request->post('student_id');
        $pickupPoint = $this->request->post('pickup_point', 'School Gate');
        $pickupTime  = $this->request->post('pickup_time', '08:00');
        $pickupLat   = $this->request->post('pickup_lat');
        $pickupLng   = $this->request->post('pickup_lng');

        if (!$studentId) {
            Session::flash('error', 'Please select a student.');
            return $this->redirect('/transport/student-assignments');
        }

        // Delete any existing route map to avoid unique key crash
        $this->db()->query("DELETE FROM student_transport WHERE student_id = ?", [$studentId]);

        $this->db()->insert('student_transport', [
            'student_id'   => $studentId,
            'driver_id'    => $driver['id'],
            'pickup_point' => $pickupPoint,
            'pickup_time'  => $pickupTime,
            'pickup_lat'   => $pickupLat ? (float)$pickupLat : null,
            'pickup_lng'   => $pickupLng ? (float)$pickupLng : null,
            'created_at'   => now(),
        ]);

        // Timeline log
        $student = $this->db()->selectOne("SELECT first_name, last_name, tenant_id, school_id, branch_id FROM students WHERE id = ?", [$studentId]);
        if ($student) {
            $this->db()->insert('student_timeline', [
                'student_id'  => $studentId,
                'event_type'  => 'transport',
                'title'       => 'Transport Assigned',
                'description' => "Assigned to driver '{$driver['name']}'. Pickup: {$pickupPoint} at {$pickupTime}.",
                'color'       => 'indigo',
                'icon'        => 'truck',
                'actor_name'  => auth()['name'] ?? 'Staff',
                'occurred_at' => now(),
            ]);
        }

        ActivityLog::log('student_assigned_transport', auth_id(), ['student_id' => $studentId, 'driver_id' => $driver['id']]);
        Session::flash('success', 'Student assigned to driver.');
        return $this->redirect('/transport/student-assignments');
    }

    public function updateAssignment(string $id): string
    {
        $tenantId = \Core\Database::getTenantId();
        $assignment = $this->db()->selectOne("SELECT * FROM student_transport WHERE id = ?", [(int)$id]);
        
        if (!$assignment) {
            Session::flash('error', 'Assignment not found.');
            return $this->redirect('/transport/student-assignments');
        }

        $pickupPoint = $this->request->post('pickup_point');
        $pickupTime  = $this->request->post('pickup_time');
        $pickupLat   = $this->request->post('pickup_lat');
        $pickupLng   = $this->request->post('pickup_lng');
        
        $data = [
            'pickup_point' => $pickupPoint,
            'pickup_time'  => $pickupTime,
        ];
        
        if ($pickupLat && $pickupLng) {
            $data['pickup_lat'] = (float)$pickupLat;
            $data['pickup_lng'] = (float)$pickupLng;
        }

        $this->db()->update('student_transport', $data, ['id' => $assignment['id']]);
        
        Session::flash('success', 'Student assignment updated successfully.');
        return $this->redirect('/transport/student-assignments');
    }

    public function removeAssignment(string $id): string
    {
        $tenantId = \Core\Database::getTenantId();
        $assignment = $this->db()->selectOne("SELECT * FROM student_transport WHERE id = ?", [(int)$id]);
        
        if (!$assignment) {
            Session::flash('error', 'Assignment not found.');
            return $this->redirect('/transport/student-assignments');
        }

        $this->db()->query("DELETE FROM student_transport WHERE id = ?", [$assignment['id']]);
        
        ActivityLog::log('student_removed_transport', auth_id(), ['assignment_id' => $id]);
        Session::flash('success', 'Student removed from transport route.');
        return $this->redirect('/transport/student-assignments');
    }

    public function destroy(string $id): string
    {
        $route = TransportRoute::find((int)$id);
        if ($route) {
            // Delete all mappings then the route
            $this->db()->query("DELETE FROM student_transport WHERE route_id = ?", [$route['id']]);
            $this->db()->query("DELETE FROM transport_routes WHERE id = ?", [$route['id']]);
            ActivityLog::log('transport_route_deleted', auth_id(), ['route_id' => $id]);
            Session::flash('success', 'Route deleted.');
        }

        return $this->redirect('/transport');
    }

    public function createDriver(): string
    {
        return $this->view('transport/create_driver');
    }

    public function storeDriver(): string
    {
        $data = $this->request->getBody();
        unset($data['_csrf']);

        $rules = [
            'name'     => 'required|min:2',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required',
            'password' => 'required|min:8',
        ];

        $validator = new \Core\Validator($data, $rules);
        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', $data);
            return $this->redirect('/transport/driver/create');
        }

        $db = $this->db();
        $tenantId = \Core\Database::getTenantId();
        $schoolId = \Core\Database::getSchoolId() ?: 1;
        $branchId = \Core\Database::getBranchId() ?: 1;

        $userId = $db->insert('users', [
            'uuid'              => str_uuid(),
            'tenant_id'         => $tenantId,
            'school_id'         => $schoolId,
            'branch_id'         => $branchId,
            'name'              => $data['name'],
            'email'             => $data['email'],
            'phone'             => $data['phone'],
            'password'          => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            'designation'       => 'Driver',
            'is_active'         => 1,
            'email_verified_at' => now(),
            'created_at'        => now(),
        ]);

        // Assign Driver role (ID 7)
        $driverRole = $db->selectOne("SELECT id FROM roles WHERE slug = 'driver'");
        if ($driverRole) {
            $db->insert('user_roles', [
                'user_id' => $userId,
                'role_id' => $driverRole['id']
            ]);
        }

        // Assign driver_app in user_apps
        $db->insert('user_apps', [
            'user_id'  => $userId,
            'app_name' => 'driver_app'
        ]);

        // Insert into employees table
        $empCodeCount = (int) ($db->selectOne("SELECT COUNT(*) as cnt FROM employees")['cnt'] ?? 0) + 101;
        $empCode = 'EMP-' . str_pad((string)$empCodeCount, 3, '0', STR_PAD_LEFT);
        
        $designationId = $db->selectOne("SELECT id FROM designations WHERE title = 'Driver'")['id'] ?? null;

        $nameParts = explode(' ', $data['name'], 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        $db->insert('employees', [
            'tenant_id'      => $tenantId,
            'user_id'        => $userId,
            'emp_code'       => $empCode,
            'first_name'     => $firstName,
            'last_name'      => $lastName,
            'email'          => $data['email'],
            'phone'          => $data['phone'],
            'designation_id' => $designationId,
            'license_number' => $data['license_number'] ?? 'PENDING',
            'status'         => 'active',
            'created_at'     => now()
        ]);

        ActivityLog::log('user_created', auth_id(), ['user_id' => $userId, 'role' => 'driver']);
        Session::flash('success', "Driver user '{$data['name']}' created successfully.");
        return $this->redirect('/transport/drivers');
    }

    // --- DRIVER APP API ENDPOINTS ---

    public function apiStartTrip(): string
    {
        header('Content-Type: application/json');
        $userId = auth()['id'] ?? 0;
        $driver = $this->db()->selectOne("
            SELECT e.id, e.current_latitude as lat, e.current_longitude as lng 
            FROM employees e JOIN designations des ON e.designation_id = des.id
            WHERE e.user_id = ? AND des.title = 'Driver' LIMIT 1
        ", [$userId]);

        if (!$driver) return json_encode(['success' => false, 'message' => 'Driver not found.']);
        
        $activeTrip = $this->db()->selectOne("SELECT id FROM driver_trips WHERE driver_id = ? AND status = 'active'", [$driver['id']]);
        if (!$activeTrip) {
            $tripId = $this->db()->insert('driver_trips', ['driver_id' => $driver['id'], 'status' => 'active']);
            error_log("Created trip ID: " . $tripId);
            $students = $this->db()->select("SELECT student_id FROM student_transport WHERE driver_id = ?", [$driver['id']]);
            error_log("Found students: " . count($students));
            foreach ($students as $st) {
                try {
                    $this->db()->insert('trip_students', [
                        'trip_id' => $tripId, 'student_id' => $st['student_id'], 'status' => 'Waiting', 'is_current' => 0
                    ]);
                    error_log("Inserted student: " . $st['student_id']);
                } catch (\Exception $e) {
                    error_log("Insert error: " . $e->getMessage());
                }
            }
        } else {
            $tripId = $activeTrip['id'];
            error_log("Reused trip ID: " . $tripId);
        }

        // Set driver route status to en_route
        $this->db()->query("UPDATE employees SET route_status = 'en_route' WHERE id = ?", [$driver['id']]);

        $this->calculateNextStop($tripId, $driver['lat'], $driver['lng']);
        return json_encode(['success' => true, 'trip_id' => $tripId]);
    }

    public function apiUpdateStudentStatus(): string
    {
        header('Content-Type: application/json');
        $studentId = $_POST['student_id'] ?? null;
        $status = $_POST['status'] ?? null; // 'Picked Up', 'Skipped', 'Absent'
        
        if (!$studentId || !$status) return json_encode(['success' => false]);
        
        $userId = auth()['id'] ?? 0;
        $driver = $this->db()->selectOne("SELECT e.id, e.current_latitude as lat, e.current_longitude as lng FROM employees e JOIN designations des ON e.designation_id = des.id WHERE e.user_id = ? AND des.title = 'Driver' LIMIT 1", [$userId]);
        if (!$driver) return json_encode(['success' => false]);

        $activeTrip = $this->db()->selectOne("SELECT id FROM driver_trips WHERE driver_id = ? AND status = 'active'", [$driver['id']]);
        if (!$activeTrip) return json_encode(['success' => false, 'message' => 'No active trip']);

        $tripId = $activeTrip['id'];
        $this->db()->query("UPDATE trip_students SET status = ?, is_current = 0 WHERE trip_id = ? AND student_id = ?", [$status, $tripId, $studentId]);
        
        $this->calculateNextStop($tripId, $driver['lat'], $driver['lng']);
        return json_encode(['success' => true]);
    }

    public function apiCompleteTrip(): string
    {
        header('Content-Type: application/json');
        $userId = auth()['id'] ?? 0;
        $driver = $this->db()->selectOne("SELECT e.id FROM employees e JOIN designations des ON e.designation_id = des.id WHERE e.user_id = ? AND des.title = 'Driver' LIMIT 1", [$userId]);
        if ($driver) {
            $this->db()->query("UPDATE driver_trips SET status = 'completed' WHERE driver_id = ? AND status = 'active'", [$driver['id']]);
            $this->db()->query("UPDATE employees SET route_status = 'completed' WHERE id = ?", [$driver['id']]);
        }
        return json_encode(['success' => true]);
    }

    private function calculateNextStop($tripId, $driverLat, $driverLng)
    {
        $this->db()->query("UPDATE trip_students SET is_current = 0 WHERE trip_id = ?", [$tripId]);
        
        $waiting = $this->db()->select("
            SELECT ts.student_id, st.pickup_lat, st.pickup_lng 
            FROM trip_students ts
            JOIN student_transport st ON ts.student_id = st.student_id
            WHERE ts.trip_id = ? AND ts.status = 'Waiting'
        ", [$tripId]);

        if (empty($waiting) || !$driverLat || !$driverLng) return;

        $nearest = null;
        $minDist = PHP_FLOAT_MAX;

        foreach ($waiting as $w) {
            $dist = $this->haversineDistance($driverLat, $driverLng, $w['pickup_lat'], $w['pickup_lng']);
            if ($dist < $minDist) {
                $minDist = $dist;
                $nearest = $w['student_id'];
            }
        }

        if ($nearest) {
            $this->db()->query("UPDATE trip_students SET is_current = 1 WHERE trip_id = ? AND student_id = ?", [$tripId, $nearest]);
        }
    }

    private function haversineDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; // km
        $latFrom = deg2rad((float)$lat1);
        $lonFrom = deg2rad((float)$lon1);
        $latTo = deg2rad((float)$lat2);
        $lonTo = deg2rad((float)$lon2);
        $dLat = $latTo - $latFrom;
        $dLon = $lonTo - $lonFrom;
        $a = sin($dLat/2) * sin($dLat/2) + cos($latFrom) * cos($latTo) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }

    public function apiUpdateLocation($id): string
    {
        header('Content-Type: application/json');
        $body = $this->request->getBody();
        if ($this->request->isJson()) {
            $data = json_decode(file_get_contents('php://input'), true);
            $lat = $data['lat'] ?? null;
            $lng = $data['lng'] ?? null;
            $speed = $data['speed'] ?? 0;
        } else {
            $lat = $_POST['lat'] ?? null;
            $lng = $_POST['lng'] ?? null;
            $speed = $_POST['speed'] ?? 0;
        }
        
        if ($lat && $lng) {
            $this->db()->query("
                UPDATE employees 
                SET current_latitude = ?, current_longitude = ?, current_speed = ?, last_updated_at = NOW() 
                WHERE id = ?
            ", [$lat, $lng, $speed, $id]);

            // Save location history if there's an active trip
            $activeTrip = $this->db()->selectOne("SELECT id FROM driver_trips WHERE driver_id = ? AND status = 'active'", [$id]);
            if ($activeTrip) {
                $this->db()->insert('driver_locations', [
                    'trip_id' => $activeTrip['id'],
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'speed' => $speed
                ]);
            }
        }
        
        return json_encode(['success' => true]);
    }
}

