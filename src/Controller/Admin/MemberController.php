<?php

namespace App\Controller\Admin;

use App\Entity\Admin\Member;
use App\Form\Admin\MemberType;
use App\Repository\MemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/member')]
final class MemberController extends AbstractController
{
    #[Route('/', name: 'app_admin_member_index', methods: ['GET'])]
    public function index(MemberRepository $memberRepository): Response
    {
        return $this->render('admin/member/index.html.twig', [
            'members' => $memberRepository->findAll(),
        ]);
    }

    #[Route('/new/{role}', name: 'app_admin_member_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        $role
    ): Response {
        $member = new Member();
        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();

            $member->setIsVerified(1);

            if ($role === 'prescripteur') {
                $member->setRoles(['ROLE_PRESCRIPTEUR']);
            }else if($role === 'mediateur'){
                $member->setRoles(['ROLE_MEDIATEUR']);
            }
            if ($password) {
                $hashedPassword = $passwordHasher->hashPassword($member, $password);
                $member->setPassword($hashedPassword);
            } else {
                return $this->render('admin/member/new.html.twig', [
                    'member' => $member,
                    'form' => $form,
                ]);
            }

            $entityManager->persist($member);
            $entityManager->flush();

            $this->addFlash('success', 'Le membre a bien été créé.');
            return $this->redirectToRoute('app_admin_member_index');
        }

        return $this->render('admin/member/new.html.twig', [
            'role' => $role,
            'member' => $member,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_member_show', methods: ['GET'])]
    public function show(Member $member): Response
    {
        return $this->render('admin/member/show.html.twig', [
            'member' => $member,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_member_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Member $member,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //  Rôle
            $selectedRole = $form->get('role')->getData();
            if ($selectedRole) {
                $member->setRoles([$selectedRole]);
            }

            //  Nouveau mot de passe si saisi
            $plainPassword = $form->get('plainPassword')->getData();
            if (!empty($plainPassword)) {
                $hashedPassword = $passwordHasher->hashPassword($member, $plainPassword);
                $member->setPassword($hashedPassword);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Le membre a bien été mis à jour.');
            return $this->redirectToRoute('app_admin_member_index');
        }

        return $this->render('admin/member/edit.html.twig', [
            'member' => $member,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_member_delete', methods: ['POST'])]
    public function delete(Request $request, Member $member, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $member->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($member);
            $entityManager->flush();
            $this->addFlash('danger', 'Le membre a bien été supprimé.');
        }

        return $this->redirectToRoute('app_admin_member_index');
    }
}
