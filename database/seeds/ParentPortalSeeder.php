<?php

declare(strict_types=1);

class ParentPortalSeeder
{
    public function __construct(private \Core\Database $db) {}

    public function run(): void
    {
        // 1. Fetch default Tenant, School, and Branch IDs
        $tenant = $this->db->selectOne("SELECT id FROM tenants WHERE slug = 'psnf'");
        if (!$tenant) return;
        $tid = (int) $tenant['id'];

        $school = $this->db->selectOne("SELECT id FROM schools WHERE tenant_id = ? LIMIT 1", [$tid]);
        if (!$school) return;
        $sid = (int) $school['id'];

        $branch = $this->db->selectOne("SELECT id FROM branches WHERE school_id = ? LIMIT 1", [$sid]);
        if (!$branch) return;
        $bid = (int) $branch['id'];

        // 2. Create Parent User if not exists
        $parentEmail = 'parent@psnf.edu';
        $existsParent = $this->db->selectOne("SELECT id FROM users WHERE email = ?", [$parentEmail]);
        if (!$existsParent) {
            $puid = $this->db->insert('users', [
                'uuid'              => str_uuid(),
                'tenant_id'         => $tid,
                'school_id'         => $sid,
                'branch_id'         => $bid,
                'name'              => 'Rajesh Kumar',
                'email'             => $parentEmail,
                'password'          => password_hash('Parent@1234', PASSWORD_BCRYPT, ['cost' => 12]),
                'phone'             => '+91-9876543210',
                'designation'       => 'Guardian',
                'is_active'         => 1,
                'is_email_verified' => 1,
                'email_verified_at' => now(),
                'created_at'        => now()
            ]);

            // Assign Parent role
            $parentRole = $this->db->selectOne("SELECT id FROM roles WHERE slug = 'parent'");
            if ($parentRole) {
                $this->db->insert('user_roles', [
                    'user_id' => $puid,
                    'role_id' => $parentRole['id']
                ]);
            }
        } else {
            $puid = (int) $existsParent['id'];
        }

        // 3. Create Guardian profile
        $existsGuardian = $this->db->selectOne("SELECT id FROM guardians WHERE user_id = ?", [$puid]);
        if (!$existsGuardian) {
            $gid = $this->db->insert('guardians', [
                'tenant_id'    => $tid,
                'uuid'         => str_uuid(),
                'name'         => 'Rajesh Kumar',
                'relationship' => 'Father',
                'gender'       => 'male',
                'phone'        => '+91-9876543210',
                'email'        => $parentEmail,
                'occupation'   => 'Software Engineer',
                'address'      => 'Apt 405, Pearl Heights, Mumbai, India',
                'id_type'      => 'Aadhar',
                'id_number'    => '1234-5678-9012',
                'user_id'      => $puid,
                'created_at'   => now()
            ]);
        } else {
            $gid = (int) $existsGuardian['id'];
        }

        // 4. Create Students (Children) if not exists
        $students = [
            [
                'first_name'            => 'Aarav',
                'last_name'             => 'Kumar',
                'gender'                => 'male',
                'dob'                   => '2018-05-15',
                'blood_group'           => 'O+',
                'disability_type'       => 'ASD',
                'disability_detail'     => 'Autism Spectrum Disorder. Learns well visually. Sensitive to loud noises.',
                'care_instructions'     => 'Use quiet zones if overwhelmed. Visual prompts for transitions.',
                'special_needs_summary' => 'Speech therapy and behavior modification plan active.',
                'admission_status'      => 'enrolled',
                'class'                 => 'Class A',
                'section'               => 'S1'
            ],
            [
                'first_name'            => 'Diya',
                'last_name'             => 'Kumar',
                'gender'                => 'female',
                'dob'                   => '2020-09-20',
                'blood_group'           => 'B+',
                'disability_type'       => 'Down Syndrome',
                'disability_detail'     => 'Down Syndrome. Hypotonia. Highly social and responsive to music therapy.',
                'care_instructions'     => 'Support during motor activities. Encourage speech repetitions.',
                'special_needs_summary' => 'Physical therapy and speech therapy goals established.',
                'admission_status'      => 'enrolled',
                'class'                 => 'Class B',
                'section'               => 'S2'
            ]
        ];

        $studentIds = [];
        foreach ($students as $stu) {
            $existsStu = $this->db->selectOne("SELECT id FROM students WHERE tenant_id = ? AND first_name = ? AND last_name = ?", [$tid, $stu['first_name'], $stu['last_name']]);
            if (!$existsStu) {
                $uuid = str_uuid();
                $sId = $this->db->insert('students', [
                    'tenant_id'             => $tid,
                    'school_id'             => $sid,
                    'branch_id'             => $bid,
                    'uuid'                  => $uuid,
                    'admission_number'      => admission_number($sid),
                    'gr_number'             => gr_number($sid),
                    'first_name'            => $stu['first_name'],
                    'middle_name'           => 'Rajesh',
                    'last_name'             => $stu['last_name'],
                    'gender'                => $stu['gender'],
                    'dob'                   => $stu['dob'],
                    'blood_group'           => $stu['blood_group'],
                    'disability_type'       => $stu['disability_type'],
                    'disability_detail'     => $stu['disability_detail'],
                    'care_instructions'     => $stu['care_instructions'],
                    'special_needs_summary' => $stu['special_needs_summary'],
                    'admission_status'      => $stu['admission_status'],
                    'class'                 => $stu['class'],
                    'section'               => $stu['section'],
                    'created_at'            => now()
                ]);

                // Create student medical profile
                $this->db->insert('student_medical', [
                    'student_id'          => $sId,
                    'tenant_id'           => $tid,
                    'school_id'           => $sid,
                    'branch_id'           => $bid,
                    'allergies'           => $stu['disability_type'] === 'ASD' ? 'Gluten, Lactose' : 'Dust, Pollen',
                    'triggers'            => $stu['disability_type'] === 'ASD' ? 'Loud whistles, bright strobe lights' : 'Sudden changes in temperature',
                    'current_medications' => 'None',
                    'doctor_name'         => 'Dr. Anjali Mehta',
                    'doctor_phone'        => '+91-9892011223',
                    'hospital'            => 'Children Specialty Hospital',
                    'care_instructions'   => $stu['care_instructions'],
                    'emergency_protocols' => 'Contact father immediately and relocate to quiet room.'
                ]);

                // Link to Guardian
                $this->db->insert('guardian_student', [
                    'guardian_id'  => $gid,
                    'student_id'   => $sId,
                    'is_primary'   => 1,
                    'can_pickup'   => 1,
                    'is_emergency' => 1
                ]);

                $studentIds[] = $sId;
            } else {
                $studentIds[] = (int) $existsStu['id'];
            }
        }

        // 5. Seed Attendance (last 30 days except Sundays)
        foreach ($studentIds as $idx => $sId) {
            // Check if attendance already seeded
            $existsAttendance = $this->db->selectOne("SELECT 1 FROM attendance WHERE student_id = ? LIMIT 1", [$sId]);
            if ($existsAttendance) continue;

            $startDate = new DateTime('now');
            $startDate->modify('-30 days');

            for ($i = 0; $i < 30; $i++) {
                $dateStr = $startDate->format('Y-m-d');
                $dayOfWeek = $startDate->format('w'); // 0 = Sunday

                if ($dayOfWeek !== '0') {
                    // 90% chance present, 5% absent, 5% late
                    $rand = mt_rand(1, 100);
                    $status = 'present';
                    $remarks = 'Attended class';

                    if ($rand > 95) {
                        $status = 'absent';
                        $remarks = 'Sick leave - notified by parent';
                    } elseif ($rand > 90) {
                        $status = 'late';
                        $remarks = 'Late by 15 mins due to traffic';
                    }

                    $this->db->insert('attendance', [
                        'tenant_id'  => $tid,
                        'school_id'  => $sid,
                        'branch_id'  => $bid,
                        'student_id' => $sId,
                        'date'       => $dateStr,
                        'status'     => $status,
                        'remarks'    => $remarks
                    ]);
                }
                $startDate->modify('+1 day');
            }
        }

        // 6. Seed Timetables for Class A (Aarav) and Class B (Diya)
        $classes = ['Class A', 'Class B'];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $subjects = [
            'Class A' => ['Speech Therapy', 'Sensory Integration', 'Visual Arts', 'Math Foundations', 'Life Skills'],
            'Class B' => ['Occupational Therapy', 'Music Therapy', 'Social Communication', 'Basic Literacy', 'Motor Coordination']
        ];

        foreach ($classes as $cls) {
            $existsTimetable = $this->db->selectOne("SELECT 1 FROM timetables WHERE class = ? LIMIT 1", [$cls]);
            if ($existsTimetable) continue;

            foreach ($days as $day) {
                // Seed 4 periods per day
                $startTimes = ['09:00:00', '10:30:00', '12:00:00', '13:30:00'];
                $endTimes   = ['10:15:00', '11:45:00', '13:15:00', '14:45:00'];
                $teachers   = ['Ms. Sarah D\'Souza', 'Mrs. Priya Nair', 'Mr. Amit Sharma', 'Dr. Kiran Patel'];

                for ($period = 0; $period < 4; $period++) {
                    $subjIdx = ($day === 'Monday' ? $period : ($period + mt_rand(1, 4)) % 5);
                    $subject = $subjects[$cls][$subjIdx];

                    $this->db->insert('timetables', [
                        'tenant_id'    => $tid,
                        'school_id'    => $sid,
                        'branch_id'    => $bid,
                        'class'        => $cls,
                        'section'      => $cls === 'Class A' ? 'S1' : 'S2',
                        'day_of_week'  => $day,
                        'subject'      => $subject,
                        'teacher_name' => $teachers[$period],
                        'room'         => 'Room ' . ($cls === 'Class A' ? '101' : '102'),
                        'start_time'   => $startTimes[$period],
                        'end_time'     => $endTimes[$period]
                    ]);
                }
            }
        }

        // 7. Seed Homeworks
        foreach ($classes as $cls) {
            $existsHomework = $this->db->selectOne("SELECT 1 FROM homeworks WHERE class = ? LIMIT 1", [$cls]);
            if ($existsHomework) continue;

            $hws = [
                [
                    'subject'     => $cls === 'Class A' ? 'Speech Therapy' : 'Music Therapy',
                    'title'       => 'Daily Phonics Practice' . ($cls === 'Class A' ? ' (Visual Cards)' : ' (Rhythm Clapping)'),
                    'description' => 'Spend 10 minutes practicing the phonics sheets attached. Record a video of the exercise.',
                    'due_days'    => 2
                ],
                [
                    'subject'     => $cls === 'Class A' ? 'Life Skills' : 'Basic Literacy',
                    'title'       => 'Color Matching & Vocabulary',
                    'description' => 'Match the visual items to their colors using the worksheet. Practice naming each item out loud.',
                    'due_days'    => 5
                ]
            ];

            foreach ($hws as $hw) {
                $dueDate = new DateTime('now');
                $dueDate->modify('+' . $hw['due_days'] . ' days');

                $this->db->insert('homeworks', [
                    'tenant_id'   => $tid,
                    'school_id'   => $sid,
                    'branch_id'   => $bid,
                    'class'       => $cls,
                    'section'     => $cls === 'Class A' ? 'S1' : 'S2',
                    'subject'     => $hw['subject'],
                    'title'       => $hw['title'],
                    'description' => $hw['description'],
                    'file_path'   => '/uploads/homework/worksheet.pdf',
                    'due_date'    => $dueDate->format('Y-m-d'),
                    'assigned_at' => date('Y-m-d')
                ]);
            }
        }

        // 8. Seed Exam Results
        foreach ($studentIds as $idx => $sId) {
            $existsExams = $this->db->selectOne("SELECT 1 FROM exam_results WHERE student_id = ? LIMIT 1", [$sId]);
            if ($existsExams) continue;

            $studentClass = ($idx === 0) ? 'Class A' : 'Class B';
            $examSubjects = $subjects[$studentClass];

            foreach ($examSubjects as $sub) {
                $maxMarks = 50.00;
                $marksObtained = (float) mt_rand(35, 48);
                $grade = $marksObtained >= 45 ? 'A+' : ($marksObtained >= 40 ? 'A' : 'B');

                $this->db->insert('exam_results', [
                    'tenant_id'      => $tid,
                    'school_id'      => $sid,
                    'branch_id'      => $bid,
                    'student_id'     => $sId,
                    'exam_name'      => 'First Term Evaluation',
                    'subject'        => $sub,
                    'marks_obtained' => $marksObtained,
                    'max_marks'      => $maxMarks,
                    'grade'          => $grade,
                    'remarks'        => 'Shows positive focus and excellent participation.',
                    'date_published' => date('Y-m-d', strtotime('-5 days'))
                ]);
            }
        }

        // 9. Seed Announcements
        $existsAnn = $this->db->selectOne("SELECT 1 FROM announcements WHERE tenant_id = ? LIMIT 1", [$tid]);
        if (!$existsAnn) {
            $anns = [
                [
                    'title'           => 'Annual Sensory Integration Workshop',
                    'content'         => 'Dear Parents, we are hosting a workshop on Sensory Integration Strategies at home. Speakers include Dr. Rajesh Verma (Lead Therapist). Join us this Saturday at 10 AM in the auditorium.',
                    'target_audience' => 'parents'
                ],
                [
                    'title'           => 'School Reopening & Safety Protocols Update',
                    'content'         => 'Please note that new thermal checks and drop-off protocols are active starting Monday. Kindly review the safety handbook shared in the documents tab.',
                    'target_audience' => 'all'
                ],
                [
                    'title'           => 'Special Olympics Registration Open',
                    'content'         => 'Registration is open for the upcoming PSNF Special Olympics events. Sports include Bocce, Athletics, and Unified Soccer. Contact the sports coordinator for signup details.',
                    'target_audience' => 'parents'
                ]
            ];

            foreach ($anns as $ann) {
                $this->db->insert('announcements', [
                    'tenant_id'       => $tid,
                    'school_id'       => $sid,
                    'branch_id'       => $bid,
                    'title'           => $ann['title'],
                    'content'         => $ann['content'],
                    'target_audience' => $ann['target_audience'],
                    'published_at'    => now()
                ]);
            }
        }

        // 10. Seed Certificates
        foreach ($studentIds as $idx => $sId) {
            $existsCert = $this->db->selectOne("SELECT 1 FROM certificates WHERE student_id = ? LIMIT 1", [$sId]);
            if ($existsCert) continue;

            $this->db->insert('certificates', [
                'tenant_id'        => $tid,
                'school_id'        => $sid,
                'branch_id'        => $bid,
                'student_id'       => $sId,
                'title'            => 'Outstanding Progress in Sensory Integration',
                'certificate_type' => 'academic',
                'file_path'        => '/uploads/certificates/sensory_cert.pdf',
                'issued_at'        => date('Y-m-d', strtotime('-10 days'))
            ]);
            $this->db->insert('certificates', [
                'tenant_id'        => $tid,
                'school_id'        => $sid,
                'branch_id'        => $bid,
                'student_id'       => $sId,
                'title'            => 'Active Participation Certificate — Special Sports Meet',
                'certificate_type' => 'sports',
                'file_path'        => '/uploads/certificates/sports_cert.pdf',
                'issued_at'        => date('Y-m-d', strtotime('-15 days'))
            ]);
        }

        // 11. Seed Fee Invoices & Payments
        foreach ($studentIds as $idx => $sId) {
            $existsInv = $this->db->selectOne("SELECT 1 FROM fee_invoices WHERE student_id = ? LIMIT 1", [$sId]);
            if ($existsInv) continue;

            // Invoice 1: Paid Term Tuition Fee
            $invNum1 = 'INV-' . date('Y') . '-' . $sId . '-01';
            $invId1 = $this->db->insert('fee_invoices', [
                'tenant_id'      => $tid,
                'school_id'      => $sid,
                'branch_id'      => $bid,
                'student_id'     => $sId,
                'invoice_number' => $invNum1,
                'title'          => 'Term 1 Tuition & Therapy Fee',
                'description'    => 'Tuition fees, speech therapy services, and resource room access charges.',
                'amount'         => 15000.00,
                'due_date'       => date('Y-m-d', strtotime('-10 days')),
                'status'         => 'paid',
                'paid_amount'    => 15000.00,
                'paid_at'        => date('Y-m-d H:i:s', strtotime('-12 days'))
            ]);

            // Seed fee payment
            $this->db->insert('fee_payments', [
                'tenant_id'      => $tid,
                'school_id'      => $sid,
                'branch_id'      => $bid,
                'invoice_id'     => $invId1,
                'amount'         => 15000.00,
                'payment_method' => 'Online',
                'payment_ref'    => 'PAY-REF-TXN88910'
            ]);

            // Invoice 2: Unpaid Transport Fee
            $invNum2 = 'INV-' . date('Y') . '-' . $sId . '-02';
            $this->db->insert('fee_invoices', [
                'tenant_id'      => $tid,
                'school_id'      => $sid,
                'branch_id'      => $bid,
                'student_id'     => $sId,
                'invoice_number' => $invNum2,
                'title'          => 'Monthly Transport & Bus Fee',
                'description'    => 'Bus transport pick-and-drop service charges for this month.',
                'amount'         => 2500.00,
                'due_date'       => date('Y-m-d', strtotime('+15 days')),
                'status'         => 'unpaid',
                'paid_amount'    => 0.00
            ]);
        }

        // 12. Seed Transport Routes
        $existsRoute = $this->db->selectOne("SELECT id FROM transport_routes WHERE tenant_id = ? LIMIT 1", [$tid]);
        if (!$existsRoute) {
            $routeId = $this->db->insert('transport_routes', [
                'tenant_id'         => $tid,
                'school_id'         => $sid,
                'branch_id'         => $bid,
                'route_name'        => 'Mumbai East Route - Route 5',
                'bus_number'        => 'MH-12-AB-5678',
                'driver_name'       => 'Rajendra Singh',
                'driver_phone'      => '+91-9123456789',
                'current_latitude'  => 19.076090,
                'current_longitude' => 72.877426,
                'status'            => 'en_route',
                'last_updated_at'   => now()
            ]);
        } else {
            $routeId = (int) $existsRoute['id'];
        }

        // Assign students to transport route
        foreach ($studentIds as $sId) {
            $existsMap = $this->db->selectOne("SELECT 1 FROM student_transport WHERE student_id = ?", [$sId]);
            if (!$existsMap) {
                $this->db->insert('student_transport', [
                    'student_id'   => $sId,
                    'route_id'     => $routeId,
                    'pickup_point' => 'Society Main Gate, Pearl Heights',
                    'pickup_time'  => '08:15:00'
                ]);
            }
        }

        // 13. Seed Communication Messages (between Rajesh Kumar and Admin/Teacher)
        // We will assume teacher is user with email 'admin@psnf.edu' (acting as admin/teacher here)
        $adminUser = $this->db->selectOne("SELECT id FROM users WHERE email = 'admin@psnf.edu'");
        if ($adminUser) {
            $adminUid = (int) $adminUser['id'];

            $existsMsg = $this->db->selectOne("SELECT 1 FROM communication_messages WHERE sender_id = ? OR receiver_id = ? LIMIT 1", [$puid, $puid]);
            if (!$existsMsg) {
                $messages = [
                    [
                        'sender'   => $puid,
                        'receiver' => $adminUid,
                        'msg'      => 'Hello Ms. Sarah, Aarav seemed a bit sensitive to noise this morning. I have given him his noise-canceling headphones. Please guide the class helper to assist him if it gets noisy.',
                        'offset'   => '-2 hours'
                    ],
                    [
                        'sender'   => $adminUid,
                        'receiver' => $puid,
                        'msg'      => 'Hello Mr. Rajesh, thank you for letting us know! We have briefed Mrs. Nair (our class helper) to keep an eye on Aarav and make sure he has his headphones on during group activities.',
                        'offset'   => '-1 hour'
                    ],
                    [
                        'sender'   => $puid,
                        'receiver' => $adminUid,
                        'msg'      => 'Excellent! Thanks for the quick update. Have a great day.',
                        'offset'   => '-30 mins'
                    ]
                ];

                foreach ($messages as $m) {
                    $this->db->insert('communication_messages', [
                        'tenant_id'   => $tid,
                        'school_id'   => $sid,
                        'branch_id'   => $bid,
                        'sender_id'   => $m['sender'],
                        'receiver_id' => $m['receiver'],
                        'subject'     => 'Aarav Sensory Update',
                        'message'     => $m['msg'],
                        'is_read'     => $m['sender'] === $adminUid ? 0 : 1,
                        'created_at'  => date('Y-m-d H:i:s', strtotime($m['offset']))
                    ]);
                }
            }
        }
    }
}
