<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ArticlesRepository;
use App\Entity\Articles;

class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="app_blog")
     */
    public function index(Request $request,ManagerRegistry $doctrine): Response
    {
        $page_no = 0;
        if($request->query->get('page_no')){
            $page_no = $request->query->get('page_no');
        }

        if($request->query->get('del')){
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository(Articles::class)->findOneBySomeField($request->query->get('del'));
            $em->remove($user);
            $em->flush();
        }

        $offset = $page_no * 10;
        $entityManager = $doctrine->getManager();
        $articles = $entityManager->getRepository(Articles::class)->findArc($offset);

        $previous_page = $page_no - 1;
        $next_page = $page_no + 1;

        $count = $doctrine->getManager()->getRepository(Articles::class)->counts();
        $cnt = (int)$count /10;
        $cntFnal = $cnt - 1;
        if($cntFnal > 9){
            $cntFnal = 9;
        }

        return $this->render('blog/index.html.twig', [
            'articles' => $articles,
            'count' => $cntFnal,
            'page_no' => $page_no,
            'previous_page' => $previous_page,
            'next_page' => $next_page,
        ]);
    }
}
