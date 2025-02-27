<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/security/", name="security")
     */
    public function index()
    {
        return $this->render('security/index.html.twig', [
            'controller_name' => 'SecurityController',
        ]);
    }

    /**
     * @Route("/security/signup", name="security_signup")
     */
    public function signup(Request $request, UserPasswordEncoderInterface $passwordEncoder) {
        if ($this->getUser() != null) {
            return $this->redirectToRoute('index');
        }

        $tmpUser = new User();

        $form = $this->createFormBuilder($tmpUser)
            ->add('email', EmailType::class, ['label' => 'security.email.email', 'attr' => ['placeholder' => 'security.email.email']])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'security.password', 'attr' => ['placeholder' => 'security.password']],
                'second_options' => ['label' => 'security.repeat-password', 'attr' => ['placeholder' => 'security.repeat-password']],
                ])
            ->add('register', SubmitType::class, ['label' => 'security.register', 'attr' => ['class' => 'btn-success btn-block']])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $formData = $form->getNormData();
            $user = new User();
            $user->setEmail($formData->getEmail());
            $user->setPassword($passwordEncoder->encodePassword($user, $formData->getPassword()));
            $this->em->persist($user);
            $this->em->flush();

            return $this->redirectToRoute('security_login');

            $this->addFlash('danger', $this->trans->trans('security.registerToken.invalid'));
        }
        $form = $form->createView();
        return $this->render('security/login.html.twig', compact('form'));
    }

    /**
     * @Route("/security/login", name="security_login")
     */
    public function login(AuthenticationUtils $authenticationUtils) {
        if ($this->getUser() != null) {
            return $this->redirectToRoute('index');
        }
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        $form = $this->createFormBuilder(['remember_me' => true])
            ->add('email', EmailType::class, ['label' => 'E-Mail', 'attr' => [
                'value' => (isset($lastUsername) ? $lastUsername : ""),
                'placeholder' => 'hallo@jugendhackt.de'
                ]])
            ->add('password', PasswordType::class, [
                'label' => 'Passwort',
                'attr' => [
                    'placeholder' => '***********',
                ],
                ])
            ->add('login', SubmitType::class, [
                'label' => 'Anmelden',
                'attr' => [
                    'class' => 'btn-success btn-block'
                    ]])
            ->getForm()
            ->createView();
        return $this->render('security/login.html.twig',
        [
            'form' => $form,
            'error' => $error]);
    }

    /**
     * @Route("/security/logout", name="security_logout")
     */
    public function logout()
    {

    }
}
