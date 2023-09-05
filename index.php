<?php
session_start();
include_once "carrega.php";

if (isset($_GET['acao'])) {
    $acao = $_GET['acao'];
    include_once $acao;
} else {
    include_once 'paginas/corpo.php';
}
