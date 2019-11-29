<?php
/**
 * Created by PhpStorm.
 * User: quinho
 * Date: 31/10/17
 * Time: 17:50
 */
session_start();

if (isset($_SESSION[$_POST['pudo_id']])) {
    echo json_encode([
        'latitude' => $_SESSION[$_POST['pudo_id']]['latitude'],
        'longitude' => $_SESSION[$_POST['pudo_id']]['longitude'],
        'address' => $_SESSION[$_POST['pudo_id']]['address'],
        'time' => $_SESSION[$_POST['pudo_id']]['time']
    ]);
    return;
}

echo json_encode(['latitude' => '', 'longitude' => '', 'address' => '', 'time' => '']);