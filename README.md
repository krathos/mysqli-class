MYSQLi Class
=============

**Please understand that using something like this in a modern application is a bad idea. This was created before PDO and Query Builders like those seen in Laravel, CodeIgniter, etc. I highly recommend Laravel if you are looking for a better way.**

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
- Execute INSERT, UPDATE, UPSERT(!!!) and DELETE queries from values that define tables, field names, field values and conditions
- Truncate a table or tables
- Optimize a table or tables
- Send email messages with MySQL access and query errors
- Display the total number of queries performed during all instances of the class

# Usage
```php
require_once "class.db.php";
$db = new DB();
foreach( $db->getResults( "SELECT * FROM users_table" ) as $result )
{
  $name = $result['name'];
  $email = $result['email'];
  
  echo "Name: $name" . "<br />" . "Email: $email" . "<br /><br />";
}
```
## Stored Procedures  
showProcedure($procedure)  
callProcedure($procedure, $params = array(), $responses = array())  
  
## Filtering  
filter($data)  
escape($data)  
clean($data)  
  
## Queries (Getting Data)  
query($query)  
getArray($query, $type = MYSQLI_ASSOC)  
getRow($query, $object = false)  
getResult($query, $pos = 0)  
getResults($query, $object = false)  
  
## Queries (Storing Data)  
insert($table, $variables = array())  
insertMulti($table, $columns = array(), $records = array())  
update($table, $variables = array(), $where = array(), $limit = null)  
upsert($table, $data = array(), $where = array())  
  
## Checks  
tableExists($table)  
numRows($query)  
exists($table = '', $check_val = '', $params = array())  
  
## Utilities  
search($table, $where = array(), $limit = null)  
delete($table, $where = array(), $limit = null)  
affected()  
numFields($query)  
showColumns($table)  
truncate($tables = array())  
optimize($tables = array())  
totalQueries()  
lastQuery()  
lastId()  
  
