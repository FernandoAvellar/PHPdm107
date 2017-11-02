<?php 
    use \Psr\Http\Message\ServerRequestInterface as Request; 
    use \Psr\Http\Message\ResponseInterface as Response;
    require '../vendor/autoload.php';

    $config['displayErrorDetails'] = true; 
    $config['addContentLengthHeader'] = false;
    $config['db']['host'] = "localhost"; 
    $config['db']['user'] = "root"; 
    $config['db']['pass'] = ""; 
    $config['db']['dbname'] = "dm107";

    $app = new \Slim\App(["config" => $config]);
    $container = $app->getContainer();

    $username = "ADMIN";
    $password = "ADMIN";
    $app->add(new Tuupola\Middleware\HttpBasicAuthentication([ "users" => [ $username => $password ] ]));

    $container['db'] = function ($c) { 
        $dbConfig = $c['config']['db']; 
        $pdo = new PDO("mysql:host=" . $dbConfig['host'] . ";dbname=" . $dbConfig['dbname'], $dbConfig['user'], $dbConfig['pass']); 
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 
        $db = new NotORM($pdo); 
        return $db; 
    };

    $app->put('/entrega/{numeroPedido}', function(Request $request, Response $response){ 

        $numeroPedido = $request->getAttribute('numeroPedido'); 

        $parsedBody = $request->getParsedBody();

        $nomeRecebedor = $parsedBody['nomeRecebedor'];
        $cpfRecebedor = $parsedBody['cpfRecebedor'];
        $dataEntrega = $parsedBody['dataEntrega'];    

        $array = array("nomeRecebedor" => $nomeRecebedor, 
                        "cpfRecebedor" => $cpfRecebedor, 
                        "dataEntrega" => $dataEntrega);

        $entregaParaSerAtualizada = $this->db->entrega()->where('numeroPedido', $numeroPedido);

        if (!$entregaParaSerAtualizada->fetch()) {
            return $response->withStatus(404);
        }

        $entregaParaSerAtualizada->update($array);
    });

    $app->delete('/entrega/{numeroPedido}', function(Request $request, Response $response){ 
        
                $numeroPedido = $request->getAttribute('numeroPedido'); 
        
                $entregaASerDeletada = $this->db->entrega()->where('numeroPedido', $numeroPedido);

                if (!$entregaASerDeletada->fetch()) {
                    return $response->withStatus(404);
                }

                $entregaASerDeletada->delete();
            });

    $app->run(); 
?>