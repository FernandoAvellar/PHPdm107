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

    $container['db'] = function ($c) { 
        $dbConfig = $c['config']['db']; 
        $pdo = new PDO("mysql:host=" . $dbConfig['host'] . ";dbname=" . $dbConfig['dbname'], $dbConfig['user'], $dbConfig['pass']); 
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 
        $db = new NotORM($pdo); 
        return $db; 
    };

    $headerUsername = $_SERVER['PHP_AUTH_USER'];
    $headerPassword = $_SERVER['PHP_AUTH_PW'];

    $userDatabase = $container->db->usuario("usuario = ?", $headerUsername)->fetch();
    $databaseUsername = $userDatabase['usuario'];
    $databasePassword = $userDatabase['senha'];

    if($headerUsername === $databaseUsername && $headerPassword === $databasePassword )
    {
        $app->add(new Tuupola\Middleware\HttpBasicAuthentication([ "users" => [ $databaseUsername => $databasePassword ] ]));

        $app->put('/entrega/{id}', function(Request $request, Response $response){ 

            $id = $request->getAttribute('id'); 

            $parsedBody = $request->getParsedBody();

            $nomeRecebedor = $parsedBody['nomeRecebedor'];
            $cpfRecebedor = $parsedBody['cpfRecebedor'];
            $dataEntrega = $parsedBody['dataEntrega'];    

            $array = array("nomeRecebedor" => $nomeRecebedor, 
                            "cpfRecebedor" => $cpfRecebedor, 
                            "dataEntrega" => $dataEntrega);

            $entregaParaSerAtualizada = $this->db->entrega()->where('id', $id);

            if (!$entregaParaSerAtualizada->fetch()) {
                return $response->withStatus(404);
            }

            $entregaParaSerAtualizada->update($array);
        });

        $app->delete('/entrega/{id}', function(Request $request, Response $response){ 
            
                    $id = $request->getAttribute('id'); 
            
                    $entregaASerDeletada = $this->db->entrega()->where('id', $id);

                    if (!$entregaASerDeletada->fetch()) {
                        return $response->withStatus(404);
                    }

                    $entregaASerDeletada->delete();
                });

    } else {
        $app->add(new Tuupola\Middleware\HttpBasicAuthentication([
            "users" => [
                "ADMIN" => "ADMIN",
                "USER" => "USER"
            ],
            "error" => function ($request, $response, $arguments) {
                $data = [];
                $data["status"] = "error";
                $data["message"] = $arguments["message"];
                return $response->write(json_encode($data, JSON_UNESCAPED_SLASHES));
            }
        ]));

    }
    $app->run(); 
?>