<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\Persistence\ManagerRegistry;


class AdminController extends AbstractController
{
  /**
   * @Route("/admin/blog", name="admin_blog")
   */
  public function indexBlog(ArticleRepository $repository)
  {
    $articles = $repository->findAll();

    return $this->render('admin/index.html.twig', [
      'controller_name' => 'BlogController',
      'articles' => $articles,
    ]);
  }

  /**
   * @Route("/admin/blog/delete/{id}", name="article_delete", methods={"POST"})
   */
  public function delete(Article $article, ManagerRegistry $doctrine, Request $request): Response
  {
    if ($this->isCsrfTokenValid('delete' . $article->getId(), $request->request->get('_token'))) {
      $entityManager = $doctrine->getManager();

      $destination = $this->getParameter('kernel.project_dir') . '/public/images/article_cover/';
      unlink($destination . $article->getCover());

      $entityManager->remove($article);
      $entityManager->flush();

      return $this->redirectToRoute('admin_blog');
    }
  }
}
