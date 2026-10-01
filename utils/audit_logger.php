<?php

function logAudit($conn, $user_id, $action, $table, $record_id, $description)
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

    $stmt = $conn->prepare("
        INSERT INTO audit_log
        (user_id, action, table_name, record_id, description, ip_address)
        VALUES
        (:user_id, :action, :table_name, :record_id, :description, :ip)
    ");

    $stmt->execute([
        ':user_id' => $user_id,
        ':action' => $action,
        ':table_name' => $table,
        ':record_id' => $record_id,
        ':description' => $description,
        ':ip' => $ip
    ]);
}