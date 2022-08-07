<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Form\Type\ArticleType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

// use Symfony\Component\BrowserKit\Request;

class BlogController extends AbstractController
{
    /**
     * @Route("/", name="app_blog")
     */
    public function index(ArticleRepository $repository): Response
    {
        $articles = $repository->findArticles();

        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/article/{id}-{slug}", name="show_article")
     */
    public function show(string $slug, ArticleRepository $repository): Response
    {
        // ... fetch articles by slug
        $article = $repository->findBySlug($slug);

        if (!$article) {
            throw $this->createNotFoundException(
                'No article found for slug: ' . $slug
            );
        }

        return $this->render('blog/show.html.twig', [
            'article' => $article,
        ]);
    }

    /**
     * @Route("/article/add", name="add_article")
     * @Route("/admin/blog/{id}/edit", name="article_edit")
     */
    public function createorUpdate(Article $article = null, Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger): Response
    {
        if (!$article) {
            $article = new Article();
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cover = $form->get("cover")->getData();

            //cover file upload
            if ($cover) {
                $destination = $this->getParameter('kernel.project_dir') . '/public/images/article_cover';
                $originalFilename = pathinfo($cover->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename . '-' . uniqid() . '.' . $cover->guessExtension();

                try {
                    $cover->move(
                        $destination,
                        $newFilename
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $article->setCover($newFilename);
            }

            $article->setSlug(
                strtolower($slugger->slug($article->getTitle())) . "-" . uniqid()
            );

            if (!$article->getID()) {
                $article->setCreatedAt(new \DateTime());
            }

            // persist Data to database
            $manager = $doctrine->getManager();
            $manager->persist($article);
            $manager->flush();

            return $this->redirectToRoute('show_article', [
                'id' => $article->getId(),
                'slug' => $article->getSlug()
            ]);
        }

        return $this->render('/admin/form.html.twig', [
            'form' => $form->createView(),
            'edit' => $article->getId() !== null
        ]);
    }
}
