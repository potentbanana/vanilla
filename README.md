# vanilla
The vanilla database model and persistence library.

## AbstractModel

With vanilla-db you can create an activerecord model that can be stored and retrieved by utilizing the persistence handler built into the vanilla-db package.

```php
class MyModel extends \Vanilla\Models\AbstractModel
{
    /**
     * @var String
     */
    private $someField;
    
    public function getSomeField()
    {
        return $this->someField;
    }
    
    public function setSomeField($someField)
    {
        $this->someField = $someField;
        return $this;
    }
}
```

You may provide a mapping object if your fields are NOT identical to the table. You may also establish a table name by defining the tableName function:
```php
class MyModel extends \Vanilla\Models\AbstractModel
{
    protected $modelMap = [
        "someField" => "field_test"
    ];
    
    public function tableName()
    {
        return "tbl_temporary";
    }
}
``` 

### Foreign Keys
One of the advantages to using our simple vanilla-db library is that we traverse the relationships of the models and persist those. For that we need a few tricks in the AbstractModel and PersistSql classes.

```php
class MyModel extends \Vanilla\Models\AbstractModel
{
    private $myRelatedModel = [];
    
    public function getMyRelatedModel()
    {
        return $this->myRelatedModel;
    }
    
    public function setMyRelatedModel($myRelatedModel)
    {
        $this->myRelatedModel = $myRelatedModel;
        return $this;
    }
       
    public function listModels()
    {
        return [
            "MyRelatedModel" => "modelId"
        ];
    }  
}

class MyRelatedModel extends \Vanilla\Models\AbstractModel
{
    public function foreignKeys()
    {
        return [
            "MyModel" => "modelId"
        ];
    }
}
```

During persistence the foreignKeys method is checked, and if empty, ignored. But if it is not empty it will attempt to record the id of the attached parent model into the foreign key field of the related model.
On load, it will attempt to find all related models and apply them to the main model, thus building a traversable relationship of model-linked data.

```php
$myModel = new MyModel();

$myRelatedModel = new MyRelatedModel();

$myModel->setMyRelatedModel([$myRelatedModel]);

$persistenceHandler = \Vanilla\Persist\PersistFactory::getInstance();
$persistenceHandler->save($myModel);

```