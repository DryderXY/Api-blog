<?php

namespace App\Controller;

use App\Dto\CategoryCountPostsDTO;
use App\Entity\Post;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PostController extends AbstractController
{

    private PostRepository $postRepository;
    private SerializerInterface $serializer;
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    private CategoryRepository $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository,PostRepository $postRepository, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->postRepository = $postRepository;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->categoryRepository = $categoryRepository;
    }


    #[Route('/api/posts', name: 'api_getPosts', methods: ["GET"])]
    public function getPosts(): Response
    {
        // Rechercher les posts dans la base de données
        $posts = $this->postRepository->findAll();
        // Normaliser le tableau $posts
        // -> Transformer $posts en tableau associatif
        //$postsArray = $normalizer->normalize($posts);
        // Encoder en Json
        //$postJson = json_encode($postsArray);

        // Sérialiser le tableau $posts en Json
        $postsJson = $this->serializer->serialize($posts,'json',['groups' => 'list_posts']);

        // Générer la réponse HTTP
        /*$response = new Response();
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('content-type','application/json');
        $response->setContent($postsJson);
        return $response;*/

        return new Response($postsJson,Response::HTTP_OK, ['content-type' => 'application/json']);
    }

    #[Route('/api/posts/{id}', name: 'api_getPost', methods: ["GET"])]
    public function getPost(int $id): Response
    {
        $post = $this->postRepository->find($id);
        // Générer une erreur si le post recherché n'existe pas
        if (!$post) {
            return $this->generateError("Le poste demandé n'existe pas", Response::HTTP_NOT_FOUND);
        }
        $postJson = $this->serializer->serialize($post,'json',['groups' => 'get_post']);
        return new Response($postJson,Response::HTTP_OK, ['content-type' => 'application/json']);
    }

    #[Route('/api/posts', name: 'api_createPost', methods: ["POST"])]
    public function createPost(Request $request) : Response
    {
        // Récupérer dans la requête le body contenant le JSON du new post
        $bodyRequest = $request->getContent();
        // Désérializer les JSON en un objet de la classe Post

        try {
            // Surveiller si le conde ci-dessous lève une exception
            $post  = $this->serializer->deserialize($bodyRequest,Post::class,'json');
            $category = $this->categoryRepository->find($bodyRequest);
        } catch (NotEncodableValueException $exception) {
            return $this->generateError("Erreur syntax", Response::HTTP_BAD_REQUEST);
        }
        // Validation des données ($post) en fonctions des règles de validation définies
        $erreur = $this->validator->validate($post);
        // Tester s'il y a des erreurs
        if (count($erreur) > 0) {
            // Transformer le tableau en json
            $erreurjson = $this->serializer->serialize($erreur,"json");
            return new Response($erreurjson,Response::HTTP_BAD_REQUEST,
            ["content-type" => "application/json"]);
        }

        // Insérer le nouveau post dans la base de donnée
        $post->setCreatedAt(new \DateTime());
        $this->entityManager->persist($post);
        $this->entityManager->flush();
        // Générer la réponse HTTP
        // Sérialiser $post en json
        $postJson = $this->serializer->serialize($post,'json');
        return new  Response($postJson, Response::HTTP_CREATED,
        ["content-type" => "application/json"]);
    }

    #[Route('/api/posts/{id}', name: 'api_deletePost', methods: ["DELETE"])]
    public function deletePost(int $id) : Response
    {
        $post = $this->postRepository->find($id);
        if (!$post) {
            return $this->generateError("Le poste à supprimer n'existe pas", Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($post);
        $this->entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/posts/{id}', name: 'api_updatePost', methods: ["PUT"])]
    public function updatePost(int $id, Request $request) : Response
    {
        // Récupérer les body de la requête
        $bodyRequest = $request->getContent();
        // Récupérer dans la base de données le post à modifier
        $post = $this->postRepository->find($id);
        if (!$post) {
            return $this->generateError("Le poste à modifier n'existe pas", Response::HTTP_NOT_FOUND);
        }        // Modifier le post avec les données du body (json)
        try {
            $this->serializer->deserialize($bodyRequest,Post::class,'json',
                ['object_to_populate' => $post]);
        } catch (NotEncodableValueException $exception) {
            return $this->generateError("Erreur de syntaxe", Response::HTTP_BAD_REQUEST);
        }
        // Modifier le post dans la base de données
        $this->entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    private function generateError(string $message, int $status) : Response {

            $erreur = [
                'status' => $status,
                'message' => $message
            ];
            // Renvoyer la réponse au format json (erreur)
            return new Response(json_encode($erreur), $status,
                ['content-type' => "application/json"]);
        }

    #[Route('/api/categories', name: 'api_getCategories', methods: ["GET"])]
    public function getCategories(): Response
    {
        $categories = $this->categoryRepository->findAll();

        // Générer une erreur si le post recherché n'existe pas
        $postJson = $this->serializer->serialize($categories,'json',['groups' => 'list_categories']);
        return new Response($postJson,Response::HTTP_OK, ['content-type' => 'application/json']);
    }

    #[Route('/api/categories/{id}/posts', name: 'api_getPostsCategory', methods: ["GET"])]
    public function getPostsCategory(int $id): Response
    {
        $post = $this->categoryRepository->find($id);
        // Générer une erreur si le post recherché n'existe pas
        if (!$post) {
            return $this->generateError("La categorie demandée n'existe pas", Response::HTTP_NOT_FOUND);
        }
        $postJson = $this->serializer->serialize($post,'json',['groups' => 'list_post_cat']);
        return new Response($postJson,Response::HTTP_OK, ['content-type' => 'application/json']);
    }

    #[Route('/api/category/{id}', name: 'api_getCategory', methods: ["GET"])]
    public function getCategory(int $id): Response
    {
        $category = $this->categoryRepository->find($id);

        // Générer une erreur si le post recherché n'existe pas
        if (!$category) {
            return $this->generateError("La categorie demandée n'existe pas", Response::HTTP_NOT_FOUND);
        }

        $DTO = new CategoryCountPostsDTO();
        $DTO->setTitle($category->getTitle());
        $DTO->setId($category->getId());
        $DTO->setNbPosts(count($category->getPosts()));

        $postJson = $this->serializer->serialize($DTO,'json'); // Plus besoin du groupe car DTO
        return new Response($postJson,Response::HTTP_OK, ['content-type' => 'application/json']);
    }
}
