<?php

namespace App\Logic;

class DatabaseLogic
{
    public function __construct()
    {
        
    }

    public function createDatabase($domain){
        // Create database with the same name as the domain
        // $database = $domain;
        $query = "CREATE DATABASE IF NOT EXISTS `{$domain}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;";
        $created = $this->executeQuery($query);

        if ($created) {
            // $password = $this->createUser($domain);
            // $this->grantPrivileges($domain);
            // $this->flushPrivileges();
            // return $database;
        } else {
            // return false;
        }
    }

    // public function createUser($domain){
    //     // Create user with the same name as the domain
    //     $user = $domain;
    //     $password = $this->generatePassword();
    //     $query = "CREATE USER '$user'@'localhost' IDENTIFIED BY '$password'";
    //     $this->executeQuery($query);
    //     return $password;
    // }

    // public function grantPrivileges($domain){
    //     // Grant all privileges to the user on the database
    //     $database = $domain;
    //     $user = $domain;
    //     $query = "GRANT ALL PRIVILEGES ON $database.* TO '$user'@'localhost'";
    //     $this->executeQuery($query);
    // }

    // public function flushPrivileges(){
    //     // Flush privileges
    //     $query = "FLUSH PRIVILEGES";
    //     $this->executeQuery($query);
    // }

    public function executeQuery($query){
        $host = env( 'DB_HOST' );
        $user = env( 'DB_USERNAME' );
        $pass = env( 'DB_PASSWORD' );
        $port = env( 'DB_PORT' );

        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $db = new \PDO( "mysql:host=$host; port=$port", $user, $pass, $options );
        } catch( \PDOException $e ) {
            return false;
        }

        $query = $db->query( $query );

        return ! $query ? false: true;
    }

    // public function handle()
    // {
    //     $host = env( 'DB_HOST' );
    //     $user = env( 'DB_USERNAME' );
    //     $pass = env( 'DB_PASSWORD' );
    //     $port = env( 'DB_PORT' );

    //     $options = [
    //         \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
    //         \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    //         \PDO::ATTR_EMULATE_PREPARES   => false,
    //     ];

    //     try {
    //         $db = new \PDO( "mysql:host=$host; port=$port", $user, $pass, $options );
    //     } catch( \PDOException $e ) {
    //         return Command::FAILURE;
    //     }

    //     $query = $db->query( "CREATE DATABASE IF NOT EXISTS `development` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;" );

    //     return ! $query ? Command::FAILURE : Command::SUCCESS;
    // }
}