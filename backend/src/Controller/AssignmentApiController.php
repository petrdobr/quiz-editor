<?php

namespace App\Controller;

use App\Entity\Assignment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/assignments')]
class AssignmentApiController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $assignments = $em->getRepository(Assignment::class)->findBy(['userId' => 1]);
        return $this->json($assignments);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getOne(Assignment $assignment): JsonResponse
    {
        return $this->json($assignment);
    }

    #[Route('', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        $assignment = $serializer->deserialize($request->getContent(), Assignment::class, 'json');

        $assignment->setUserId(1);
        $assignment->setCreatedAt(new \DateTimeImmutable());
        $assignment->setUpdatedAt(new \DateTimeImmutable());

        $errors = $validator->validate($assignment);
        if (count($errors) > 0) {
            return $this->json($errors, 400);
        }

        $em->persist($assignment);
        $em->flush();

        return $this->json($assignment, 201);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(
        Assignment $assignment,
        Request $request,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $assignment->setTitle($data['title'] ?? $assignment->getTitle());
        $assignment->setType($data['type'] ?? $assignment->getType());
        $assignment->setSpec($data['spec'] ?? $assignment->getSpec());
        $assignment->setUpdatedAt(new \DateTimeImmutable());

        $errors = $validator->validate($assignment);
        if (count($errors) > 0) {
            return $this->json($errors, 400);
        }

        $em->flush();
        return $this->json($assignment);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(Assignment $assignment, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($assignment);
        $em->flush();
        return $this->json(['status' => 'deleted']);
    }
}
