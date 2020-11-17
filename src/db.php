<?php

function getDB()
{
    $dbhost="localhost";
    $dbuser="";
    $dbpass="";
    $dbname="";
    $dbConnection = new PDO("pgsql:host=$dbhost;port=5432;dbname=$dbname;user=$dbuser;password=$dbpass;options='--client_encoding=UTF8'");
    $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbConnection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $dbConnection;
}