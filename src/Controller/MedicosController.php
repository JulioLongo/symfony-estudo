<?php

namespace App\Controller;

use App\Entity\Medico;
use App\Helper\MedicoFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

//abstract da função a acessar dado do doctrine
class MedicosController extends AbstractController {

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var MedicoFactory
     */
    private $medicoFactory;

    public function __construct(
        EntityManagerInterface $entityManager,
        MedicoFactory $medicoFactory
    ){
        $this->entityManager = $entityManager;
        $this->medicoFactory = $medicoFactory;
    }

    /**
     * @Route("/medicos" , methods={"POST"})
     */
    public function cadastrarMedico(Request $request): Response
    {
        $corpoRequisicao = $request->getContent();

        //puxa classe medico factory
        $medico = $this->medicoFactory->criarMedico($corpoRequisicao);

        //observar médico
        $this->entityManager->persist($medico);

        //enviar informacao para o banco
        $this->entityManager->flush();

        return new JsonResponse($medico);
    }

    /**
     * @Route("/medicos", methods={"GET"})
     */
    public function buscarTodos(): Response
    {
        $repositorioMedico = $this
        ->getDoctrine()
        ->getRepository(Medico::class);

        //buscar dados
        $medicoList = $repositorioMedico->findAll();

        //retornar lista de médicos
        return new JsonResponse($medicoList);
    }

    /**
     * @Route("/medicos/{id}", methods={"GET"})
     */
    public function buscarMedico(int $id): Response
    {
        $medico = $this->buscaMedico($id);

        //status se caso não existir médico
        $codigoRetorno = 200;
        if (is_null($medico)){
            $codigoRetorno = Response::HTTP_NO_CONTENT;
        }

        return new JsonResponse($medico, $codigoRetorno);
    }

    /**
     * @Route("/medicos/{id}", methods={"PUT"})
     */
    //pega id da rota
    public function atualizaMedico(int $id, Request $request): Response
    {
        $corpoRequisicao = $request->getContent();

        //envia json da requisicao para classe factory
        $medicoEnviado = $this->medicoFactory->criarMedico($corpoRequisicao);

        //buscando medico no repositorio
        $medicoExistente = $this->buscaMedico($id);

        //verifica se existe medico
        if(is_null($medicoExistente)){
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        //alterar medico existente com dados enviados
        $medicoExistente->crm = $medicoEnviado->crm;
        $medicoExistente->nome = $medicoEnviado->nome;

        //enviar para o banco de dados
        $this->entityManager->flush();

        return new JsonResponse($medicoExistente);
    }

    /**
     * @Route("/medicos/{id}", methods={"DELETE"})
     */
    public function remove(int $id):Response
    {
        $medico = $this->buscaMedico($id);
        $this->entityManager->remove($medico);
        $this->entityManager->flush();

        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @param int $id
     * @return Medico|object|null
     */
    public function buscaMedico(int $id)
    {
        $repositorioMedico = $this
            ->getDoctrine()
            ->getRepository(Medico::class);

        $medico = $repositorioMedico->find($id);

        return $medico;
    }
}