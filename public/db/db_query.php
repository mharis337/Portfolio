<?php

function removePatient($conn, $patientId) {
    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare("SELECT wait_time FROM queue WHERE id = :id");
        $stmt->bindParam(':id', $patientId);
        $stmt->execute();
        $removedPatient = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$removedPatient) {
            throw new Exception("Patient not found");
        }

        $removedWaitTime = $removedPatient['wait_time'];

        $stmt = $conn->prepare("DELETE FROM queue WHERE id = :id");
        $stmt->bindParam(':id', $patientId);
        $stmt->execute();

        $stmt = $conn->prepare("SELECT id, wait_time FROM queue WHERE wait_time > 0");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $updatedWaitTimes = [];
        foreach ($rows as $row) {
            $newWaitTime = $row['wait_time'] - $removedWaitTime;
            if ($newWaitTime < 0) $newWaitTime = 0; // Ensure wait time doesn't go below 0
            $stmtUpdate = $conn->prepare("UPDATE queue SET wait_time = :wait_time WHERE id = :id");
            $stmtUpdate->bindParam(':wait_time', $newWaitTime);
            $stmtUpdate->bindParam(':id', $row['id']);
            $stmtUpdate->execute();
            $updatedWaitTimes[$row['id']] = $newWaitTime;
        }

        $conn->commit();

        return ['success' => true, 'updatedWaitTimes' => $updatedWaitTimes];
    } catch (Exception $e) {
        $conn->rollBack();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function admitPatient($conn, $patientId) {
    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare("SELECT wait_time FROM queue WHERE id = :id");
        $stmt->bindParam(':id', $patientId);
        $stmt->execute();
        $admittedPatient = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admittedPatient) {
            throw new Exception("Patient not found");
        }

        $admittedWaitTime = $admittedPatient['wait_time'];

        $stmt = $conn->prepare("UPDATE queue SET wait_time = 0 WHERE id = :id");
        $stmt->bindParam(':id', $patientId);
        $stmt->execute();

        $stmt = $conn->prepare("SELECT id, wait_time FROM queue WHERE id != :id AND wait_time > 0");
        $stmt->bindParam(':id', $patientId);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $updatedWaitTimes = [];
        foreach ($rows as $row) {
            $newWaitTime = $row['wait_time'] - $admittedWaitTime;
            $stmtUpdate = $conn->prepare("UPDATE queue SET wait_time = :wait_time WHERE id = :id");
            $stmtUpdate->bindParam(':wait_time', $newWaitTime);
            $stmtUpdate->bindParam(':id', $row['id']);
            $stmtUpdate->execute();
            $updatedWaitTimes[$row['id']] = $newWaitTime;
        }

        $conn->commit();

        return ['success' => true, 'updatedWaitTimes' => $updatedWaitTimes];
    } catch (Exception $e) {
        $conn->rollBack();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}


function fetchQueueData($conn)
{
    if (!$conn) {
        return ['error' => 'Database connection failed'];
    }

    $sql = "
        SELECT 
            COUNT(*) AS totalPatients,
            COUNT(CASE WHEN wait_time > 0 THEN 1 END) AS waiting,
            COUNT(CASE WHEN wait_time = 0 THEN 1 END) AS inTreatment,
            SUM(wait_time) AS estimatedWait
        FROM queue
    ";

    $stmt = $conn->query($sql);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($stmt->rowCount() > 0) {
        $data = $result[0];
        return [
            'totalPatients' => $data['totalpatients'],
            'inTreatment' => $data['intreatment'],
            'waiting' => $data['waiting'],
            'estimatedWait' => round($data['estimatedwait'] / 60, 2)
        ];
    } else {
        return ['error' => 'No data found'];
    }
}

