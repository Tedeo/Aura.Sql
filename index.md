---
title: The Aura SQL connecting with Relational Database
layout: cloud
---

Getting Started
---------------

The Aura.Sql package will provide adapters for connecting to these relational database systems:

- Microsoft SQL Server (Windows only)
- MySQL
- Oracle
- Postgres
- Sqlite 2
- Sqlite 3

The adapters are being ported and refactored from the Solar_Sql adapters.

#Creating a connection with Aura.Sql

Add the below line in the configuration of the Package you want to use.

    //Setting Class Constructor Params
    $di->params['Aura\Sql\ConnectionFactory'] = array(
        'forge' => $di->getForge(),
        'map'   => array(
            'mysql'         => 'Aura\Sql\Connection\Mysql',
            'sqlsrv'        => 'Aura\Sql\Connection\Sqlsrv',
            'sqlsrv_denali' => 'Aura\Sql\Connection\SqlsrvDenali',
        ),
    );
    //Setting Class Constructor Params
    $di->params['Aura\Sql\ConnectionManager'] = array(
        'factory' => $di->lazyNew('Aura\Sql\ConnectionFactory'),
        'default' => array(
            'adapter'  => 'mysql',
            'dsn'      => array(
                'host' => 'localhost',
                'dbname' => 'database_name',
            ),
            'username' => 'monty',
            'password' => 'some_pass',
            'options' => array(),
        ),
    );
    //Setting Class Constructor Params
    $di->params['Aura\Sql\Connection\AbstractConnection'] = array(
        'signal' => $di->lazyGet('signal_manager'),
    );
    //Setting Class Constructor Params
    $di->set('sql_connection_manager', function() use ($di) {
        return $di->newInstance('Aura\Sql\ConnectionManager');
    });
    //if we call $di->get('sql_connection_manager'); , we will be having connection object


#Connecting with Models

As DI is mainly used we never try to create an object explicity by the keyword new. Everything is created via [DI](http://auraphp.github.com/Aura.Di/). If you have still not looked how the DI works, [please have a look](http://auraphp.github.com/Aura.Di/).

So to pass the connection to a model we need several pieces:

*  An abstract model class. For this example, we'll use a TableDataGateway type of model; it is simple and straightforward. It needs an SQL connection manager to connect to the database.

Let us create a recruitment table and make some query from a model .

- The actual TableDataGateway model class for the "recruitment" table.

- A factory to create TableDataGateway model instances for you.

- A manager to retain TDG model instances; this way you only ever have one copy of a model object.  The manager uses the factory to create the instance as needed.

- The page controller, which retrieves TDG instances from the manager.

Here they are, one by one.

First, the abstract model class.  The config at the top sets up an SQL connection manager service, and tells the abstract class to use that service for the `connections` constructor param.

    $di->params['Vendor\Package\AbstractTableDataGateway']['connections'] = $di->lazyGet('sql_connection_manager');

    namespace Vendor\Package;
    use Aura\Sql\ConnectionManager;

    class AbstractTableDataGateway
    {
        protected $connections;

        protected $table_name;

        public function __construct(ConnectionManager $connections)
        {
            $this->connections = $connections;
        }

        public function fetchAll($where)
        {
            $stmt = "SELECT * FROM {$this->table_name} WHERE $where";
            $read = $this->connections->getRead();
            return $read->fetchAll($stmt);
        }

        public function insert()
        {

        }

        public function update()
        {

        }

        public function delete()
        {

        }
    }


Second, the actual model class. This requires no config, since it descends from the abstract (which is already configured).


    namespace Vendor\Package;

    class Recruitment extends AbstractTableDataGateway
    {
        $this->table_name = 'recruitment';
    }


Third, a factory so we can create instances without having to use the `new` keyword.  The config at the top tells the factory to use the DI forge object for its constructor param.  Note the `$map` property is hardcoded here; you could make is a param too if you wanted.


    $di->params['Vendor\Package\TableDataGatewayFactory']['forge'] = $di->getForge();

    namespace Vendor\Package;
    use Aura\Di\ForgeInterface;

    class TableDataGatewayFactory
    {
        protected $forge;

        protected $map = array(
            'my_table_name' => 'Vendor\Package\TableName'
        );

        public function __construct(ForgeInterface $forge)
        {
            $this->forge = $forge;
        }

        /*
         * This way we can set the map from config/default.php , or the one above
         *
         * $di->params['Vendor\Package\TableDataGatewayFactory']['map'] = array(
         *     'recruitment' => 'Vendor\Package\Recruitment',
         *     'user' => 'Vendor\Package\User'
         * );
         * 
         */
        /*
        public function __construct(ForgeInterface $forge, array $map = array())
        {
            $this->forge = $forge;
            $this->map = $map;
        }
        */

        public function newInstance($name)
        {
            $class = $this->map[$name];
            return $forge->newInstance($class);
        }
    }


Fourth, the manager.  The config at the top creates a service in the DI container for the factory, and then uses that service as a constructor param for the manager.


    $di->params['Vendor\Package\TableDataGatewayManager']['factory'] = $di->lazyGet('tdg_factory');
  
    $di->set('tdg_factory', function() use ($di) {
        return $di->newInstance('Vendor\Package\TableDataGatewayFactory');
    });

    namespace Vendor\Package;

    class TableDataGatewayManager
    {
        protected $gateways = array();

        protected $factory;

        public function __construct(TableDataGatewayFactory $factory)
        {
            $this->factory = $factory;
        }

        public function get($name)
        {
            if (! isset($this->gateways[$name])) {
                $this->gateways[$name] = $this->factory->newInstance($name);
            }

            return $this->gateways[$name];
        }
    }


Fifth, and finally, we get to the page controller.  The config at the top adds the TDG instance manager as a service in the DI container, then uses a *setter* on the page controller to inject that manager.  (We don't use a constructor param here because that means you would have to copy the entire Aura\Web\Page constructor; easier and less error-prone to use a setter method.)


     $di->set('tdg_manager', function () use ($di) {
         return $di->newInstance('Vendor\Package\TableDataGatewayManager');
     });
  
     $di->setter['Vendor\Package\Web\MyController\Page']['setTableDataGatewayManager'] = $di->lazyGet('tdg_manager');

     namespace Vendor\Package\Web\MyController;
     use Aura\Web\Page as PageController;

     class Page extends PageController
     {
        protected $tdg_manager;

        /**
         * By setter injection when ever a call to this controller happens , 
         * we set the TDG Manager object via setTableDataGatewayManager method.
         *
         */
        public function setTableDataGatewayManager($tdg_manager)
        {
            $this->tdg_manager = $tdg_manager;
        }

        public function actionIndex()
        {
            $model = $this->tdg_manager->get('recruitment');
            $this->response->view_data->rows = $model->fetch('id <= 10');
        }
    }


That's one example of what the class definitions and config/wiring would look like using dependency injection.

The one above is keeping all the TDG, TDF , Model files in src folder. But if you want to move the files inside Model folder, just move it and add Model infront of the namespace and object creation.

If you want to [see an example in action](https://github.com/harikt/Bridge.Careers/) . Switch between the branches. 
MinSrc means Model in SRC .
Model in Model folder.

Hope you enjoyed connecting with model in system.