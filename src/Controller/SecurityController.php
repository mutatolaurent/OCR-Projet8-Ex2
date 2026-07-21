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

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): Void
    {
        //throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
        //return $this->redirectToRoute('app_accueil');
    }

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

        $secret = $user->getGoogleAuthenticatorSecret();

        // Générer l'URI TOTP pour Google Authenticator
        $qrContent = $googleAuthenticator->getQRContent($user);

        // Générer le QR code (PNG en base64)
        // $result = Builder::create()
        //     ->writer(new PngWriter())
        //     ->data($qrContent)
        //     ->size(300)
        //     ->margin(10)
        //     ->build();

        $result = (new Builder(
            writer: new SvgWriter(),
            writerOptions: [],
            validateResult: false,
            data: $qrContent,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        ))->build();

        $qrCodeDataUri = $result->getDataUri();

        return $this->render('security/enable_2fa.html.twig', [
            'secret' => $secret,
            'qrCodeDataUri' => $qrCodeDataUri,
        ]);
    }
}