function addToQueue($conn, $input)
{
    if (!$conn) {
        return ['error' => 'Database connection failed'];
    }

    $weight_severity = 1;
    $weight_wait_time = 0.1;

    try {
        if (isset($input['name']) && isset($input['injury']) && isset($input['severity'])) {
            $name = $input['name'];
            $injury = $input['injury'];
            $severity = intval($input['severity']);
            $currentTime = time();

            // Calculate base wait time based on severity
            $baseWaitTime = max(10, 60 - ($severity * 5));

            $conn->beginTransaction();

            $stmt = $conn->prepare('SELECT id, wait_time, place_in_line, severity, EXTRACT(EPOCH FROM NOW() - created_at) AS elapsed_time FROM queue ORDER BY place_in_line');
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate priority score for the new entry
            $newEntryPriority = $severity * $weight_severity;

            $placeInLine = 1;
            $totalWaitTimeAhead = 0;
            $inserted = false;
            $newEntryId = null;

            foreach ($rows as $index => $row) {
                // Calculate the priority score for the current entry
                $elapsedTime = $row['elapsed_time'];
                $currentPriority = $row['severity'] * $weight_severity + $elapsedTime * $weight_wait_time;

                if (!$inserted && $currentPriority <= $newEntryPriority) {
                    $stmtInsert = $conn->prepare('INSERT INTO queue (name, injury, severity, code, wait_time, place_in_line) VALUES (:name, :injury, :severity, :code, :wait_time, :place_in_line)');
                    $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 3));
                    $waitTime = $totalWaitTimeAhead + $baseWaitTime;
                    $stmtInsert->execute(['name' => $name, 'injury' => $injury, 'severity' => $severity, 'code' => $code, 'wait_time' => $waitTime, 'place_in_line' => $placeInLine]);
                    $newEntryId = $conn->lastInsertId('queue_id_seq');
                    $inserted = true;
                    $placeInLine++;
                }

                $totalWaitTimeAhead += $row['wait_time'];
                $stmtUpdate = $conn->prepare('UPDATE queue SET place_in_line = :place_in_line WHERE id = :id');
                $stmtUpdate->execute(['place_in_line' => $placeInLine, 'id' => $row['id']]);
                $placeInLine++;
            }

            if (!$inserted) {
                $stmtInsert = $conn->prepare('INSERT INTO queue (name, injury, severity, code, wait_time, place_in_line) VALUES (:name, :injury, :severity, :code, :wait_time, :place_in_line)');
                $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 3));
                $waitTime = $totalWaitTimeAhead + $baseWaitTime;
                $stmtInsert->execute(['name' => $name, 'injury' => $injury, 'severity' => $severity, 'code' => $code, 'wait_time' => $waitTime, 'place_in_line' => $placeInLine]);
                $newEntryId = $conn->lastInsertId('queue_id_seq');
            }

            // Recalculate and update wait times for all entries
            $totalWaitTime = 0;
            $stmt = $conn->prepare('SELECT id, severity FROM queue ORDER BY place_in_line');
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $newEntryWaitTime = 0;
            foreach ($rows as $index => $row) {
                $baseWaitTime = max(10, 60 - ($row['severity'] * 5));
                $totalWaitTime += $baseWaitTime;
                if ($row['id'] == $newEntryId) {
                    $newEntryWaitTime = $totalWaitTime;
                }
                $stmtUpdateWaitTime = $conn->prepare('UPDATE queue SET wait_time = :wait_time WHERE id = :id');
                $stmtUpdateWaitTime->execute(['wait_time' => $totalWaitTime, 'id' => $row['id']]);
            }

            $conn->commit();

            return ['success' => true, 'code' => $code, 'waitTime' => $newEntryWaitTime];
        } else {
            return ['success' => false, 'error' => 'Invalid input'];
        }
    } catch (Exception $e) {
        $conn->rollBack();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function check_wait_time($conn, $name, $code)
{
    try {
        $stmt = $conn->prepare("SELECT wait_time FROM queue WHERE name = :name AND code = :code");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            return ['success' => true, 'waitTime' => $user['wait_time']];
        } else {
            return ['success' => false, 'error' => 'User not found or invalid code'];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'error' => 'Query failed'];
    }
}

function admin_login($conn, $input)
{
    $username = $input['username'];
    $password = $input['password'];

    $stmt = $conn->prepare('SELECT * FROM admins WHERE username = ?');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin) {
        $storedPassword = $admin['password'];

        if ($password === $storedPassword) {
            return $admin['id'];
        }
    }

    return false;
}
?>
