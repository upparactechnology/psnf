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

    public function index(): string
    {
        $tenantId = \Core\Database::getTenantId();

        $routes = $this->db()->select("
            SELECT tr.*, COUNT(st.id) as student_count
            FROM transport_routes tr
            LEFT JOIN student_transport st ON st.route_id = tr.id
            WHERE tr.tenant_id = ?
            GROUP BY tr.id
            ORDER BY tr.route_name ASC
        ", [$tenantId]);

        $assignments = $this->db()->select("
            SELECT st.*, s.first_name, s.last_name, s.admission_number, tr.route_name
            FROM student_transport st
            JOIN students s ON s.id = st.student_id
            JOIN transport_routes tr ON tr.id = st.route_id
            WHERE tr.tenant_id = ? AND s.deleted_at IS NULL
            ORDER BY tr.route_name ASC, s.first_name ASC
        ", [$tenantId]);

        $unassignedStudents = $this->db()->select("
            SELECT id, first_name, last_name, admission_number
            FROM students
            WHERE tenant_id = ? AND deleted_at IS NULL AND id NOT IN (
                SELECT student_id FROM student_transport
            )
            ORDER BY first_name ASC
        ", [$tenantId]);

        return $this->view('transport/index', compact('routes', 'assignments', 'unassignedStudents'));
    }

    public function tracking(): string
    {
        $tenantId = \Core\Database::getTenantId();

        // Auto-patch schema if current_speed column is missing
        $columns = $this->db()->select("SHOW COLUMNS FROM `transport_routes` LIKE 'current_speed'");
        if (empty($columns)) {
            $this->db()->query("ALTER TABLE `transport_routes` ADD COLUMN `current_speed` DECIMAL(5,2) NOT NULL DEFAULT 0.00");
        }

        $routes = $this->db()->select("
            SELECT tr.*, COUNT(st.id) as student_count
            FROM transport_routes tr
            LEFT JOIN student_transport st ON st.route_id = tr.id
            WHERE tr.tenant_id = ?
            GROUP BY tr.id
            ORDER BY tr.status DESC, tr.route_name ASC
        ", [$tenantId]);

        return $this->view('transport/tracking', compact('routes'));
    }

    public function liveData(): string
    {
        $tenantId = \Core\Database::getTenantId();

        // Auto-patch schema if current_speed column is missing
        $columns = $this->db()->select("SHOW COLUMNS FROM `transport_routes` LIKE 'current_speed'");
        if (empty($columns)) {
            $this->db()->query("ALTER TABLE `transport_routes` ADD COLUMN `current_speed` DECIMAL(5,2) NOT NULL DEFAULT 0.00");
        }

        $rows = $this->db()->select("
            SELECT tr.id, tr.route_name as name, tr.bus_number as bus, tr.driver_name as driver,
                   tr.driver_phone as phone, tr.status,
                   tr.current_latitude  as lat,
                   tr.current_longitude as lng,
                   tr.current_speed     as speed,
                   tr.last_updated_at   as updated_at,
                   COUNT(st.id) as students
            FROM transport_routes tr
            LEFT JOIN student_transport st ON st.route_id = tr.id
            WHERE tr.tenant_id = ?
            GROUP BY tr.id
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
            'updated_at' => $r['updated_at'],
        ], $rows);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'routes' => $data]);
        exit();
    }

    public function updateLocation(string $id): string
    {
        header('Content-Type: application/json');

        $route = TransportRoute::find((int)$id);
        if (!$route) {
            echo json_encode(['success' => false, 'message' => 'Route not found.']);
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

        TransportRoute::update($route['id'], $updateData);

        ActivityLog::log('transport_location_updated', auth_id(), [
            'route_id' => $route['id'],
            'lat'      => $lat,
            'lng'      => $lng,
            'speed'    => $speed,
        ]);

        echo json_encode(['success' => true, 'lat' => $lat, 'lng' => $lng, 'speed' => $speed]);
        exit();
    }

    public function create(): string
    {
        return $this->view('transport/create');
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

        return $this->view('transport/edit', compact('route'));
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
            Session::flash('errors', $validator->errors());
            return $this->redirect("/transport/{$id}/edit");
        }

        $data['current_latitude']  = $data['current_latitude'] ? (float)$data['current_latitude'] : null;
        $data['current_longitude'] = $data['current_longitude'] ? (float)$data['current_longitude'] : null;
        $data['last_updated_at']   = now();

        TransportRoute::update($route['id'], $data);

        ActivityLog::log('transport_route_updated', auth_id(), ['route_id' => $route['id']]);
        Session::flash('success', 'Route updated successfully.');
        return $this->redirect('/transport');
    }

    public function assignStudent(string $id): string
    {
        $route = TransportRoute::find((int)$id);
        if (!$route) {
            Session::flash('error', 'Route not found.');
            return $this->redirect('/transport');
        }

        $studentId   = (int)$this->request->post('student_id');
        $pickupPoint = $this->request->post('pickup_point', 'School Gate');
        $pickupTime  = $this->request->post('pickup_time', '08:00');

        if (!$studentId) {
            Session::flash('error', 'Please select a student.');
            return $this->redirect('/transport');
        }

        // Delete any existing route map to avoid unique key crash
        $this->db()->query("DELETE FROM student_transport WHERE student_id = ?", [$studentId]);

        $this->db()->insert('student_transport', [
            'student_id'   => $studentId,
            'route_id'     => $route['id'],
            'pickup_point' => $pickupPoint,
            'pickup_time'  => $pickupTime,
            'created_at'   => now(),
        ]);

        // Timeline log
        $student = $this->db()->selectOne("SELECT first_name, last_name, tenant_id, school_id, branch_id FROM students WHERE id = ?", [$studentId]);
        if ($student) {
            $this->db()->insert('student_timeline', [
                'student_id'  => $studentId,
                'tenant_id'   => $student['tenant_id'],
                'school_id'   => $student['school_id'],
                'branch_id'   => $student['branch_id'],
                'event_type'  => 'transport',
                'title'       => 'Transport Assigned',
                'description' => "Assigned to route '{$route['route_name']}' ({$route['bus_number']}). Pickup: {$pickupPoint} at {$pickupTime}.",
                'color'       => 'indigo',
                'icon'        => 'truck',
                'actor_name'  => auth()['name'] ?? 'Staff',
                'occurred_at' => now(),
            ]);
        }

        ActivityLog::log('student_assigned_transport', auth_id(), ['student_id' => $studentId, 'route_id' => $route['id']]);
        Session::flash('success', 'Student assigned to route.');
        return $this->redirect('/transport');
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
}
