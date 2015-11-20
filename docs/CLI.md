# CLI

```
$ vendor/bin/eve help
```

```
[eve] Help Menu
[eve] - `eve generate <schema*> <namespace>`    Generates files based on schema
[eve] - `eve database <schema*>`                Generates database table/s schema
[eve] - `eve install`                           Generates default framework files
[eve] - `eve job <name*> <data*>`               Executes a job
[eve] - `eve queue <name*> <data*>`             Queues a job
```

<a name="generators"></a>
## Generators


Eve comes with a code generator which is only recommended to use if you understand how the underlying structure, classes and coding standards included in this framework. Example schemas can be found in the `schema` folder. To get an overview of the capabilities of the generator you may do so with the following command.

```
$ vendor/bin/eve generate sink
```

This command will generate the kitchen sink code as defined by `schema/sink.php`. To also add this schema to your database you may do so with the following command.

```
$ vendor/bin/eve database sink
```

To see the sink UI add the following to App/Back/routes.php

```
    '/control/sink/search' => array(
        'method' => 'GET',
        'class' => '\\Eve\\App\\Back\\Action\\Sink\\Search'
    ),
    '/control/sink/create' => array(
        'method' => 'ALL',
        'class' => '\\Eve\\App\\Back\\Action\\Sink\\Create'
    ),
    '/control/sink/update' => array(
        'method' => 'ALL',
        'class' => '\\Eve\\App\\Back\\Action\\Sink\\Update'
    ),
    '/control/sink/remove' => array(
        'method' => 'GET',
        'class' => '\\Eve\\App\\Back\\Action\\Sink\\Remove'
    ),
    '/control/sink/restore' => array(
        'method' => 'GET',
        'class' => '\\Eve\\App\\Back\\Action\\Sink\\Restore'
    ),
```

Then visit `127.0.0.1/control/sink/search` to see the kitchen sink generated visually from the schema. 

## Jobs and Queue

See: [Jobs](https://github.com/Eve-PHP/Framework/blob/master/docs/Jobs.md).