<?php

function create_notification($pdo, $complaint_id, $user_id, $title, $message)
{
    $stmt = $pdo->prepare("
        INSERT INTO notifications (
            complaint_id,
            user_id,
            title,
            message,
            created_at
        ) VALUES (?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $complaint_id,
        $user_id,
        $title,
        $message
    ]);
}

?>