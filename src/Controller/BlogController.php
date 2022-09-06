<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Post;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Define a common route
 * @Route("/blog")
 */
class BlogController extends AbstractController
{

    const POSTS = [
        ['id' => 1, 'slug' => 'Symfony 4'],
        ['id' => 2, 'slug' => 'PHP'],
        ['id' => 10, 'slug' => 'JS']
    ];

    /**
     * @Route("/posts/{page}", defaults={"page": 5}, name="get-all-posts")
     */
    public function index($page, Request $request)
    {

        $repository = $this->getDoctrine()->getRepository(Post::class);
        $posts = $repository->findAll();

        return $this->json([
            'page' => $page,
            'limit' => $request->get('limit', 45), // the second parameter represents the default value (if the query doesn't contain the param limit in this case)
            'data' => array_map(function(Post $post){
                return [
                    'title' => $post->getTitle(),
                    'content' => $post->getContent(),
                    'author' => $post->getAuthor(),
                    //'link' => $this->generateUrl('get-post-by-id', ['id' => $post->getId()])
                ];
            }, $posts)
        ]);
    }

    /**
     * @Route("/post/{post_id}", requirements={"post_id": "\d+"}, name="get-post-by-id", methods={"GET"})
     * @ParamConverter("post", class="App:Post", options = {"mapping": {"post_id":"id"}})
     */
    public function postById($post)
    {
        /* Search in an associative array by field not by index */
        /*if (array_search($id, array_column(self::POSTS, 'id')) === false) {
            return $this->json(['message' => 'No posts found with index ' . $id]);
        }
        return $this->json(self::POSTS[array_search($id, array_column(self::POSTS, 'id'))]);*/
        
        /*$repository = $this->getDoctrine()->getRepository(Post::class);
        $post = $repository->find($id);*/

        /* We can delete the above code once we inject the Post class so the two lines will be executed automatically : get repo and find by the id received in the URL*/
        /* A better way to do it is to use the ParamConverter annotation and remove the dependency injection*/
        /* I changed the segment name to post_id to try the mapping */
        return $this->json($post);
    }

    /**
     * @Route("/post/{slug}", name="get-post-by-slug", methods={"GET"})
     * @ParamConverter("post", class="App:Post", options = {"mapping": {"slug":"slug"}})
     */
    public function postBySlug($post)
    {
        /*if (array_search($slug, array_column(self::POSTS, 'slug')) === false) {
            return $this->json(['message' => 'No posts found with slug ' . $slug]);
        }
        return $this->json(self::POSTS[array_search($slug, array_column(self::POSTS, 'slug'))]);*/
        
        /*$repository = $this->getDoctrine()->getRepository(Post::class);
        $post = $repository->findOneBy(['slug' => $slug]);*/

        /* Same thing as the postById*/

        return $this->json($post);
    }
    /**
     * @Route("/add", name="add-post", methods={"POST"})
     */
    public function add(Request $request)
    {
        $serializer = $this->get('serializer');
        $post = $serializer->deserialize($request->getContent(), Post::class, 'json');
        $em = $this->getDoctrine()->getManager();
        $em->persist($post);//the persist method generates the INSERT SQL query
        $em->flush();// executes the query
        return $this->json($post);
    }
    /**
     * @Route("/post/{id}", requirements={"id": "\d+"}, name="delete-post", methods={"DELETE"})
     * @ParamConverter("post", class="App:Post", options = {"mapping": {"id":"id"}})
     */
    public function destroy($post)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();

        return $this->json(null, 204);
    }

    public function test(UserPasswordEncoderInterface $passwordEncoder)
    {   
        
    }

}
