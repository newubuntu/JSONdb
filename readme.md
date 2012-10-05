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

## Methods

### Constructor

```PHP
    public function __construct($db_folder, $db_file, $auto_commit = true, $create_if_not_exists = false)
```

The ``` $db_folder``` is the folder in which you want to save your files.

The ``` $db_file``` is the file in which you want to save your data.

If you set ``` $auto_commit``` to true, all calls to functions that edit the data will be directly written to file.

If you set ``` $create_if_not_exists``` to true, JSONdb will try to create the database file if it does not allready exist.

### Save

```PHP 
    public function save($data)
```

This method will save ``` $data``` to the buffer, and if ``` $auto_commit``` was set to true, it will write the changes to disk.

I prefer to add the data in the form of ``` stdClass()``` but an assoc array or ordinary array should work as well. Note that when you load the data back it will be a ``` stdClass()``` and not an array. There will probably be an option to get the data as an array in a future version.

### Edit methods

```PHP 
    public function edit_where($search, $key, $replace)
```

This method will search for the key ```$key``` with the value ```$search ```and replace the value with ``` $replace```.

***

```PHP 
    public function edit_what_where($key, $search, $rkey, $replace)
```

This method will do as the above, but with differens that it allows you to change/create another key ``` $rkey```.


***

```PHP 
    public function edit_row($row, $data)
```

This method allows you to write to row number ``` $row```.

### Fetch methods

```PHP 
    public function get_all()
```

This function will return the data buffer as it is represented internaly.


***

```PHP
    public function get_slice($offset, $count)
```

This method will return a slice of the buffer. It will begin at "row" ``` $offset``` and continue ``` $count``` number of rows. It throws an exception if you try to get a row that does not exist


***

```PHP 
    public function get_row($row)
```

This method returns a specific row. Internaly every row is stored as an array like so ``` $this->buffer[]``` and this method acts as a wrapper to read diffrent indicies.


***

```PHP 
    public function get_row_where($key, $value)
```

This method returns a row with the key ``` $key``` and value ``` $value```. Simple as that.

### Search

```PHP 
    public function public function search($kv_array)
```

This method takes an assoc array and serches for the key with the corresponding value.

### Commit changes

```PHP 
    public function commit()
```

Use this method to commit changes if you constructed the database without auto commit.
