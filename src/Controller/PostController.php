<?php

namespace App\Controller;

require_once '..\vendor\autoload.php';

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\DateTime;
use Doctrine\Persistence\ManagerRegistry;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use App\Repository\ArticlesRepository;
use App\Entity\Articles;

class PostController extends AbstractController
{
    /**
     * @Route("/post", name="app_post")
     */
    public function index(Request $request,ManagerRegistry $doctrine): Response
    {        
        //$t is title to parse.
        //$srtD for short description to parse.
        //$pic url picture to parse.
        //$date_added the date added to be database

        $connAMQ = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connAMQ->channel();

        $respond_reqst = '';
        
        $error_response_msg = '';
        $msgFormat = 'empty query';
        $new_article_id = "";

        if(!$request->query->get('t')||!$request->query->get('srtD')||!$request->query->get('pic')||!$request->query->get('date_add')){
            $error_response_msg = 'One of this  "t,srtD,pic,date_add" CLI is missing. Look for the missing CLI and try again.';
        }

        if($request->query->get('t')){

            $t = $request->query->get('t');
            $dateStamp = date("d-m-Y H:i");
            $srtD = $request->query->get('srtD');
            $pic = $request->query->get('pic');
            $date_added = $request->query->get('date_added');

            $entityManager = $doctrine->getManager();
            $chkFindTitle =  $entityManager->getRepository(Articles::class)->findOneBySomeField($t);
            if ($chkFindTitle) {
                $error_response_msg = 'News Resource already exist';
                $get_last_update = $chkFindTitle->getDateUpdateEvn();
                $msgFormat = "['Last Update Time: $get_last_update']";
            }else{
                $error_response_msg = "[x] Sent for Admin approval'\n";
                $msgFormat = "['$t','$srtD','$pic','$date_added','$dateStamp']";
                $article = new Articles();
                $article->setTitle($t);
                $article->setDescription(substr($srtD,0,500));
                $article->setPicture($pic);
                $article->setDate($date_added);
                $article->setDateUpdateEvn(date('d-m-Y H:i'));
                $entityManager->persist($article);
                $entityManager->flush();

                $new_article_id = $article->getId();
            }
        }

        $channel->queue_declare('article', false, false, false, false);

        $msg = new AMQPMessage($msgFormat);

        $channel->basic_publish($msg, '', 'article');        

        return $this->render('post/index.html.twig', [
            'error_msg' => $error_response_msg,
            'new_article_id' => $new_article_id,
            'article_log' => $msgFormat,
            
        ]);

        $channel->close();
        $connAMQ->close();

    }
    
}
