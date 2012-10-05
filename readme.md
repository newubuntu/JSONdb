# 	JSONdb
JSONdb is a noSQL database written entirely in PHP.

The idea of JSONdb came up when I wanted to create a site with dynamic content one weekend. The problem was that the computer I was sat at did have Apache and PHP, but no DB software. Well, I thought, I'll just have to save the data in plain text in an ordinary looking file. After dabbeling with delimiters and basically just made a bulky CSV reader I thought of using JSON to serialize my data. This proved to be a good enough idea that I thought to share it with you guys. This is really nothing special, but I thought it might be usefull for someone somewhere.

## How to use

To use, just include the JSONdb.class.php in your project and make sure you have a safe place to store your JSON files. For security reasons don't put your JSON files anywhere where there is outside access(for obvious reasons). Also make sure that PHP has read and write permissions to the folder(s) in which you store your files.

### Sample code

```PHP
<?php
    // This is the standard constructor, there are som other options that will be explained later
    $mydb = new JSONdb('../dbfiles/', 'mydb.json');
    
    // The buffer is an array of stdClasses
    $row = new stdClass();
    $row->data = "Some data";
    $row->aNumber = 42;
    
    // Writes the change to the buffer, and in this case it auto commits.
    $mydb->save($row);
    
    
    $result = $mydb->getAll();
    foreach($result as $entry) {
        echo "My number: ".$entry->aNumber;
    }
?>
```
