<?php
function safe_query($conn, $sql, $params = []) {
    $stmt = mysqli_prepare($conn, $sql);
    if(!empty($params)) {
        $types = str_repeat('s', count($params)); // All strings for simplicity
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    return $stmt;
}
?>