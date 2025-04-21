<?php
namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Bundle\SecurityBundle\Security; // <-- Importe le service Security

class AppAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private Security $security // <-- Injecte le service Security
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');

        // Stocke l'email dans la session
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        // Crée un "passeport" pour l'authentification sans token CSRF
        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', ''))
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // Redirige les administrateurs vers /admin
        if ($this->security->isGranted('ROLE_ADMIN')) { // <-- Utilise le service Security
            return new RedirectResponse($this->urlGenerator->generate('app_admin'));
        }

        // Redirige les utilisateurs normaux vers /user/dashboard
        return new RedirectResponse($this->urlGenerator->generate('app_user_dashboard'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}