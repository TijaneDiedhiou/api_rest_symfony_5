<?php

namespace App\Controller;

use App\Entity\Region;
use App\Repository\RegionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{
    
    /**
     * @Route("/api/region", name="api_add_to_region",methods={"GET"})
     */
    public function addRegionByApi(SerializerInterface $serializer)
    {

        $regionJson=file_get_contents("https://geo.api.gouv.fr/regions");
      /*  //METHODE 1
        // 1Etape =>decode:conversion Json to Tableau

        $regionTab=$serializer->decode($regionJson,"json");

        // 2Etape => Dénormalisation: Conversion de Tableau vers Objet

        $regionObject=$serializer->denormalize($regionTab, 'App\Entity\Region[]');

        dd($regionObject);*/
        // METHODE 2 Déserialise : conversion Json to Object

        $regionObject = $serializer->deserialize($regionJson,'App\Entity\Region[]','json');

         $entityManager = $this->getDoctrine()->getManager();
      
         foreach($regionObject as $region){
        
            $entityManager->persist($region);
            
         }
            $entityManager->flush();
    
              return new JsonResponse("succes",Response::HTTP_CREATED,[],true);
    }

    //RECUPERATIONS DE CE QUI EST DANS LA BDD
            /**
            * @Route("/api/regions", name="api_allto_region", methods={"GET"})
            */
    public function showRegion(SerializerInterface $serializer,RegionRepository $repo)
     {
         $regionsObject=$repo->findAll();

         $regionsJson =$serializer->serialize(
             $regionsObject,
             "json",
            [
                "groups"=>["region:read_all"]
            ]);

         return new JsonResponse($regionsJson,Response::HTTP_OK,[],true);
     }

      /**
            * @Route("/api/regions", name="api_add_region",methods={"POST"})
            */
    public function AddRegion(SerializerInterface $serializer, Request $request, ValidatorInterface $validator)
    {
        //recuperation du body de la requette
        $regionJson = $request->getContent();

        //Deserialisation
        $region = $serializer->deserialize( $regionJson,Region::class,'json');
        
        //validation
        $errors = $validator->validate($region);
            if (count($errors) > 0) {
      
                 $errorsString =$serializer->serialize($errors,"json");
     
                return new JsonResponse( $errorsString ,Response::HTTP_BAD_REQUEST,[],true);
         }

        $entityManager = $this->getDoctrine()->getManager();
       
        $entityManager->persist($region);
        
        $entityManager->flush();
      
        return new JsonResponse("succes",Response::HTTP_CREATED,[],true);
        


    }
}
