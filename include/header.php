<?php
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $role === 'admin' ? 'Admin Panel' : 'User Panel'; ?></title>
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.1.6/datatables.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../assets/css/style.css">
    <!-- Favicon -->
    <link rel="shortcut icon" href="./assets/img/logo.jpg" type="image/x-icon">
</head>

<body>