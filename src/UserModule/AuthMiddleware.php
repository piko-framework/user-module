<?php
namespace Piko\Usermodule;

use PDO;
use Piko\ModularApplication;
use HttpSoft\Message\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Piko\UserModule\Models\User;

final class AuthMiddleware implements MiddlewareInterface
{
    private ModularApplication $application;

    public function __construct(ModularApplication $app)
    {
        $this->application = $app;

        $pdo = $this->application->getComponent('PDO');
        assert($pdo instanceof PDO);
        // User::setPDO($pdo);
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\MiddlewareInterface::process()
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $this->application->getComponent('Piko\User');
        assert($user instanceof \Piko\User);

        $router = $this->application->getComponent('Piko\Router');
        assert($router instanceof \Piko\Router);

        $loginUrl = $router->getUrl('user/default/login');

        $allowedUrls = [
            $loginUrl,
            $router->getUrl('user/default/reminder'),
            $router->getUrl('user/default/reset-password'),
            $router->getUrl('user/default/check-registration'),
        ];

        $params = $request->getServerParams();
        $path = rtrim(parse_url($params['REQUEST_URI'], PHP_URL_PATH), '/');

        if ($user->isGuest() && !in_array($path, $allowedUrls)) {

            $response= new Response();

            return $response->withHeader('Location', $loginUrl);
        }

        return $handler->handle($request);
    }
}
