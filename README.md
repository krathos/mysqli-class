MYSQLi Class
=============

PHP class to access MySQL database wrapper using MySQLi

This class can:

- Connect to a given MySQL server
- Execute SQL queries
- Retrieve the number of query result rows, result columns and last inserted id
- Retrieve the query results in a single array
- Escape a single string or an array of literal text values to use in queries
- Determine if one value or an array of values contain common MySQL function calls
- Check of a table exists
- Check of a given table record exists
- Return a query result that has just one row
- Execute INSERT, UPDATE and DELETE queries from values that define tables, field names, field values and conditions
- Truncate a table or tables
- Optimize a table or tables
- Send email messages with MySQL access and query errors
- Display the total number of queries performed during all instances of the class

# Usage
```php
require_once "class.db.php";
$db = new DB();
foreach( $db->get_results( "SELECT * FROM users_table" ) as $result )
{
  $name = $result['name'];
  $email = $result['email'];
  
  echo "Name: $name" . "<br />" . "Email: $email" . "<br /><br />";
}
```
