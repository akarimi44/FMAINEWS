<?php

namespace App\Controller;


use App\Form\PostsType;
use App\Entity\Posts;
use App\Repository\PostsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Filesystem\Filesystem;



#[Route('/posts')]
final class PostsController extends AbstractController
{
    #[Route(name: 'app_posts_index', methods: ['GET'])]
    public function index(PostsRepository $postsRepository): Response
    {
        return $this->render('posts/index.html.twig', [
            'posts' => $postsRepository->findAll(),
        ]);
    }
#[Route('/new', name: 'app_posts_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, Security $security): Response
{
    $post = new Posts();
    $form = $this->createForm(PostsType::class, $post);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $imageFile = $form->get('image')->getData();

        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

            $imageFile->move(
                $this->getParameter('images_directory'),
                $newFilename
            );

            $post->setImage($newFilename);
        }

        // 1. Получаем текущего залогиненного пользователя
        $user = $security->getUser();

        // 2. Устанавливаем его как автора поста
        $post->setAuteur($user);

        $entityManager->persist($post);
        $entityManager->flush();

        return $this->redirectToRoute('app_posts_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('posts/new.html.twig', [
        'post' => $post,
        'form' => $form,
    ]);
}
    #[Route('/{id}', name: 'app_posts_show', methods: ['GET'])]
    public function showPost(Posts $post): Response
    {
        return $this->render('posts/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_posts_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Posts $post, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
{
    $form = $this->createForm(PostsType::class, $post);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $imageFile = $form->get('image')->getData();

        if ($imageFile) {
            $oldImage = $post->getImage();

            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

            $imageFile->move(
                $this->getParameter('images_directory'),
                $newFilename
            );

            $post->setImage($newFilename);

            if ($oldImage) {
                $filesystem = new Filesystem();
                $oldImagePath = $this->getParameter('images_directory') . '/' . $oldImage;
                
                if ($filesystem->exists($oldImagePath)) {
                    $filesystem->remove($oldImagePath);
                }
            }
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_posts_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('posts/edit.html.twig', [
        'post' => $post,
        'form' => $form,
    ]);
}

    #[Route('/{id}', name: 'app_posts_delete', methods: ['POST'])]
    public function delete(Request $request, Posts $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_posts_index', [], Response::HTTP_SEE_OTHER);
    }
}
