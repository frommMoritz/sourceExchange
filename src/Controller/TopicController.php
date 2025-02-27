<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Topic;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use App\Entity\Link;
use Doctrine\DBAL\Types\TextType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType as SymfonyTextType;
use Symfony\Component\HttpFoundation\Request;

class TopicController extends AbstractController
{
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/topic", name="topic")
     */
    public function index()
    {
        return $this->render('index.html.twig', [
            'controller_name' => 'TopicController',
        ]);
    }

    /**
     * @Route("/mytopics", name="mytopics")
     */
    public function mytopics()
    {
        return $this->render('default/mytopics.html.twig');
    }

    /**
     * @Route("/topic/{id}", name="topic_show")
     */
    public function show($id, Request $request)
    {
        $topic = $this->getDoctrine()
            ->getRepository(Topic::class)
            ->find($id);

        $link = new Link();
        $link->setTopic($topic);

        $linkForm = $this->createFormBuilder($link)
            ->add('href', UrlType::class, [
                'label' => 'Link hinzufügen',
                'attr' => [
                    'placeholder' => 'Link eingeben',
                ],
            ])
            ->add('description', SymfonyTextType::class, [
                'label' => 'Anmerkung',
                'attr' => [
                    'placeholder' => 'Anmerkung eingeben',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Link hinzufügen',
                'attr' => [
                    'class' => 'btn-success btn-block',
                ],
            ])
            ->getForm();

        $linkForm->handleRequest($request);

        if ($linkForm->isSubmitted() && $linkForm->isValid() && $this->isGranted('ROLE_USER'))
        {
            $link = $linkForm->getNormData();
            $link->setUser($this->getUser());
            $this->em->persist($link);
            $this->em->flush();
        }
        if (!$topic) {
            throw $this->createNotFoundException(
                'No topic found for id '.$id
            );
        }

        $linkForm = $linkForm->createView();

        // or render a template
        // in the template, print things with {{ product.name }}
        return $this->render('default/single-view.html.twig', [
            'topic' => $topic,
            'linkForm' => $linkForm,
        ]);
    }
}
