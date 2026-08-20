<?php

namespace App\Controller\Admin;

use App\Entity\Admin\Member;
use App\Form\Admin\FirstAccountType;
use App\Repository\MemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class SetupController extends AbstractController
{
    #[Route('/setup', name: 'app_setup', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        MemberRepository $memberRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response {
        // Garde-fou : cet écran ne doit exister que tant qu'aucun compte n'existe.
        if ($memberRepository->count([]) > 0) {
            return $this->redirectToRoute('app_login');
        }

        $member = new Member();
        $form = $this->createForm(FirstAccountType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();

            $member->setPassword($passwordHasher->hashPassword($member, $plainPassword));
            $member->setRoles(['ROLE_SUPER_ADMIN']);
            $member->setIsVerified(true);

            $entityManager->persist($member);
            $entityManager->flush();

            return $security->login($member, 'form_login', 'main');
        }

        return $this->render('admin/security/setup.html.twig', [
            'form' => $form,
        ]);
    }
}
