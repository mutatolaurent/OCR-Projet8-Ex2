<?php

namespace App\Controller;

use App\Entity\Employe;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Test\Constraint\ResponseHasCookie;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;

class SecurityController extends AbstractController
{
    // Page de connexion
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    // page de déconnexion (logout) - cette route est interceptée par le firewall de sécurité
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): Void
    {
        //throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
        //return $this->redirectToRoute('app_accueil');
    }

    // Page pour activer la double authentification (2FA) avec Google Authenticator
    #[Route('/profil/2fa', name: 'app_profil_2fa')]
    public function enable2fa(
        EntityManagerInterface $em,
        GoogleAuthenticatorInterface $googleAuthenticator
    ): Response {
        /** @var Employe $user */
        $user = $this->getUser();
        if (!$user instanceof Employe) {
            throw $this->createAccessDeniedException();
        }

        // Générer un secret si absent
        if (!$user->getGoogleAuthenticatorSecret()) {
            $secret = $googleAuthenticator->generateSecret();
            $user->setGoogleAuthenticatorSecret($secret);
            $em->flush();
        }

        // Récupérer le secret pour l'afficher dans la vue
        $secret = $user->getGoogleAuthenticatorSecret();

        // Générer l'URI TOTP pour Google Authenticator
        $qrContent = $googleAuthenticator->getQRContent($user);

        // Générer le QR code en SVG
        $result = (new Builder(
            writer: new SvgWriter(),                            // génère un QR code au format SVG (vectoriel)
            writerOptions: [],                                  // aucunes options spécifiques au writer choisi.
            validateResult: false,                              // pas de validation automatique du QR code généré
            data: $qrContent,                                   // URI TOTP générée par SchebTwoFactorBundle
            encoding: new Encoding('UTF-8'),                    // encodage des caractères utilisés pour data
            errorCorrectionLevel: ErrorCorrectionLevel::Low,    // niveau de correction d’erreur du QR code
            size: 300,                                          // taille du QR code en pixels (largeur/hauteur)
            margin: 10,                                         // marge blanche autour du QR code, en pixels
            roundBlockSizeMode: RoundBlockSizeMode::Margin,     // ajuste la taille des blocs en jouant sur la marge,
        ))->build();

        // Récupérer le QR code sous forme de Data URI pour l'afficher dans la vue
        $qrCodeDataUri = $result->getDataUri();

        // Rendre la vue avec le secret et le QR code
        return $this->render('security/enable_2fa.html.twig', [
            'secret' => $secret,
            'qrCodeDataUri' => $qrCodeDataUri,
        ]);
    }
}
