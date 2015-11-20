# Database / ORM

Out of the box Eve uses by default [Eden MySQL](https://github.com/Eden-PHP/Mysql) and can easily be interchanged with [Eden PostGres](https://github.com/Eden-PHP/Postgre) and [Eden SQLite](https://github.com/Eden-PHP/Sqlite) by simply updating your `settings/databases.php` like the following example and choosing one to be the default database.

```
return array (
    'custom mysql' => array(
        'host'    => '127.0.0.1',
        'name'    => 'eve_framework',
        'user'    => 'root',
        'pass'    => '',
        'type'    => 'mysql',
        'default' => true),
    'custom sqlite' => array(
        'file'    => '/path/to/sqlite.db',
        'type'    => 'sqlite',
        'default' => false),
    'custom postgre' => array(
        'host'    => '127.0.0.1',
        'name'    => 'eve_framework',
        'user'    => 'root',
        'pass'    => '',
        'type'    => 'postgre',
        'default' => false)
);
```

If you would like to use another ORM you may do so by updating the same `settings/databases.php` and add/replace:

```
	'custom ORM' => array(
		'type' => new [CUSTOM ORM](),
		'default' => true
	),
```

If you are using a custom ORM, there is no need to read the rest of this section.

====

The following documentation applies to:

 - [Eden MySQL](https://github.com/Eden-PHP/Mysql)
 - [Eden PostGres](https://github.com/Eden-PHP/Postgre)
 - [Eden SQLite](https://github.com/Eden-PHP/Sqlite)

<a name="selecting"></a>
## Selecting a Database

In Eve, you can register multiple databases, even if they are different database types. The default database as defined in `settings/databases.php` can be accessed simply by using `eve()->database()`.

Other databases that are not the default can be accessed using `eve()->database('custom sqlite')` or whatever the label of the database is as defined in `settings/databases.php`.

<a name="basic"></a>
## Basic Querying

On a very low level can call raw queries as in Figure 2.

**Figure 2. Raw Query**

```
eve()->database()->query('SELECT * FROM user');  // returns results of raw queries
```

Eve uses PDO to communcate with your database. It's recommended you bind incoming variables contributed by the end user. Still on a low level, this can be acheived as in `Figure 3`.

**Figure 3. Raw Binding**

```
$query  = 'SELECT * FROM user WHERE user_name LIKE :user_name AND user_active = :user_active';
$bind   = array(':user_name' => '%'.$userName.'%', ':user_active' => 1);

eve()->database()->query($query, $bind); // returns results of raw queries
```

The above figure sets `$query` to a string with binded place holders `:user_name` and `:user_active`. `$bind` has the actual values these placeholders and should be replaced with duing execution of the query. We encourage this method because binding values prevents database injections.

> **Note:** Binded variables must start with a colon(:).

====

<a name="manipulation"></a>
## Data Manipulation

If you prefer the ORM way to save data Figure 4 provides several method examples on how to acheive this.

Figure 4. Data Manilpulation

```
$settings = array(
	'user_name'     => 'Chris'
	'user_email'    => 'myemail@mail.com');
	 
$filter[] = array('user_id=%s', 1);     

// inserts row into 'user' table
eve()->database()->insertRow('user', $settings);
// updates rows in 'user' table where user_id is 
eve()->database()->updateRows('user', $settings, $filter);
// delete rows in 'user' table where user_id is 1
eve()->database()->deleteRows('user', $filter);

//updates data if there is a user_email with the value of anemail@gmail.com otherwise will insert
eve()->database()->setRow('user', 'user_email', 'anemail@gmail.com', $settings);         
```

Inserting data is pretty trivial. We included 2 ways to insert data. Like getRow(), there's no need to worry about binded data because Eve wil do this for you. Figure 4 shows the 2 kind of inserts mentioned.

**Figure 4. Two ways to insert**

```
$settings = array('user_name' => 'Chris', 'user_email' => 'myemail@mail.com');
eve()->database()->insertRow('user', $settings);         // inserts row into 'user' table
 
$settings = array();
$settings[] = array('user_name' => 'Chris', 'user_email' => 'myemail@mail.com');
$settings[] = array('user_name' => 'Dan', 'user_email' => 'myemail2@mail.com');
$settings[] = array('user_name' => 'Clark', 'user_email' => 'myemail3@mail.com');
eve()->database()->insertRows('user', $settings);            // inserts multiple rows into 'user' table
```

So obviously `insertRow()` should be used if you just want to insert one row. Inserting two or more rows at the same time, you should use `insertRows()`. This method expects an array of arrays, or an array table.

> **Note:** A common error found amongst programmers, is simply using `insertRows()` instead of `insertRow()`.

> **Note:** Using models and collections, you don't really need to worry about this method because it's covered in the `save()` method in a collection or model object. We'll go over models and collections later in this section.

Updating is about as easy as inserting. There's only one method you need to know.

**Figure 5. Updating**

```
$settings = array('user_name' => 'Chris', 'user_email' => 'myemail@mail.com');
$filter[] = array('user_id=%s', 1);
eve()->database()->updateRows('user', $settings, $filter); // inserts row into 'user' table
```

A common scenario is when you need to insert if a column value is not found and update if it is. We added an extra method called `setRow()` to simply to save you some lines of redundancy.

**Figure 6. Insert or update**

```
$settings = array('user_name' => 'Chris2', 'user_email' => 'myemail@mail.com');
eve()->database()->setRow('user', 'user_email', 'myemail@mail.com', $settings);
```

`Figure 6` basically says, in user table, if `myemail@mail.com` exists in the `user_email` column, then update that row. If not then insert. Removing data is simple enough as well.

**Figure 7. Remove**

```
$filter[] = array('user_id=%s', 1);
eve()->database()->deleteRows('user', $filter); // delete rows in 'user' table where user_id is 1
```

====

<a name="searching"></a>
## Searching

A better way to build complex queries is with using the search object. An overview example can be found in `Figure 8`.

**Figure 8. MySQL Search**

```
eve()
	->database()
	->search('user')
	->setColumns('*')
	->innerJoinOn('group', 'group_owner=user_id')
	->leftJoinUsing('friends', 'user_id')
	->filterByUserName('Chris')
	->addFilter("user_last LIKE '%s%%'", 'Brown')
	->sortByUserId('ASC')
	->addSort('user_last', 'DESC')
	->setRange(25)
	->setStart(75)
	->getRows();
```

In the figure above there's a few methods being powered with magic, but we'll just start going down the line. First off, to instantiate the search object you simply need to call `search()` passing the name of the table as the argument. Secondly we call `setColumns()`. This call is optional, but if used, can either accept an array of columns or an argument separated list of columns, ie. `setColumns('user_id', 'user_name')`. Next, `innerJoinOn()` is the new way we accept joins. There are eight methods dedicated to different kinds of joins.

**Kinds of Join methods**

```
innerJoinOn()
innerJoinUsing()
leftJoinOn()
leftJoinUsing()
rightJoinOn()
rightJoinUsing()
outerJoinOn()
outerJoinUsing()
```

No matter what methods you choose from above there are two arguments you need to add. The first argument is the name of the table you would like to join and the second one is the how they relate to each other.

The first magic powered method is called `filterByUserName()`. There is no actual method called `filterByUserName()` in the MySQL class. Instead when this function is called it will parse out the name of the method and recognize that UserName is the name of a column and convert that into `addFilter('user_name=%s', 'Chris')` as in `Figure 8`.

`addFilter()` generally accepts two arguments. The first argument is the filter clause. If you notice in our filter example in `Figure 8` we use %s to delimit a binded value. You can have as many binded values per filter as you like. The following arguments need to include the binded values in order of when they occur in the filter clause.

The second magic powered mehod is called `sortByUserId('ASC')`.There is no actual method called `sortByUserId('ASC')` in the MySQL class. Instead when this function is called it will parse out the name of the method and recognize that UserId is the name of a column and convert that into `addSort('user_id', 'ASC')` as in `Figure 8`.

There are three kinds of pagination methods also available

**Pagination Methods**

```
setRange(75)
setStart(25)
setPage(1)
```

It's important if you are going to use `setPage(1)` to call `setRange(75)` first because the underlying function simply calculates the start index based on the range. Two other methods that are not covered by `Figure 8` are the ability to group and to set the table to something else.

**Figure 9. Other Useful methods**

```
->setTable('user')
->setGroup('user_active')
```

### Getting Results

When your happy with your query you can retrieve the results in 3 ways as described in Figure 0.

**Figure 10. Retrieving Results**

```
->getTotal()
->getRows()
->getCollection()
```

`Figure 10` shows three ways to get the results, the first way `getTotal()`, will retrieve the total number and does not consider pagination elements. `getRows()` will simply return a raw array. `getCollection()` will return you an object with the results for further manipulation. 

====

<a name="collection"></a>
## Collections

Collection objects can be made in 2 ways.

 - From `$collection = eve()->database()->search('table') ... ->getCollection();`
 - From `$collection = eve()->database()->collection();`

Collections do exactly the same thing as models except it manipulates multiple models instead. Collections can be iterable and access as arrays as well. Collections only hold model objects so if you wanted to use your own extended model, you would need to call `setModel('Your_Model')`.

**Figure 11. MySQL Collections**

```
//set user name for all rows
$collection->setUserName('Chris');

// set or get any abstract key for all rows
$collection->setAnyThing()

//collections are iterable
foreach($collection as $model) {        
	echo $model->getUserName().' ';
	echo $model['user_email'];
}
 
//access as array
echo $collection[0]['user_name'];
//set as array
$collection[0]['user_email'] = 'my@email.com'; 
 
$collection->save('user', eve()
	->database());    //save to 'user' table in database
										//only relavent columns will be saved
										//for all rows
```

Some other utility methods not covered by th above examples are date formating and copying from one column to another. `Figure 12`, show how we would go about doing these things.

**Figure 12. Utility methods**

```
//formats a date column
$collection->formatTime('post_created', 'F d, y g:ia'); 

//for each row, copy the value of post_user to the user_id column
$collection->copy('post_user', 'user_id');

//remove the row with the index of 1, reindexes all the rows
$collection->cut(1);

//returns the number of rows
$collection->count();

//adds a new row
$collection->add(array('post_title' => 'Hi'));

//returns a table array (no objects)
$collection->get();                                      
```

====

<a name="model"></a>
## Models

Model objects can be made in 3 ways.

 - From `$model = eve()->database()->search('table') ... ->getModel();`
 - From `$model = $collection[$i];`
 - From `$model = eve()->database()->model();`

We managed to loosely define models which takes off the restrictiveness of a normal ORM and adds scalability as an end result. First off, what we did was define a generic, yet powerful model class that can be extended, but also can be used as is. Our model class is already powerful enough to solve for a lot of use cases, you might not need to extend it. We played around with the concept of "loosely defined" and here's what we came up with.

**Figure 13. Database Model (Extends Array)**

```
$model->setUserName('Chris');            //set user name
$model->getUserEmail();                  // returns user email
 
//$model->setAnyThing()              // set or get any abstract key
 
echo $model['user_name'];               //access as array
$model['user_email'] = 'my@email.com';  //set as array
 
echo $model->user_name;              //access as object
$model->user_name = 'my@email.com';      //set as object
 
$model->save('user'); //save to 'user' table in database
						//only relavent columns will be saved
```

So model properties can be accesed by method, object or array. The preference we leave up to you. With our model, you can put extra key values in the object, even if it has nothing to do with the intended database table. When you call `save()`, this is when you need to specify the table your saving to. This method is really powerful, in that it will first check to see what columns exist in the table then compares it with your model. It will only save columns that have the matching column name in your object. Lastly it will auto determine whether if we should insert or update that row.

A common example is when you have an array table that comprises of joined data. Here, you can leave that array as is then call `save()` for each table as in `Figure 14`.

**Figure 14. Two tables**

```
$row = array(
	'user_id'       => 1,
	'user_name'     => 'Chris',
	'user_email'    => 'my@email.com',
	'post_user'     => 1,
	'post_title'    => 'My Post',
	'post_detail'   => 'This is my new article');
	 
$db->model($row)->save('user')->save('post');
```

> **Note:** You can also save to different databases as in `save('post', $db2)`

====

<a name="together"></a>
## Putting it all together

So a common scenario would be retrieving data, manipulating the results and sending back to the database. Let's see how search, collection and model objects how we can acheive this.

**Figure 15. The Coolest Thing Ever!**

```
//load database
eden('mysql', MYSQL_HOST, MYSQL_NAME, MYSQL_USER, MYSQL_PASS)
	
	//search user table
	->search('user')
	
	//WHERE user_gender = $_get['gender']
	->filterByUserGender($_GET['gender'])
	
	//ORDER BY user_id
	->sortByUserId('ASC')
	
	//LIMIT 75, 25
	->setStart(75)->setRange(25)
	
	//get a collection object
	->getCollection()
	
	//sets all users to active
	->setUserActive(1)
	
	//Set a new column post_title
	->setPostTitle('A '.$_GET['gender'].'\'s Post')
	
	//Set a new column post_detail
	->setPostDetail('Content is King')
	
	//Copy the contents of user_id to a new column post_user
	->copy('user_id', 'post_user')
	
	//Set a new column post_created
	->setPostCreated(time())
	->formatTime('post_created', 'Y-m-d H:i:s')
	
	//save to user table
	->save('user')
	
	//save to post table
	->save('post');
```