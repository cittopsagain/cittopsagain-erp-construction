<?php

require_once 'www/config.php';

$dsn = "mysql:host=127.0.0.1;port=3308;dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

    // Create table
    $sql = "CREATE TABLE IF NOT EXISTS hr_employees (
        id INT AUTO_INCREMENT PRIMARY KEY,
        employee_no VARCHAR(50) NOT NULL UNIQUE,
        employee_name VARCHAR(150) NOT NULL,
        position_id INT,
        department_id INT,
        branch_id INT,
        employment_type_id INT,
        work_schedule_id INT,
        date_hired DATE,
        supervisor_id INT,
        status ENUM('Active', 'Inactive') DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        created_by INT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);
    echo "Table 'hr_employees' created or already exists.\n";

    // Helper to get IDs
    $getDeptId = function ($name) use ($pdo) {
        $stmt = $pdo->prepare("SELECT id FROM hr_departments WHERE name = ?");
        $stmt->execute([$name]);
        return $stmt->fetchColumn();
    };

    $getPosId = function ($name) use ($pdo) {
        $stmt = $pdo->prepare("SELECT pos_id FROM hr_position_types WHERE pos_name = ?");
        $stmt->execute([$name]);
        return $stmt->fetchColumn();
    };

    $getBranchId = function ($name) use ($pdo) {
        $stmt = $pdo->prepare("SELECT id FROM hr_branches WHERE branch_name = ?");
        $stmt->execute([$name]);
        return $stmt->fetchColumn();
    };

    $getEmpTypeId = function ($name) use ($pdo) {
        $stmt = $pdo->prepare("SELECT id FROM hr_employment_types WHERE employment_type = ?");
        $stmt->execute([$name]);
        return $stmt->fetchColumn();
    };

    $getSchedId = function ($name) use ($pdo) {
        $stmt = $pdo->prepare("SELECT id FROM hr_work_schedules WHERE schedule_name LIKE ?");
        $stmt->execute([$name . '%']);
        return $stmt->fetchColumn();
    };

    // Sample Data
    $sampleData = [
        ['EMP-2026-0001', 'Juan Dela Cruz', 'HR Manager', 'Human Resources', 'Cebu Head Office', 'Regular', 'Regular Office', '2019-05-15', null],
        ['EMP-2026-0002', 'Maria Santos', 'HR Officer', 'Human Resources', 'Cebu Head Office', 'Regular', 'Regular Office', '2021-07-12', 'Juan Dela Cruz'],
        ['EMP-2026-0003', 'Peter Ramos', 'Payroll Specialist', 'Finance', 'Cebu Head Office', 'Regular', 'Regular Office', '2020-01-08', null],
        ['EMP-2026-0004', 'Carlo Reyes', 'Project Manager', 'Engineering', 'Cebu Head Office', 'Regular', 'Construction Site', '2018-03-20', null],
        ['EMP-2026-0005', 'Michael Garcia', 'Project Engineer', 'Engineering', 'Cebu Head Office', 'Regular', 'Construction Site', '2022-08-15', 'Carlo Reyes'],
        ['EMP-2026-0006', 'Mark Villanueva', 'Site Engineer', 'Engineering', 'Cebu Head Office', 'Probationary', 'Construction Site', '2026-03-10', 'Michael Garcia'],
        ['EMP-2026-0007', 'Allan Fernandez', 'Electrician', 'Operations', 'Cebu Head Office', 'Project-Based', 'Construction Site', '2026-01-15', null],
        ['EMP-2026-0008', 'Joseph Lim', 'Electrician', 'Operations', 'Cebu Head Office', 'Project-Based', 'Construction Site', '2025-11-20', null],
        ['EMP-2026-0009', 'Ryan Castillo', 'CCTV Technician', 'Operations', 'Cebu Head Office', 'Regular', 'Construction Site', '2024-04-18', null],
        ['EMP-2026-0010', 'Kevin Torres', 'FDAS Technician', 'Operations', 'Cebu Head Office', 'Regular', 'Construction Site', '2023-06-02', null],
        ['EMP-2026-0011', 'Anthony Lopez', 'Warehouse Supervisor', 'Warehouse', 'Cebu Warehouse', 'Regular', 'Warehouse Shift', '2019-09-01', null],
        ['EMP-2026-0012', 'Daniel Cruz', 'Warehouse Staff', 'Warehouse', 'Cebu Warehouse', 'Probationary', 'Warehouse Shift', '2026-04-21', 'Anthony Lopez'],
        ['EMP-2026-0013', 'Grace Mendoza', 'Purchasing Officer', 'Purchasing', 'Cebu Head Office', 'Regular', 'Regular Office', '2021-02-11', null],
        ['EMP-2026-0014', 'Karen Flores', 'Accountant', 'Finance', 'Cebu Head Office', 'Regular', 'Regular Office', '2020-10-14', null],
        ['EMP-2026-0015', 'Nicole Perez', 'Administrative Assistant', 'Administration', 'Cebu Head Office', 'Regular', 'Regular Office', '2024-01-16', null],
        ['EMP-2026-0016', 'John Espinosa', 'Safety Officer', 'Safety', 'Cebu Head Office', 'Project-Based', 'Construction Site', '2025-05-12', 'Carlo Reyes'],
        ['EMP-2026-0017', 'Patrick Gomez', 'Driver', 'Logistics', 'Cebu Head Office', 'Regular', 'Logistics Shift', '2022-09-01', null],
        ['EMP-2026-0018', 'Charles Tan', 'IT Support Specialist', 'Information Technology', 'Cebu Head Office', 'Regular', 'Regular Office', '2023-11-06', null],
        ['EMP-2026-0019', 'Jennifer Ong', 'Receptionist', 'Administration', 'Cebu Head Office', 'Probationary', 'Regular Office', '2026-05-05', null],
        ['EMP-2026-0020', 'Sophia Rivera', 'Office Clerk', 'Administration', 'Cebu Head Office', 'Intern', 'Regular Office', '2026-06-01', 'Nicole Perez']
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO hr_employees (employee_no, employee_name, position_id, department_id, branch_id, employment_type_id, work_schedule_id, date_hired, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");

    foreach ($sampleData as $row) {
        $posId = $getPosId($row[2]);
        // If position doesn't exist, create a placeholder
        if (!$posId) {
            $deptId = $getDeptId($row[3]);
            if (!$deptId) {
                $insDept = $pdo->prepare("INSERT INTO hr_departments (name) VALUES (?)");
                $insDept->execute([$row[3]]);
                $deptId = $pdo->lastInsertId();
            }
            $insPos = $pdo->prepare("INSERT INTO hr_position_types (pos_code, pos_name, dept_id) VALUES (?, ?, ?)");
            $insPos->execute([strtoupper(substr($row[2], 0, 4)) . rand(100, 999), $row[2], $deptId]);
            $posId = $pdo->lastInsertId();
        }

        $deptId = $getDeptId($row[3]);
        if (!$deptId) {
            $insDept = $pdo->prepare("INSERT INTO hr_departments (name) VALUES (?)");
            $insDept->execute([$row[3]]);
            $deptId = $pdo->lastInsertId();
        }

        $branchId = $getBranchId($row[4]);
        if (!$branchId) {
            $insBranch = $pdo->prepare("INSERT INTO hr_branches (branch_code, branch_name) VALUES (?, ?)");
            $insBranch->execute([strtoupper(substr($row[4], 0, 4)) . rand(100, 999), $row[4]]);
            $branchId = $pdo->lastInsertId();
        }

        $empTypeId = $getEmpTypeId($row[5]);
        $schedId = $getSchedId($row[6]);

        $stmt->execute([
            $row[0],
            $row[1],
            $posId,
            $deptId,
            $branchId,
            $empTypeId,
            $schedId,
            $row[7]
        ]);
    }

    // Update supervisors
    foreach ($sampleData as $row) {
        if ($row[8]) {
            $pdo->prepare("UPDATE hr_employees SET supervisor_id = (SELECT id FROM (SELECT id FROM hr_employees WHERE employee_name = ?) AS t) WHERE employee_name = ?")
                ->execute([$row[8], $row[1]]);
        }
    }

    echo "Sample data inserted successfully.\n";

} catch (\PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
    exit(1);
}
