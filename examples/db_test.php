<?php
require_once(__DIR__."/../internal-src/constants.php"); //how to bring in the constants file

$dbh = new PDO(DB_INFO); //how to connect to the database

// How to do generic run-of-the-mill queries
$dbh->query("CREATE TABLE IF NOT EXISTS php_test (id serial NOT NULL, hello text, world text, CONSTRAINT php_test_pkey PRIMARY KEY (id));");



//How to use transactions to safely do multiple inserts in one go"
$dbh->beginTransaction(); //Start the transaction
//Everything we do here will be in limbo until the commit command

$dbh->exec("INSERT INTO php_test (hello, world) VALUES ('hello', 'world');");
$dbh->exec("INSERT INTO php_test (hello, world) VALUES ('hi', 'mom');");

$dbh->commit(); // This will execute the above queries together, ensuring that nothing could happen with the database in between



// How to use prepared statements and receive data from queries
$specific_hello_query = "SELECT * FROM php_test WHERE hello = :hello_col"; //our SQL query, the :hello_col is a placeholder
$specific_hello_stmt = $dbh->prepare($specific_hello_query); //Compiles this into a PreparedStatement


$hello_col = "hello"; //pretend this is user data
$specific_hello_stmt->execute(['hello_col' => "hi"]); //this runs the query on the DB with the user data as specified
$results = $specific_hello_stmt->fetchAll(); // loads the results
//displays the results
foreach($results as $row) {
    print $row['id'] . "\t";
    print $row['hello'] . "\t";
    print $row['world'] . "\n";
}

//We could now also reuse $specific_hello_stmt to run queries with different parameters if we wanted to
$specific_hello_stmt->execute(['hello_col' => "hello"]); //this runs the query on the DB with the user data as specified
$results = $specific_hello_stmt->fetchAll(); // loads the results
//displays the results
foreach($results as $row) {
    print $row['id'] . "\t";
    print $row['hello'] . "\t";
    print $row['world'] . "\n";
}
?>