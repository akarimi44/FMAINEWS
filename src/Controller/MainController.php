<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Form\CommentsType;
use App\Entity\Posts;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\PostsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class MainController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function indexHome(PostsRepository $postsRepository): Response
    {
        return $this->render('mainPages/home.html.twig', [
            'posts' => $postsRepository->findLatestPosts(6),
        ]);
    }
    #[Route('/show/{id}', name: 'app_posts_show_user', methods: ['GET', 'POST'])]
    public function show(Request $request, Posts $post, EntityManagerInterface $em): Response
    {
        $comment = new Comments();
        $form = $this->createForm(CommentsType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setUser($this->getUser());
            $comment->setPost($post);
            $comment->setCreatedAtComment(new \DateTimeImmutable());

            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('app_posts_show_user', ['id' => $post->getId()]);
        }

        return $this->render('mainPages/show.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
            'comments' => $post->getComments(),
        ]);
    }

    #[Route('/category/{id}', name: 'app_posts_by_category')]
    public function postsByCategory(int $id, PostsRepository $postsRepository): Response
    {
        $posts = $postsRepository->findPostsByCategory($id, 10);

        return $this->render('mainPages/category.html.twig', [
            'posts' => $posts,
            'categoryId' => $id,
        ]);
    }
        #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }
}
